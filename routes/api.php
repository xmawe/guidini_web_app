<?php

use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ActivityController;
// use App\Http\Controllers\Api\TourImageController;
use App\Http\Controllers\Api\CitiesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Add the correct import for TourImageController if it exists elsewhere
use App\Http\Controllers\TourImageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Add this middleware to ensure JSON responses for all API routes
Route::middleware('api')->group(function () {

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'laravel_version' => app()->version()
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Cities routes
Route::get('/cities', [CitiesController::class, 'index']);

// Tours routes (Public)
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);

// Tour Images routes
Route::get('/tours/{id}/images', [TourImageController::class, 'getTourImages']);
Route::get('/tour-images', [TourImageController::class, 'index']);
Route::get('/tour-images/{id}', [TourImageController::class, 'show']);

// Guides routes (Public)
Route::get('/guides', [GuideController::class, 'index']);
Route::get('/guides/{id}', [GuideController::class, 'show']);

// Activities routes (Public)
Route::get('/activities', [ActivityController::class, 'index']);
Route::get('/activities/{id}', [ActivityController::class, 'show']);
Route::get('/activities/category/{categoryId}', [ActivityController::class, 'getByCategory']);

// Activity Categories routes
Route::get('/activity-categories', [ActivityController::class, 'getCategories']);

/*
|--------------------------------------------------------------------------
| TEMPORARY: No Auth Routes for Testing
|--------------------------------------------------------------------------
*/

// TEMPORARILY REMOVE AUTH MIDDLEWARE FOR TESTING
// Bookings routes (TESTING ONLY - NO AUTH)
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/my-bookings', [BookingController::class, 'myBookings']); // ← This one!
Route::get('/bookings/{id}', [BookingController::class, 'show']);
Route::put('/bookings/{id}', [BookingController::class, 'update']);
Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required) - COMMENTED OUT FOR TESTING
|--------------------------------------------------------------------------
*/

/*
Route::middleware(['auth:sanctum'])->group(function () {

    // User profile routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Bookings routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

});
*/

/*
|--------------------------------------------------------------------------
| Admin/Guide Routes (Role-based Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,guide'])->group(function () {

    // Tours management (Admin/Guide only)
    Route::post('/tours', [TourController::class, 'store']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);

    // Tour Images management
    Route::post('/tour-images', [TourImageController::class, 'store']);
    Route::put('/tour-images/{id}', [TourImageController::class, 'update']);
    Route::delete('/tour-images/{id}', [TourImageController::class, 'destroy']);

});

/*
|--------------------------------------------------------------------------
| Admin Only Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // Cities management (Admin only)
    Route::post('/cities', [CitiesController::class, 'store']);
    Route::put('/cities/{id}', [CitiesController::class, 'update']);
    Route::delete('/cities/{id}', [CitiesController::class, 'destroy']);

    // Guides management (Admin only)
    Route::post('/guides', [GuideController::class, 'store']);
    Route::put('/guides/{id}', [GuideController::class, 'update']);
    Route::delete('/guides/{id}', [GuideController::class, 'destroy']);

    // Activities management (Admin only)
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::put('/activities/{id}', [ActivityController::class, 'update']);
    Route::delete('/activities/{id}', [ActivityController::class, 'destroy']);

    // Activity Categories management (Admin only)
    Route::post('/activity-categories', [ActivityController::class, 'storeCategory']);
    Route::put('/activity-categories/{id}', [ActivityController::class, 'updateCategory']);
    Route::delete('/activity-categories/{id}', [ActivityController::class, 'destroyCategory']);

    // Advanced booking management (Admin only)
    Route::get('/all-bookings', [BookingController::class, 'adminIndex']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

});

}); // End of api middleware group
