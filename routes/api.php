<?php

use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\CitiesContoller;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Guide\TourController;

Route::get('/cities', [CitiesContoller::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/password/request-reset', [PasswordResetController::class, 'requestReset']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyToken']);



Route::group(['middleware' => ['auth']], function () {

    Route::post('/user/edit/personal', [ProfileController::class, 'updatePersonalInfo']);
    Route::post('/user/edit/security', [ProfileController::class, 'updateSecurityInfo']);

});

Route::group(['middleware' => ['auth:sanctum', 'role:guide']], function () {
    Route::get('/users', [UserController::class, 'index']);
});


// Public tour routes (no authentication required)
Route::get('/tours/all', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/tours/city/{cityId}', [TourController::class, 'getByCity']);
Route::get('/tours/guide/{guideId}', [TourController::class, 'getByGuide']);
Route::get('/tours/{id}/dates', [TourController::class, 'getTourDates']);

// Protected tour routes (authentication required)
Route::group(['middleware' => ['auth:sanctum', 'role:guide'], 'prefix'=>'guide'], function () {
    Route::get('/tours', [TourController::class, 'myTours']);
});


    Route::get('/tours', [TourController::class, 'create']);
    Route::post('/tours', [TourController::class, 'store']);
    // Route::get('/tours/{id}', [TourController::class, 'edit']);
    // Route::put('/tours/{id}', [TourController::class, 'update']);
    // Route::patch('/tours/{id}', [TourController::class, 'update']);
    // Route::delete('/tours/{id}', [TourController::class, 'destroy']);
