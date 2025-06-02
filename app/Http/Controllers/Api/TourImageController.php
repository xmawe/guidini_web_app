<?php

namespace App\Http\Controllers;

use App\Models\TourImage;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TourImageController extends Controller
{
    /**
     * Get all tour images
     */
    public function index(): JsonResponse
    {
        try {
            $images = TourImage::with('tour')->get();

            return response()->json([
                'success' => true,
                'data' => $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'tour_id' => $image->tour_id,
                        'image_url' => $image->image_url,
                        'full_image_url' => $image->full_image_url,
                        'tour_title' => $image->tour->title ?? null,
                        'created_at' => $image->created_at,
                        'updated_at' => $image->updated_at,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tour images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific tour image
     */
    public function show($id): JsonResponse
    {
        try {
            $image = TourImage::with('tour')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $image->id,
                    'tour_id' => $image->tour_id,
                    'image_url' => $image->image_url,
                    'full_image_url' => $image->full_image_url,
                    'tour_title' => $image->tour->title ?? null,
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour image not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all images for a specific tour
     */
    public function getTourImages($tourId): JsonResponse
    {
        try {
            $tour = Tour::findOrFail($tourId);
            $images = TourImage::where('tour_id', $tourId)->get();

            return response()->json([
                'success' => true,
                'tour_id' => $tourId,
                'tour_title' => $tour->title,
                'images_count' => $images->count(),
                'data' => $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => $image->image_url,
                        'full_image_url' => $image->full_image_url,
                        'created_at' => $image->created_at,
                        'updated_at' => $image->updated_at,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching images for tour',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
