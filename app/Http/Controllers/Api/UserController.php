<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get user details by ID
     *
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::with('city')->findOrFail($id);

            // Build profile picture URL if it exists
            $profilePicture = $user->profile_picture;
            if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
                $profilePicture = url('storage/' . $profilePicture);
            }

            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'profile_picture' => $profilePicture,
                'phone_number' => $user->phone_number,
                'last_activity_at' => $user->last_activity_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            // Add city data if available
            if ($user->city) {
                $userData['city'] = [
                    'id' => $user->city->id,
                    'name' => $user->city->name,
                ];
            }

            // Check if user is online (active in the last 5 minutes)
            if ($user->last_activity_at) {
                $userData['is_online'] = $user->last_activity_at->diffInMinutes(now()) <= 5;
            } else {
                $userData['is_online'] = false;
            }

            return response()->json($userData);
        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());

            return response()->json([
                'error' => 'User not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
