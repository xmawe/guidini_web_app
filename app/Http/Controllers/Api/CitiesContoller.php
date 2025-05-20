<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CitiesContoller extends Controller
{
    public function index()
    {
        $query = City::get();
        return response()->json([
            'cities' => $query,
        ]);
    }
}
