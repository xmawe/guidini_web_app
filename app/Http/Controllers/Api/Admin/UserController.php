<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\Api\UserResource;
use App\Models\User;

class UserController
{
    public function index()
    {
        $users = UserResource::collection(User::get());
        return response()->json([
            'users' => $users,
        ]);
    }
}
