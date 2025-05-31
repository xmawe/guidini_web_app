<?php

namespace App\Http\Controllers\Api\Auth;
// Models

use App\Http\Requests\Api\Auth\LoginUserRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
// Utils
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    use HttpResponses;
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('', 'Credentials do not match our records.', 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user(); //
        $user->load('roles');

        // Generate a token for the authenticated user
        $token = $user->createToken('API Token of ' . $user->name)->plainTextToken;

        // Return a success response with the user data and token
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->all();
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'city_id' => $validated['city_id'],
        ]);
        // $user->assignRole('default');
        //$user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ]);
    }

    public function logout()
    {
        // Get the authenticated user
        $user = Auth::user();
        // Revoke all tokens for the authenticated user
        $user->tokens()->delete();
        // Return a success response
        return $this->success([
            'message' => 'Logged out successfully.'
        ]);
    }

}
