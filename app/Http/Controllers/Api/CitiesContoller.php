<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;

class CitiesContoller extends Controller
{
    public function index()
    {
        $cities = CityResource::collection(City::get());
        return response()->json([
            'cities' => $cities,
        ]);
    }
}
