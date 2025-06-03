<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BookingResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuideBookingsController
{
    /**
     * Get all bookings for the authenticated guide's tours
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();

            // Get the guide record for the authenticated user
            $guide = Guide::where('user_id', $userId)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a guide.'
                ], 403);
            }

            // Get query parameters for filtering
            $status = $request->query('status'); // pending, confirmed, cancelled, completed
            $dateFrom = $request->query('date_from');
            $dateTo = $request->query('date_to');
            $perPage = $request->query('per_page', 15);

            // Build the query
            $query = Booking::with(['user', 'tour.guide', 'tour.location', 'tour.city', 'tourDate'])
                ->whereHas('tour', function ($q) use ($guide) {
                    $q->where('guide_id', $guide->id);
                })
                ->orderBy('booked_date', 'desc')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->where('booked_date', '>=', Carbon::parse($dateFrom)->format('Y-m-d'));
            }

            if ($dateTo) {
                $query->where('booked_date', '<=', Carbon::parse($dateTo)->format('Y-m-d'));
            }

            // Get paginated results
            $bookings = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => BookingResource::collection($bookings->items()),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch guide bookings: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch bookings.'
            ], 500);
        }
    }

    /**
     * Get booking statistics for the authenticated guide
     */
    public function getStatistics()
    {
        try {
            $userId = Auth::id();

            $guide = Guide::where('user_id', $userId)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a guide.'
                ], 403);
            }

            $baseQuery = Booking::whereHas('tour', function ($q) use ($guide) {
                $q->where('guide_id', $guide->id);
            });

            $statistics = [
                'total_bookings' => (clone $baseQuery)->count(),
                'pending_bookings' => (clone $baseQuery)->where('status', 'pending')->count(),
                'confirmed_bookings' => (clone $baseQuery)->where('status', 'confirmed')->count(),
                'completed_bookings' => (clone $baseQuery)->where('status', 'completed')->count(),
                'cancelled_bookings' => (clone $baseQuery)->where('status', 'cancelled')->count(),
                'total_revenue' => (clone $baseQuery)->whereIn('status', ['confirmed', 'completed'])->sum('total_price'),
                'this_month_bookings' => (clone $baseQuery)->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)->count(),
                'upcoming_tours' => (clone $baseQuery)->where('booked_date', '>=', Carbon::now()->format('Y-m-d'))
                    ->whereIn('status', ['pending', 'confirmed'])->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch guide statistics: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch statistics.'
            ], 500);
        }
    }

/**
 * Accept a booking request
 */
public function acceptBooking(Request $request, $bookingId)
{
    try {
        $userId = Auth::id();

        $guide = Guide::where('user_id', $userId)->first();

        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a guide.'
            ], 403);
        }

        return DB::transaction(function () use ($bookingId, $guide) {
            // Find the booking and ensure it belongs to this guide's tours
            $booking = Booking::with(['user', 'tour.guide', 'tour.location', 'tour.city', 'tourDate'])
                ->whereHas('tour', function ($q) use ($guide) {
                    $q->where('guide_id', $guide->id);
                })
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or you do not have permission to manage it.'
                ], 404);
            }

            // Check if booking is in pending status
            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending bookings can be accepted. Current status: ' . $booking->status
                ], 400);
            }

            // Fix: Simplified date comparison - just check if booked_date is today or in the future
            try {
                $bookedDate = Carbon::parse($booking->booked_date)->startOfDay();
                $today = Carbon::now()->startOfDay();

                // Log for debugging
                Log::info('Date comparison debug', [
                    'booking_id' => $booking->id,
                    'booked_date' => $booking->booked_date,
                    'parsed_booked_date' => $bookedDate->toDateTimeString(),
                    'today' => $today->toDateTimeString(),
                    'is_past' => $bookedDate->lt($today)
                ]);

                // Check if the booking date is in the past (before today)
                if ($bookedDate->lt($today)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot accept bookings for past dates. Booking date: ' . $bookedDate->toDateString()
                    ], 400);
                }

            } catch (\Exception $dateException) {
                Log::error('Date parsing error', [
                    'booked_date' => $booking->booked_date,
                    'error' => $dateException->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid booking date format.'
                ], 400);
            }

            // Update booking status to confirmed
            $booking->update(['status' => 'confirmed']);

            Log::info('Booking accepted by guide', [
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking accepted successfully!',
                'data' => new BookingResource($booking)
            ]);
        });

    } catch (\Exception $e) {
        Log::error('Failed to accept booking: ' . $e->getMessage(), [
            'booking_id' => $bookingId,
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => "An error occurred while accepting the booking. Please try again.",
        ], 500);
    }
}

    /**
     * Decline a booking request
     */
public function declineBooking(Request $request, $bookingId)
{
    $request->validate([
        'decline_reason' => 'nullable|string|max:500'
    ]);

    try {
        $userId = Auth::id();

        $guide = Guide::where('user_id', $userId)->first();

        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a guide.'
            ], 403);
        }

        return DB::transaction(function () use ($bookingId, $guide, $request) {
            // Find the booking and ensure it belongs to this guide's tours
            $booking = Booking::with(['user', 'tour.guide', 'tour.location', 'tour.city', 'tourDate'])
                ->whereHas('tour', function ($q) use ($guide) {
                    $q->where('guide_id', $guide->id);
                })
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or you do not have permission to manage it.'
                ], 404);
            }

            // Check if booking is in pending status
            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending bookings can be declined. Current status: ' . $booking->status
                ], 400);
            }

            // Check if the booking date is still in the future (optional validation)
            try {
                if (strpos($booking->tourDate->start_time, ' ') !== false) {
                    // start_time already contains date and time
                    $bookingDateTime = Carbon::parse($booking->tourDate->start_time);
                } else {
                    // start_time is just time, combine with booked_date
                    $bookingDate = Carbon::parse($booking->booked_date)->format('Y-m-d');
                    $bookingDateTime = Carbon::parse($bookingDate . ' ' . $booking->tourDate->start_time);
                }

                // Optional: Prevent declining bookings that are too close to start time
                // if ($bookingDateTime->diffInHours(Carbon::now()) < 24) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Cannot decline bookings within 24 hours of the tour date.'
                //     ], 400);
                // }

            } catch (\Exception $dateException) {
                Log::error('Date parsing error in decline booking', [
                    'booked_date' => $booking->booked_date,
                    'start_time' => $booking->tourDate->start_time,
                    'error' => $dateException->getMessage()
                ]);

                // Continue with decline even if date parsing fails
                // as declining doesn't necessarily require date validation
            }

            // Update booking status to cancelled
            $updateData = ['status' => 'cancelled'];

            // If decline reason is provided, you might want to store it
            // You'll need to add a 'decline_reason' or 'notes' column to the bookings table
            if ($request->decline_reason) {
                // Uncomment this line if you have a decline_reason column
                // $updateData['decline_reason'] = $request->decline_reason;

                // Or store in notes column if available
                // $updateData['notes'] = $request->decline_reason;
            }

            $booking->update($updateData);

            Log::info('Booking declined by guide', [
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
                'user_id' => Auth::id(),
                'decline_reason' => $request->decline_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking declined successfully.',
                'data' => new BookingResource($booking)
            ]);
        });

    } catch (\Exception $e) {
        Log::error('Failed to decline booking: ' . $e->getMessage(), [
            'booking_id' => $bookingId,
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An error occurred while declining the booking. Please try again. ' . $e->getMessage(),
        ], 500);
    }
}
    /**
     * Get specific booking details for the guide
     */
    public function show($bookingId)
    {
        try {
            $userId = Auth::id();

            $guide = Guide::where('user_id', $userId)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a guide.'
                ], 403);
            }

            $booking = Booking::with([
                'user',
                'tour.guide',
                'tour.location',
                'tour.city',
                'tour.tourImages',
                'tour.activities',
                'tourDate'
            ])
                ->whereHas('tour', function ($q) use ($guide) {
                    $q->where('guide_id', $guide->id);
                })
                ->where('id', $bookingId)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or you do not have permission to view it.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new BookingResource($booking)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch booking details: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch booking details.'
            ], 500);
        }
    }

    /**
     * Mark booking as completed (after the tour is finished)
     */
    public function markAsCompleted(Request $request, $bookingId)
    {
        try {
            $userId = Auth::id();

            $guide = Guide::where('user_id', $userId)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a guide.'
                ], 403);
            }

            $booking = Booking::with(['user', 'tour.guide', 'tour.location', 'tour.city', 'tourDate'])
                ->whereHas('tour', function ($q) use ($guide) {
                    $q->where('guide_id', $guide->id);
                })
                ->where('id', $bookingId)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or you do not have permission to manage it.'
                ], 404);
            }

            if ($booking->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only confirmed bookings can be marked as completed.'
                ], 400);
            }

            // Check if the tour date has passed
            $bookingDateTime = Carbon::parse($booking->booked_date . ' ' . $booking->tourDate->end_time);
            if ($bookingDateTime->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark future bookings as completed.'
                ], 400);
            }

            $booking->update(['status' => 'completed']);

            Log::info('Booking marked as completed by guide', [
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking marked as completed successfully!',
                'data' => new BookingResource($booking)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark booking as completed: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Get customers who have booked with this guide
     */
    public function getCustomers(Request $request)
    {
        try {
            $userId = Auth::id();

            $guide = Guide::where('user_id', $userId)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a guide.'
                ], 403);
            }

            $perPage = $request->query('per_page', 15);
            $search = $request->query('search');

            // Get unique customers who have booked with this guide
            $query = \App\Models\User::whereHas('bookings.tour', function ($q) use ($guide) {
                $q->where('guide_id', $guide->id);
            })
            ->withCount(['bookings' => function ($q) use ($guide) {
                $q->whereHas('tour', function ($query) use ($guide) {
                    $query->where('guide_id', $guide->id);
                });
            }])
            ->distinct();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $customers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($customers->items()),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch guide customers: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch customers.'
            ], 500);
        }
    }
}
