<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\UpdateUserSecurityInfosRequest;
use App\Http\Resources\GuideResource;
use App\Http\Resources\UserResource;
use App\Models\Guide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuideController
{

       public function show($id)
    {
        $tour = Guide::with(['user', 'tours.activities.location', 'tours.tourImages'])->findOrFail($id);
        return new GuideResource($tour);
    }
}
