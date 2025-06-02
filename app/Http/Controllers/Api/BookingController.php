<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['tour', 'tour.guide.user'])->get();
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
    'tour_id' => 'required|exists:tours,id',
    'booked_date' => 'required|date',
    'group_size' => 'required|integer|min:1',
    'special_requests' => 'nullable|string',
]);

$booking = Booking::create([
    'tour_id' => $validated['tour_id'],
    'booked_date' => $validated['booked_date'],
    'group_size' => $validated['group_size'],
    'status' => 'pending',
    'total_price' => $this->calculateTotalPrice($validated['tour_id'], $validated['group_size']),
    'user_id' => 1, // à adapter selon utilisateur connecté
]);


        return response()->json($booking->load('tour'), 201);
    }

    public function show(Booking $booking)
    {
        return response()->json($booking->load(['tour', 'tour.guide.user']));
    }

    public function myBookings()
    {
        // Ici on récupère les bookings du user_id 1
        $bookings = Booking::where('user_id', 1)
            ->with(['tour', 'tour.guide.user', 'tour.location'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    private function calculateTotalPrice($tourId, $participantsCount)
    {
        $tour = Tour::find($tourId);
        return $tour->price * $participantsCount;
    }
}
