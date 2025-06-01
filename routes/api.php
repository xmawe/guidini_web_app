<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\CitiesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/random', [TourController::class, 'getRandomTours']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/cities', [CitiesController::class, 'index']);

// Temporarily make this public for testing (remove after auth is added)
Route::post('/tours/update-location', [TourController::class, 'updateUserLocation']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tours/nearby', [TourController::class, 'getNearbyTours']);
    Route::get('/tours/location-based', [TourController::class, 'getLocationBasedTours']);
});

// Additional routes for other controllers...