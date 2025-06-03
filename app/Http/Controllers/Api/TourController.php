<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Location;
use App\Models\User;
use App\Http\Resources\TourResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::with(['location', 'city', 'guide.user', 'activities.location', 'tourImages']);

        // Filter by keyword
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%");
            });
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->input('city_id'));
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('activities', function ($q) use ($request) {
                $q->where('activity_category_id', $request->input('category_id'));
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Location-based filtering
        if ($request->filled('location')) {
            $locationName = $request->input('location');
            if ($locationName !== 'Select Location') {
                $query->whereHas('city', function ($q) use ($locationName) {
                    $q->where('name', $locationName);
                });
                $tours = $query->limit(10)->get();
            } else {
                // Show random tours if no location selected
                $tours = $query->inRandomOrder()->limit(10)->get();
            }
        } else {
            $tours = $query->limit(10)->get();
        }

        return TourResource::collection($tours);
    }

    public function show($id)
    {
        $tour = Tour::with(['location', 'reviews','city', 'guide.user', 'activities.location', 'tourImages'])->findOrFail($id);
        return new TourResource($tour);
    }

    public function updateUserLocation(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            // Find the closest location based on coordinates
            $location = Location::select('*')
                ->selectRaw('
                    ( 6371 * acos( cos( radians(?) ) *
                      cos( radians( latitude ) ) *
                      cos( radians( longitude ) - radians(?) ) +
                      sin( radians(?) ) *
                      sin( radians( latitude ) ) )
                    ) AS distance
                ', [$latitude, $longitude, $latitude])
                ->having('distance', '<', 50) // Within 50km radius
                ->orderBy('distance')
                ->with('city')
                ->first();

            if ($location) {
                // Update user's location
                $user->location_id = $location->id;
                $user->city_id = $location->city_id;
                $user->save();

                return response()->json([
                    'success' => true,
                    'location' => $location->city->name,
                    'message' => 'Location updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'location' => 'Select Location',
                    'message' => 'No nearby location found'
                ], 404);
            }
        }

        return response()->json([
            'success' => false,
            'location' => 'Select Location',
            'message' => 'Latitude and longitude are required'
        ], 400);
    }

    public function getNearbyTours(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->location_id) {
            // Get tours in the same city as user's location
            $tours = Tour::with(['location', 'city', 'guide.user', 'activities'])
                ->where('city_id', $user->city_id)
                ->limit(10)
                ->get();

            return TourResource::collection($tours);
        }

        // If no user location, return random tours
        return $this->getRandomTours();
    }

    public function getRandomTours()
    {
        $tours = Tour::with(['location', 'city', 'guide.user', 'activities'])
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return TourResource::collection($tours);
    }

    public function getLocationBasedTours(Request $request)
    {
        $user = Auth::user();

        // Check if user has allowed location and has location set
        if ($user && $user->location_id) {
            // User has location - show nearby tours
            return $this->getNearbyTours($request);
        } else {
            // User hasn't allowed location or no location set - show random tours
            return $this->getRandomTours();
        }
    }
}
