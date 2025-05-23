<?php

use App\Http\Controllers\Api\CitiesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TourController;

Route::get('/tours', [TourController::class, 'index']);
Route::get('/cities', [CitiesController::class, 'index']);
