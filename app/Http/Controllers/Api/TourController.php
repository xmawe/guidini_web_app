<?php

namespace App\Http\Controllers\Api;

use App\Models\Tour;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TourController extends Controller
{
    /**
     * Display a listing of tours with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get total count for metadata
            $toursCount = Tour::count();

            // Start building query with relationships
            $query = Tour::with(['city', 'guide', 'location']);

            // Apply search filter
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply location filter
            if ($request->has('location_id') && !empty($request->get('location_id'))) {
                $query->where('location_id', $request->get('location_id'));
            }

            // Apply city filter (if needed)
            if ($request->has('city_id') && !empty($request->get('city_id'))) {
                $query->where('city_id', $request->get('city_id'));
            }

            // Apply guide filter (if needed)
            if ($request->has('guide_id') && !empty($request->get('guide_id'))) {
                $query->where('guide_id', $request->get('guide_id'));
            }

            // Get results with pagination
            $tours = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $tours,
                'total_tours' => $toursCount,
                'message' => 'Tours retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tours',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Display the specified tour with all relationships
     */
    public function show($id): JsonResponse
    {
        try {
            $tour = Tour::with(['city', 'guide', 'location', 'images'])
                        ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tour,
                'message' => 'Tour retrieved successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified tour
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $tour = Tour::findOrFail($id);
            $tour->update($request->all());

            // Refresh the tour with its relationships
            $tour = Tour::with(['city', 'guide', 'location', 'images'])
                        ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tour,
                'message' => 'Tour updated successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tour from storage
     */
    public function destroy($id): JsonResponse
    {
        try {
            $tour = Tour::findOrFail($id);
            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tour deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tour
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $tour = Tour::create($request->all());

            // Load relationships for the response
            $tour = Tour::with(['city', 'guide', 'location', 'images'])
                        ->findOrFail($tour->id);

            return response()->json([
                'success' => true,
                'data' => $tour,
                'message' => 'Tour created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
