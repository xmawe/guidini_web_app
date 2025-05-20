<?php

use App\Http\Controllers\Api\CitiesContoller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TourController;

Route::get('/tours', [TourController::class, 'index']);
Route::get('/cities', [CitiesContoller::class, 'index']);
