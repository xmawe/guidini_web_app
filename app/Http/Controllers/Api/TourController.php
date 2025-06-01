<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('tours')
                ->join('users', 'tours.guide_id', '=', 'users.id')
                ->select(
                    'tours.id',
                    'tours.title',
                    'tours.description',
                    'tours.price',
                    'tours.duration',
                    'tours.location_id',
                    'tours.city_id',
                    DB::raw('COALESCE((SELECT name FROM cities WHERE id = tours.city_id), "Unknown City") as city'),
                    'tours.max_group_size',
                    'tours.availability_status',
                    'tours.is_transport_included',
                    'tours.is_food_included',
                    'users.id as guide_id',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as guide_name'),
                    DB::raw('4.5 as guide_rating'), // For demo, we'll use a fixed rating
                    DB::raw('4 as activities_count') // Placeholder for activities count
                );

            // Filter by location if provided
            if ($request->has('location') && $request->location) {
                // Try to filter by city if the table exists
                try {
                    $query->join('cities', 'tours.city_id', '=', 'cities.id')
                        ->where('cities.name', 'like', '%' . $request->location . '%');
                } catch (\Exception $e) {
                    // If cities table doesn't exist, just continue without this filter
                    Log::warning("Cities table join failed: " . $e->getMessage());
                }
            }

            $tours = $query->get();

            return response()->json([
                'data' => $tours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching tours: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getToursByGuideId($guideId)
    {
        try {
            $query = DB::table('tours')
                ->join('users', 'tours.guide_id', '=', 'users.id')
                ->select(
                    'tours.id',
                    'tours.title',
                    'tours.description',
                    'tours.price',
                    'tours.duration',
                    'tours.location_id',
                    'tours.city_id',
                    DB::raw('"Marrakech" as city'), // Fallback city name
                    'tours.max_group_size',
                    'tours.availability_status',
                    'tours.is_transport_included',
                    'tours.is_food_included',
                    'users.id as guide_id',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as guide_name'),
                    DB::raw('4.5 as guide_rating'), // For demo, we'll use a fixed rating
                    DB::raw('4 as activities_count') // Placeholder for activities count
                )
                ->where('tours.guide_id', $guideId);

            // Try to join with cities table if it exists
            try {
                $query->leftJoin('cities', 'tours.city_id', '=', 'cities.id')
                      ->selectRaw('COALESCE(cities.name, "Marrakech") as city');
            } catch (\Exception $e) {
                // If cities table doesn't exist, just continue with fallback city
                Log::warning("Cities table join failed: " . $e->getMessage());
            }

            $tours = $query->get();

            return response()->json([
                'data' => $tours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching tours for guide: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $query = DB::table('tours')
                ->join('users', 'tours.guide_id', '=', 'users.id')
                ->select(
                    'tours.id',
                    'tours.title',
                    'tours.description',
                    'tours.price',
                    'tours.duration',
                    'tours.location_id',
                    'tours.city_id',
                    DB::raw('"Marrakech" as city'), // Fallback city name
                    'tours.max_group_size',
                    'tours.availability_status',
                    'tours.is_transport_included',
                    'tours.is_food_included',
                    'users.id as guide_id',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as guide_name'),
                    DB::raw('4.5 as guide_rating'), // For demo, we'll use a fixed rating
                    DB::raw('4 as activities_count') // Placeholder for activities count
                )
                ->where('tours.id', $id);

            // Try to join with cities table if it exists
            try {
                $query->leftJoin('cities', 'tours.city_id', '=', 'cities.id')
                      ->selectRaw('COALESCE(cities.name, "Marrakech") as city');
            } catch (\Exception $e) {
                // If cities table doesn't exist, just continue with fallback city
                Log::warning("Cities table join failed: " . $e->getMessage());
            }

            $tour = $query->first();

            if (!$tour) {
                return response()->json([
                    'message' => 'Tour not found'
                ], 404);
            }

            return response()->json([
                'data' => $tour
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching tour: ' . $e->getMessage()
            ], 500);
        }
    }
}
