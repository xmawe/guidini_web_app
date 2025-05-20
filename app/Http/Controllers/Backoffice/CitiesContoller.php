<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;

class CitiesContoller extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $query = City::with( 'users');
        // Apply search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }
        $cities = $query->paginate(10);
        $totalCities = City::count();
        return inertia("backoffice/cities/index", [
            'cities' => CityResource::collection($cities),
            'search' => $search ?? null,
            'metrics' => ['totalCities' => $totalCities]
        ]);
    }
}
