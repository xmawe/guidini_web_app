<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class GuideController extends Controller
{
    /**
     * Register a new guide
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'biography' => 'required|string|max:1000',
                'languages' => 'required|array|min:1',
                'languages.*' => 'string|max:50',
                'attestation_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'years_of_experience' => 'required|integer|min:0|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Check if user is already a guide
            if (Guide::where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already registered as a guide'
                ], 409);
            }

            // Handle file upload
            $imageUrl = null;
            if ($request->hasFile('attestation_image')) {
                $image = $request->file('attestation_image');
                $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('guide_attestations', $imageName, 'public');
                $imageUrl = Storage::url($imagePath);
            }

            // Create guide record
            $guide = Guide::create([
                'user_id' => $user->id,
                'biography' => $request->biography,
                'languages' => $request->languages,
                'image_url' => $imageUrl,
                'years_of_experience' => $request->years_of_experience,
                'is_verified' => '0', // Default to not verified
                'rating' => 0.00,
            ]);

            // Assign guide role to user
            $user->assignRole('guide');

            return response()->json([
                'success' => true,
                'message' => 'Guide registration successful',
                'data' => [
                    'guide' => $guide->load('user'),
                    'profile_picture' => $user->profile_picture ?? null,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during guide registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get guide profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $guide = Guide::where('user_id', $user->id)->with('user')->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guide profile not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'guide' => $guide,
                    'profile_picture' => $user->profile_picture ?? null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching guide profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update guide profile
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $guide = Guide::where('user_id', $user->id)->first();

            if (!$guide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guide profile not found'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'biography' => 'sometimes|string|max:1000',
                'languages' => 'sometimes|array|min:1',
                'languages.*' => 'string|max:50',
                'attestation_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'years_of_experience' => 'sometimes|integer|min:0|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            // Update fields if provided
            if ($request->has('biography')) {
                $updateData['biography'] = $request->biography;
            }

            if ($request->has('languages')) {
                $updateData['languages'] = $request->languages;
            }

            if ($request->has('years_of_experience')) {
                $updateData['years_of_experience'] = $request->years_of_experience;
            }

            // Handle file upload if provided
            if ($request->hasFile('attestation_image')) {
                // Delete old image if exists
                if ($guide->image_url) {
                    $oldImagePath = str_replace('/storage/', '', $guide->image_url);
                    Storage::disk('public')->delete($oldImagePath);
                }

                $image = $request->file('attestation_image');
                $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('guide_attestations', $imageName, 'public');
                $updateData['image_url'] = Storage::url($imagePath);
            }

            // Update guide
            $guide->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Guide profile updated successfully',
                'data' => [
                    'guide' => $guide->fresh()->load('user'),
                    'profile_picture' => $user->profile_picture ?? null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating guide profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}