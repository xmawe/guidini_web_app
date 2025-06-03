<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        if ($request->wantsJson()) {
            $user = $request->user();

            // Update last activity timestamp to mark user as online
            $user->last_activity_at = now();
            $user->save();

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'profile_picture' => $user->profile_picture,
                        'city_id' => $user->city_id,
                        'is_guide' => $user->is_guide,
                        'is_online' => true,
                        'last_activity_at' => $user->last_activity_at,
                    ]
                ]
            ]);
        }

        $request->session()->regenerate();

        // Update last activity timestamp for web login as well
        $request->user()->update(['last_activity_at' => now()]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        if ($request->wantsJson()) {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get authenticated user details (API)
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        // Update last activity timestamp to refresh online status
        $user->last_activity_at = now();
        $user->save();

        // Check if user is online (active in the last 5 minutes)
        $isOnline = $user->last_activity_at >= now()->subMinutes(5);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'profile_picture' => $user->profile_picture,
                    'city_id' => $user->city_id,
                    'is_guide' => $user->is_guide,
                    'is_online' => $isOnline,
                    'last_activity_at' => $user->last_activity_at,
                ]
            ]
        ]);
    }
}
