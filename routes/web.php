<?php

use App\Http\Controllers\Backoffice\CitiesController; 
use App\Http\Controllers\Backoffice\UsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('backoffice/dashboard');
    })->name('dashboard');

    Route::get('/cities', [CitiesController::class, 'index'])->name('cities.index');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';