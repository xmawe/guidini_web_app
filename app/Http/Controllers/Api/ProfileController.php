<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\UpdateUserSecurityInfosRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController
{
    /**
     * Display a listing of the resource.
     */
    public function updatePersonalInfo(UpdateUserRequest $request)
    {
        $user = Auth::user();
        $user->update($request->all());
        return response()->json(['message' => 'Informations mises à jour avec succès.', 'data' => new UserResource($user)]);
    }


    public function updateSecurityInfo(UpdateUserSecurityInfosRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->input('currentPassword'), $user->password)) {
            return response()->json([
                'error' => "L'ancien mot de passe est incorrect."
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->input('newPassword')),
        ]);

        return response()->json([
            'success' => 'Mot de passe mis à jour avec succès.'
        ], 200);
    }

}
