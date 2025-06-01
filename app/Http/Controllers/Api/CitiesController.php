<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CitiesController extends Controller
{
    /**
     * Get all cities
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $cities = City::orderBy('name')->get();

            return response()->json([
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch cities',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific city by ID
     *
     * @param int $id City ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $city = City::findOrFail($id);

            return response()->json([
                'id' => $city->id,
                'name' => $city->name,
                'created_at' => $city->created_at,
                'updated_at' => $city->updated_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching city: ' . $e->getMessage());

            return response()->json([
                'error' => 'City not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
