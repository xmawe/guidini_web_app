<?php

use App\Http\Controllers\Backoffice\CitiesContoller;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('backoffice/dashboard');
    })->name('dashboard');

    Route::resource('cities', CitiesContoller::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
