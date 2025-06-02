<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $metrics = [
            'totalTours' => Tour::count(),
        ];

        $tours = Tour::orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($tour) {
                return [
                    'id' => $tour->id,
                    'title' => $tour->title,
                    'price' => $tour->price,
                    'duration' => $tour->duration,
                    'availabilityStatus' => $tour->availability_status,
                    'createdAt' => $tour->created_at->toDateTimeString(),
                    'updatedAt' => $tour->updated_at->toDateTimeString(),
                ];
            });

        return Inertia::render('backoffice/tours/Index', [
            'metrics' => $metrics,
            'tours' => $tours,
        ]);
    }

    public function edit($id)
{
    $tour = Tour::findOrFail($id);
    return Inertia::render('backoffice/tours/Edit', [
        'tour' => $tour,
    ]);
}

}
