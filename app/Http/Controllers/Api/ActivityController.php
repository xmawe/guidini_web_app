<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Récupérer toutes les activités
     */
    public function index()
    {
        try {
            $activities = Activity::with(['tour', 'location', 'activityCategory'])
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'duration' => $activity->duration,
                        'price' => $activity->price,
                        'tour_id' => $activity->tour_id,
                        'location_id' => $activity->location_id,
                        'activity_category_id' => $activity->activity_category_id,
                        'category' => $activity->activityCategory ? [
                            'id' => $activity->activityCategory->id,
                            'name' => $activity->activityCategory->name ?? 'Non définie'
                        ] : null,
                        'tour' => $activity->tour ? [
                            'id' => $activity->tour->id,
                            'name' => $activity->tour->name ?? $activity->tour->title
                        ] : null,
                        'location' => $activity->location ? [
                            'id' => $activity->location->id,
                            'name' => $activity->location->name ?? $activity->location->city
                        ] : null,
                        'created_at' => $activity->created_at,
                        'updated_at' => $activity->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $activities,
                'message' => 'Activités récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une activité spécifique
     */
    public function show($id)
    {
        try {
            $activity = Activity::with(['tour', 'location', 'activityCategory'])->find($id);

            if (!$activity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activité non trouvée'
                ], 404);
            }

            $activityData = [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'duration' => $activity->duration,
                'price' => $activity->price,
                'tour_id' => $activity->tour_id,
                'location_id' => $activity->location_id,
                'activity_category_id' => $activity->activity_category_id,
                'category' => $activity->activityCategory ? [
                    'id' => $activity->activityCategory->id,
                    'name' => $activity->activityCategory->name ?? 'Non définie'
                ] : null,
                'tour' => $activity->tour ? [
                    'id' => $activity->tour->id,
                    'name' => $activity->tour->name ?? $activity->tour->title
                ] : null,
                'location' => $activity->location ? [
                    'id' => $activity->location->id,
                    'name' => $activity->location->name ?? $activity->location->city
                ] : null,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at
            ];

            return response()->json([
                'success' => true,
                'data' => $activityData,
                'message' => 'Activité récupérée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'activité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les activités par catégorie
     */
    public function getByCategory($categoryId)
    {
        try {
            $activities = Activity::with(['tour', 'location', 'activityCategory'])
                ->where('activity_category_id', $categoryId)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'duration' => $activity->duration,
                        'price' => $activity->price,
                        'tour_id' => $activity->tour_id,
                        'location_id' => $activity->location_id,
                        'activity_category_id' => $activity->activity_category_id,
                        'category' => $activity->activityCategory ? [
                            'id' => $activity->activityCategory->id,
                            'name' => $activity->activityCategory->name ?? 'Non définie'
                        ] : null,
                        'created_at' => $activity->created_at,
                        'updated_at' => $activity->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $activities,
                'message' => 'Activités de la catégorie récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités par catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer toutes les catégories d'activités
     */
    public function getCategories()
    {
        try {
            $categories = ActivityCategory::withCount('activities')->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Catégories d\'activités récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
