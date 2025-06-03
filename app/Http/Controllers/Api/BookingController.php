<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController
{
    /**
     * Display available dates for a specific tour
     */
    public function getAvailableDates(Request $request, $tourId)
    {
        try {
            $tour = Tour::with('tourDates')->findOrFail($tourId);

            if ($tour->availability_status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'This tour is currently not available for booking.'
                ], 400);
            }

            $startDate = Carbon::now();
            $endDate = Carbon::now()->addMonths(3); // Show availability for next 3 months

            $availableDates = [];

            // Generate available dates based on tour_dates configuration
            foreach ($tour->tourDates as $tourDate) {
                $dayOfWeek = $tourDate->day_of_week;
                $currentDate = $startDate->copy();

                while ($currentDate <= $endDate) {
                    if ($currentDate->format('l') === $dayOfWeek) {
                        $dateString = $currentDate->format('Y-m-d');

                        // Check if this date is already fully booked
                        $existingBookings = Booking::where('tour_id', $tourId)
                            ->where('tour_date_id', $tourDate->id)
                            ->where('booked_date', $dateString)
                            ->where('status', '!=', 'cancelled')
                            ->sum('group_size');

                        $remainingCapacity = $tour->max_group_size - $existingBookings;

                        if ($remainingCapacity > 0) {
                            $availableDates[] = [
                                'date' => $dateString,
                                'tour_date_id' => $tourDate->id,
                                'start_time' => $tourDate->start_time,
                                'end_time' => $tourDate->end_time,
                                'remaining_capacity' => $remainingCapacity,
                                'day_of_week' => $dayOfWeek
                            ];
                        }
                    }
                    $currentDate->addDay();
                }
            }

            return response()->json([
                'success' => true,
                'tour' => [
                    'id' => $tour->id,
                    'title' => $tour->title,
                    'price' => $tour->price,
                    'max_group_size' => $tour->max_group_size,
                    'duration' => $tour->duration
                ],
                'available_dates' => $availableDates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found.'
            ], 404);
        }
    }

    /**
     * Store a new booking
     */
    public function store(Request $request)
    {
        // Add detailed logging
        \Log::info('Booking creation attempt', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|exists:tours,id',
            'tour_date_id' => 'required|exists:tour_dates,id',
            'booked_date' => 'required|date|after_or_equal:today',
            'group_size' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            \Log::warning('Booking validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $tourId = $request->tour_id;
                $tourDateId = $request->tour_date_id;
                $bookedDate = $request->booked_date;
                $groupSize = $request->group_size;
                $userId = Auth::id();

                \Log::info('Processing booking transaction', [
                    'tour_id' => $tourId,
                    'tour_date_id' => $tourDateId,
                    'user_id' => $userId
                ]);

                // Check if user is authenticated
                if (!$userId) {
                    throw new \Exception('User not authenticated');
                }

                // Fetch tour and tour date
                $tour = Tour::findOrFail($tourId);
                $tourDate = TourDate::where('id', $tourDateId)
                    ->where('tour_id', $tourId)
                    ->first();

                if (!$tourDate) {
                    throw new \Exception('Tour date not found or does not belong to this tour');
                }

                \Log::info('Tour and TourDate found', [
                    'tour_title' => $tour->title,
                    'tour_date_day' => $tourDate->day_of_week
                ]);

                // Check if tour is available
                if ($tour->availability_status !== 'available') {
                    return response()->json([
                        'success' => false,
                        'message' => 'This tour is currently not available for booking.'
                    ], 400);
                }

                // Check if the requested date matches the tour date's day of week
                $requestedDayOfWeek = Carbon::parse($bookedDate)->format('l');
                if ($requestedDayOfWeek !== $tourDate->day_of_week) {
                    return response()->json([
                        'success' => false,
                        'message' => "The selected date ({$bookedDate}) is a {$requestedDayOfWeek}, but this tour is only available on {$tourDate->day_of_week}s."
                    ], 400);
                }

                // Check availability for the specific date
                $existingBookings = Booking::where('tour_id', $tourId)
                    ->where('tour_date_id', $tourDateId)
                    ->where('booked_date', $bookedDate)
                    ->where('status', '!=', 'cancelled')
                    ->lockForUpdate() // Prevent race conditions
                    ->sum('group_size');

                $remainingCapacity = $tour->max_group_size - $existingBookings;

                \Log::info('Capacity check', [
                    'max_group_size' => $tour->max_group_size,
                    'existing_bookings' => $existingBookings,
                    'remaining_capacity' => $remainingCapacity,
                    'requested_size' => $groupSize
                ]);

                if ($groupSize > $remainingCapacity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Only {$remainingCapacity} spots remaining for this date. You requested {$groupSize} spots."
                    ], 400);
                }

                // Check if user already has a booking for this tour on this date
                $existingUserBooking = Booking::where('user_id', $userId)
                    ->where('tour_id', $tourId)
                    ->where('booked_date', $bookedDate)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($existingUserBooking) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You already have a booking for this tour on the selected date.'
                    ], 400);
                }

                // Calculate total price
                $totalPrice = $tour->price * $groupSize;

                \Log::info('Creating booking', [
                    'total_price' => $totalPrice,
                    'price_per_person' => $tour->price
                ]);

                // Create the booking
                $booking = Booking::create([
                    'user_id' => $userId,
                    'tour_id' => $tourId,
                    'tour_date_id' => $tourDateId,
                    'booked_date' => $bookedDate,
                    'group_size' => $groupSize,
                    'total_price' => $totalPrice,
                    'status' => 'pending'
                ]);

                \Log::info('Booking created successfully', [
                    'booking_id' => $booking->id
                ]);

                // Load relationships for response
                $booking->load(['tour', 'tourDate', 'user']);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking created successfully!',
                    'booking' => [
                        'id' => $booking->id,
                        'tour_title' => $booking->tour->title,
                        'booked_date' => $booking->booked_date,
                        'start_time' => $booking->tourDate->start_time,
                        'end_time' => $booking->tourDate->end_time,
                        'group_size' => $booking->group_size,
                        'total_price' => $booking->total_price,
                        'status' => $booking->status,
                        'booking_reference' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT)
                    ]
                ], 201);
            });

        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the booking. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display user's bookings
     */
    public function getUserBookings()
    {
        try {
            $userId = Auth::id();

            $bookings = Booking::with(['tour', 'tourDate'])
                ->where('user_id', $userId)
                ->orderBy('booked_date', 'desc')
                ->get();

            $formattedBookings = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                    'tour_title' => $booking->tour->title,
                    'booked_date' => $booking->booked_date,
                    'start_time' => $booking->tourDate->start_time,
                    'end_time' => $booking->tourDate->end_time,
                    'group_size' => $booking->group_size,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'bookings' => $formattedBookings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch bookings.'
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, $bookingId)
    {
        try {
            $userId = Auth::id();

            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking is already cancelled.'
                ], 400);
            }

            if ($booking->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a completed booking.'
                ], 400);
            }

            // Check if booking date is in the future (allow cancellation at least 24 hours before)
            $bookingDateTime = Carbon::parse($booking->booked_date . ' ' . $booking->tourDate->start_time);
            $now = Carbon::now();

            if ($bookingDateTime->diffInHours($now) < 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bookings can only be cancelled at least 24 hours before the tour starts.'
                ], 400);
            }

            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found or you do not have permission to cancel it.'
            ], 404);
        }
    }

    /**
     * Get booking details
     */
    public function show($bookingId)
    {
        try {
            $userId = Auth::id();

            $booking = Booking::with(['tour', 'tourDate', 'tour.guide', 'tour.location', 'tour.city'])
                ->where('id', $bookingId)
                ->where('user_id', $userId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_reference' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                    'tour' => [
                        'id' => $booking->tour->id,
                        'title' => $booking->tour->title,
                        'description' => $booking->tour->description,
                        'duration' => $booking->tour->duration,
                        'is_transport_included' => $booking->tour->is_transport_included,
                        'is_food_included' => $booking->tour->is_food_included,
                    ],
                    'booked_date' => $booking->booked_date,
                    'start_time' => $booking->tourDate->start_time,
                    'end_time' => $booking->tourDate->end_time,
                    'group_size' => $booking->group_size,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $booking->updated_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }
    }
}
