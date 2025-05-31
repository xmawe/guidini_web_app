<?php

namespace App\Http\Controllers\Api\Guide;

use App\Http\Requests\Api\StoreTourRequest;
use App\Http\Requests\Api\UpdateTourRequest;
use App\Http\Resources\ActivityCategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Models\Guide;
use App\Models\Activity;
use App\Models\TourDate;
use App\Models\TourImage;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TourController
{

    /**
     * Display a listing of tours
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tour::with([
            'location',
            'city',
            'tourImages',
            'activities.location',
            'activities.activityCategory',
            'tourDates'
        ]);

        // Filter by guide if user is authenticated and is a guide
        if (Auth::check() && Auth::user()->isGuide()) {
            $guide = Guide::where('user_id', Auth::id())->first();
            if ($guide && $request->has('my_tours') && $request->my_tours) {
                $query->where('guide_id', $guide->id);
            }
        }

        // Filter by availability status
        if ($request->has('availability_status') && $request->availability_status) {
            $query->where('availability_status', $request->availability_status);
        }

        $tours = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tours,
        ]);
    }

    public function myTours(Request $request): JsonResponse
    {
        // Check if the user is authenticated and is a guide
        if (!Auth::check() || !Auth::user()->isGuide()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or not a guide.',
            ], 403);
        }

        // Get the guide associated with the authenticated user
        $guide = Guide::where('user_id', Auth::id())->first();

        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide profile not found.',
            ], 404);
        }

        // Build the query for the guide's tours
        $query = Tour::with([
            'location',
            'city',
            'tourImages',
            'activities.location',
            'activities.activityCategory',
            'tourDates'
        ])->where('guide_id', $guide->id);

        // Optional: filter by availability status
        if ($request->has('availability_status') && $request->availability_status) {
            $query->where('availability_status', $request->availability_status);
        }

        $tours = $query->get();

        return response()->json([
            'success' => true,
            'data' => TourResource::collection($tours),
        ]);
    }


    /**
     * Show the form for creating a new tour
     */
    public function create(): JsonResponse
    {
        // Return necessary data for tour creation form
        $activityCategories =  ActivityCategoryResource::collection(\App\Models\ActivityCategory::all());
        $cities = CityResource::collection(\App\Models\City::all());

        return response()->json([
            'success' => true,
            'data' => [
                'activityCategories' => $activityCategories,
                'cities' => $cities,
            ],
        ]);
    }

    /**
     * Store a newly created tour
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // // Get the authenticated guide
            // $guide = Guide::where('user_id', Auth::id())->firstOrFail();

            // Create the tour location from the first activity
            $firstActivityLocation = $this->createLocationFromCoordinates(
                $request->activities[0]['location']
            );

            // Create the tour
            $tour = Tour::create([
                'guide_id' => 1,
                'location_id' => $firstActivityLocation->id,
                'city_id' => $request->city_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'duration' => $request->duration,
                'max_group_size' => $request->max_group_size,
                'availability_status' => $request->availability_status ?? 'available',
                'is_transport_included' => $request->is_transport_included ?? false,
                'is_food_included' => $request->is_food_included ?? false,
            ]);

            // Create activities
            $this->createActivities($tour, $request->activities);

            // Create tour dates
            if ($request->has('tour_dates')) {
                $this->createTourDates($tour, $request->tour_dates);
            }

            // Handle tour images
            if ($request->has('images')) {
                $this->handleTourImages($tour, $request->images);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour created successfully',
                'data' => new TourResource($tour->load([
                    'guide.user',
                    'location',
                    'city',
                    'activities.location',
                    'activities.activityCategory',
                    'tourDates',
                    'tourImages'
                ])),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tour
     */
    public function show(Tour $tour): JsonResponse
    {
        $tour->load([
            'guide.user',
            'location',
            'city',
            'activities.location',
            'activities.activityCategory',
            'tourDates',
            'tourImages'
        ]);

        return response()->json([
            'success' => true,
            'data' => new TourResource($tour),
        ]);
    }

    /**
     * Show the form for editing the specified tour
     */
    public function edit(Tour $tour): JsonResponse
    {
        // Check if the authenticated user is the owner of this tour
        $guide = Guide::where('user_id', Auth::id())->first();

        if (!$guide || $tour->guide_id !== $guide->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this tour',
            ], 403);
        }

        $tour->load([
            'guide.user',
            'location',
            'city',
            'activities.location',
            'activities.activityCategory',
            'tourDates',
            'tourImages'
        ]);

        $activityCategories = \App\Models\ActivityCategory::all();
        $cities = \App\Models\City::all();

        return response()->json([
            'success' => true,
            'data' => [
                'tour' => $tour,
                'activity_categories' => $activityCategories,
                'cities' => $cities,
            ],
        ]);
    }

    /**
     * Update the specified tour
     */
    public function update(UpdateTourRequest $request, Tour $tour): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check ownership
            $guide = Guide::where('user_id', Auth::id())->firstOrFail();

            if ($tour->guide_id !== $guide->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this tour',
                ], 403);
            }

            // Update tour location if activities have changed
            if ($request->has('activities') && count($request->activities) > 0) {
                $firstActivityLocation = $this->createLocationFromCoordinates(
                    $request->activities[0]['location']
                );
                $tour->location_id = $firstActivityLocation->id;
            }

            // Update tour
            $tour->update([
                'city_id' => $request->city_id ?? $tour->city_id,
                'title' => $request->title ?? $tour->title,
                'description' => $request->description ?? $tour->description,
                'price' => $request->price ?? $tour->price,
                'duration' => $request->duration ?? $tour->duration,
                'max_group_size' => $request->max_group_size ?? $tour->max_group_size,
                'availability_status' => $request->availability_status ?? $tour->availability_status,
                'is_transport_included' => $request->is_transport_included ?? $tour->is_transport_included,
                'is_food_included' => $request->is_food_included ?? $tour->is_food_included,
            ]);

            // Update activities if provided
            if ($request->has('activities')) {
                $this->updateActivities($tour, $request->activities);
            }

            // Update tour dates if provided
            if ($request->has('tour_dates')) {
                $this->updateTourDates($tour, $request->tour_dates);
            }

            // Handle tour images if provided
            if ($request->has('images')) {
                $this->handleTourImages($tour, $request->images, true);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully',
                'data' => $tour->load([
                    'guide.user',
                    'location',
                    'city',
                    'activities.location',
                    'activities.activityCategory',
                    'tourDates',
                    'tourImages'
                ]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tour
     */
    public function destroy(Tour $tour): JsonResponse
    {
        try {
            // Check ownership
            $guide = Guide::where('user_id', Auth::id())->first();

            if (!$guide || $tour->guide_id !== $guide->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this tour',
                ], 403);
            }

            DB::beginTransaction();

            // Delete tour images from storage
            $tourImages = $tour->tourImages;
            foreach ($tourImages as $image) {
                if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }

            // Delete the tour (cascade delete should handle related records)
            $tour->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create location from coordinates
     */
    private function createLocationFromCoordinates(array $locationData): Location
    {
        return Location::create([
            'longitude' => $locationData['longitude'],
            'latitude' => $locationData['latitude'],
            'label' => $locationData['label'] ?? 'Tour Location',
        ]);
    }

    /**
     * Create activities for a tour
     */
    private function createActivities(Tour $tour, array $activities): void
    {
        foreach ($activities as $activityData) {
            $location = $this->createLocationFromCoordinates($activityData['location']);

            Activity::create([
                'tour_id' => $tour->id,
                'location_id' => $location->id,
                'activity_category_id' => $activityData['activity_category_id'],
                'title' => $activityData['title'],
                'description' => $activityData['description'],
                'duration' => $activityData['duration'],
                'price' => $activityData['price'] ?? 0,
            ]);
        }
    }

    /**
     * Update activities for a tour
     */
    private function updateActivities(Tour $tour, array $activities): void
    {
        // Delete existing activities
        $tour->activities()->delete();

        // Create new activities
        $this->createActivities($tour, $activities);
    }

    /**
     * Create tour dates
     */
    private function createTourDates(Tour $tour, array $tourDates): void
    {
        foreach ($tourDates as $dateData) {
            TourDate::create([
                'tour_id' => $tour->id,
                'day_of_week' => $dateData['day_of_week'],
                'start_time' => $dateData['start_time'],
                'end_time' => $dateData['end_time'],
            ]);
        }
    }

    /**
     * Update tour dates
     */
    private function updateTourDates(Tour $tour, array $tourDates): void
    {
        // Delete existing tour dates
        $tour->tourDates()->delete();

        // Create new tour dates
        $this->createTourDates($tour, $tourDates);
    }

    /**
     * Handle tour images upload
     */
    private function handleTourImages(Tour $tour, array $images, bool $isUpdate = false): void
    {
        if ($isUpdate) {
            // Delete existing images
            $existingImages = $tour->tourImages;
            foreach ($existingImages as $image) {
                if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
            $tour->tourImages()->delete();
        }

        foreach ($images as $image) {
            if ($image->isValid()) {
                $path = $image->store('tours', 'public');

                TourImage::create([
                    'tour_id' => $tour->id,
                    'image_url' => $path,
                ]);
            }
        }
    }
}
