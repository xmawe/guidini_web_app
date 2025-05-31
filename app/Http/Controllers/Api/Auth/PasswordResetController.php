<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use App\Models\User;

class PasswordResetController
{
    public function requestReset(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Generate a random 6-digit token
        $token = random_int(100000, 999999);

        // Store the token in the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Send the token via email
        Mail::to($request->email)->send(new PasswordResetMail($token));

        return response()->json([
            'message' => 'Password reset token sent to ' . $request->email,
        ], 200);
    }

    public function verifyToken(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|digits:6' // Validate that the token is a 6-digit number
        ]);

        // Fetch the token data from the database
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Check if the token data exists and matches
        if (!$tokenData || $tokenData->token != $request->token) {
            return response()->json([
                'message' => 'Invalid code.'
            ], 400);
        }

        // Store the email and token in session or another secure storage method
        session([
            'password_reset_email' => $request->email,
            'password_reset_token' => $request->token,
        ]);

        return response()->json([
            'message' => 'Code verified.',
            'data' => [
                'email' => $request->email,
                'token' => $request->token
            ]
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|numeric',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData || $tokenData->token != $request->token) {
            return response()->json([
                'message' => 'Invalid code.'
            ], 400);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Remove password reset token after successful reset
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password has been reset.'
        ], 200);
    }
}
