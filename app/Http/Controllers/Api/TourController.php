<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class TourController
{
    public function index()
    {
        // For now, return mock data
        return response()->json([
            ['id' => 1, 'title' => 'Safari Adventure', 'price' => 200],
            ['id' => 2, 'title' => 'City Tour', 'price' => 50],
        ]);
    }
}
