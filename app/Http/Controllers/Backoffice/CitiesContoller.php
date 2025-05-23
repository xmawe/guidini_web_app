<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CitiesContoller
{
    public function index(Request $request)
    {
        $search = $request->input('keyword', '');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');

        // Load relationships and their counts without soft delete check
        $query = City::query()->withCount(['users', 'tours']);


        if (strlen($search) > 0) {
        $query->where('name', 'like', "%{$search}%");
    }

        // Handle different sort columns
        switch ($sortBy) {
            case 'userCount':
                $query->orderBy('users_count', $sortOrder);
                break;
            case 'tourCount':
                $query->orderBy('tours_count', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            default:
                $query->orderBy('id', $sortOrder);
        }

        $cities = $query->paginate($perPage)->withQueryString();
        $totalCities = City::count();

        // Get the most active city
        $mostActiveCity = City::withCount(['users', 'tours'])
            ->orderByRaw('(users_count + tours_count) DESC')
            ->first();

        return inertia("backoffice/cities/index", [
            'cities' => CityResource::collection($cities),
            'filters' => [
                'keyword' => $search,
                'perPage' => $perPage
            ],
            'sort' => [
                'by' => $sortBy,
                'order' => $sortOrder
            ],
            'metrics' => [
                'totalCities' => $totalCities,
                'activeCities' => City::has('users')->count(),
                'mostActiveCity' => [
                    'name' => $mostActiveCity?->name ?? 'N/A',
                    'total' => $mostActiveCity ? ($mostActiveCity->users_count + $mostActiveCity->tours_count) : 0,
                    'userCount' => $mostActiveCity?->users_count ?? 0,
                    'tourCount' => $mostActiveCity?->tours_count ?? 0,
                ],
            ],
        ]);
    }

    public function create()
    {
        return inertia('backoffice/cities/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities'
        ]);

        $city = City::create($validated);

        return redirect()->back()
            ->with('success', 'City created successfully.');
    }

    public function edit(City $city)
    {
        return inertia('backoffice/cities/edit', [
            'city' => new CityResource($city)
        ]);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('cities')->ignore($city->id)]
        ]);

        $city->update($validated);

        return redirect()->back()
            ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        if ($city->users()->exists() || $city->tours()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete city with associated users or tours.');
        }

        $city->delete();

        return redirect()->back()
            ->with('success', 'City deleted successfully.');
    }
}
