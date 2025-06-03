<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MyBookingsController
{

    public function getUserBookings()
    {
            $userId = Auth::id();

            $bookings = Booking::with(['tour.guide.user', 'tour.location'])
                ->where('user_id', $userId)
                ->orderBy('booked_date', 'desc')
                ->get();

            return BookingResource::collection($bookings);

    }


}
