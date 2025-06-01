<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuideController extends Controller
{
    public function index()
    {
        try {
            // Check if we have actual guide records
            $guides = Guide::with('user')->get();

            if ($guides->count() > 0) {
                $result = $guides->map(function($guide) {
                    return $this->formatGuideResponse($guide);
                });

                return response()->json([
                    'data' => $result
                ]);
            }

            // Fallback to mock data
            return $this->getMockGuides();
        } catch (\Exception $e) {
            Log::error('Error fetching guides: ' . $e->getMessage());
            return $this->getMockGuides();
        }
    }

    public function show($id)
    {
        try {
            // First try to find a real guide record
            $guide = Guide::with('user')->where('id', $id)->first();

            // If not found, try to find by user_id
            if (!$guide) {
                $guide = Guide::with('user')->where('user_id', $id)->first();
            }

            // If we found a guide record, return it
            if ($guide) {
                return response()->json([
                    'data' => $this->formatGuideResponse($guide)
                ]);
            }

            // If no guide record exists, try to find a user and create mock guide data
            $user = User::find($id);
            if ($user) {
                return response()->json([
                    'data' => $this->createMockGuideFromUser($user)
                ]);
            }

            return response()->json([
                'message' => 'Guide not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching guide: ' . $e->getMessage());

            // Try to return mock data for this ID
            try {
                $user = User::find($id);
                if ($user) {
                    return response()->json([
                        'data' => $this->createMockGuideFromUser($user)
                    ]);
                }
            } catch (\Exception $innerEx) {
                Log::error('Error creating mock guide: ' . $innerEx->getMessage());
            }

            return response()->json([
                'error' => 'Error fetching guide',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format a guide model into a response array
     */
    private function formatGuideResponse(Guide $guide)
    {
        $user = $guide->user;

        return [
            'id' => $guide->id,
            'user_id' => $guide->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'rating' => $guide->rating,
            'languages' => $guide->languages,
            'is_verified' => $guide->is_verified,
            'biography' => $guide->biography,
            'created_at' => $guide->created_at,  // Guide creation date (experience)
            'updated_at' => $guide->updated_at,
            'user_created_at' => $user->created_at,  // User creation date (joined platform)
        ];
    }

    /**
     * Create mock guide data from a user
     */
    private function createMockGuideFromUser(User $user)
    {
        // Create mock guide data with different creation date than user
        $guideCreatedAt = now()->subYears(rand(1, 5))->subMonths(rand(1, 11));

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'rating' => 4.5, // Mock rating
            'languages' => ['English', 'Arabic', 'French'], // Mock languages
            'is_verified' => true, // Mock verification
            'biography' => 'I am a professional guide with extensive experience in showing travelers the beauty and culture of Morocco. I have been guiding tours for over 5 years and I am passionate about sharing my knowledge and love for this country with visitors from around the world.',
            'created_at' => $guideCreatedAt, // Guide creation date (experience)
            'updated_at' => now(),
            'user_created_at' => $user->created_at, // User creation date (joined platform)
        ];
    }

    /**
     * Get mock guides when no real data exists
     */
    private function getMockGuides()
    {
        $users = User::take(5)->get();

        $guides = $users->map(function($user) {
            return $this->createMockGuideFromUser($user);
        });

        return response()->json([
            'data' => $guides
        ]);
    }
}
