<?php

use App\Http\Controllers\Api\ActivityCategoryController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\CitiesContoller;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Guide\TourController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MyBookingsController;

Route::get('/cities', [CitiesContoller::class, 'index']);
Route::get('/activity-categories', [ActivityCategoryController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/password/request-reset', [PasswordResetController::class, 'requestReset']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyToken']);


// Protected tour routes (authentication required) ALL USERS
Route::group(['middleware' => ['auth:sanctum']], function () {

    // User profile
    Route::post('/user/edit/personal', [ProfileController::class, 'updatePersonalInfo']);
    Route::post('/user/edit/security', [ProfileController::class, 'updateSecurityInfo']);
    //Tours
    Route::get('/tours/nearby', [App\Http\Controllers\Api\TourController::class, 'getNearbyTours']);
    Route::get('/tours/location-based', [App\Http\Controllers\Api\TourController::class, 'getLocationBasedTours']);
    Route::get('/tours', [App\Http\Controllers\Api\TourController::class, 'index']);
    Route::get('/tours/{id}', [App\Http\Controllers\Api\TourController::class, 'show']);
    Route::get('/guides/{id}', [GuideController::class, 'show']);
    Route::get('/tours/random', [App\Http\Controllers\Api\TourController::class, 'getRandomTours']);

    // Get available dates for a specific tour
    Route::get('/tours/{tour}/available-dates', [BookingController::class, 'getAvailableDates']);

    // Booking management
    Route::prefix('bookings')->group(function () {
        // Create a new booking
        Route::post('/', [BookingController::class, 'store']);

        // Get user's bookings
        Route::get('/', [MyBookingsController::class, 'getUserBookings']);

        // Get specific booking details
        Route::get('/{booking}', [BookingController::class, 'show']);

        // Cancel a booking
        Route::patch('/{booking}/cancel', [BookingController::class, 'cancel']);
    });

});


// Protected tour routes (authentication required) GUIDES
Route::group(['middleware' => ['auth:sanctum', 'role:guide'], 'prefix'=>'guide'], function () {
    Route::get('/tours', [TourController::class, 'myTours']);
    Route::get('/tours/create', [TourController::class, 'create']);
    Route::post('/tours', [TourController::class, 'store']);
});
