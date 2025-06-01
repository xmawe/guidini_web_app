<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CitiesController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\TestChatController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\UserController;

// API Auth Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Auth Routes
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

// Tour Routes
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/guides/{guideId}/tours', [TourController::class, 'getToursByGuideId']);
Route::get('/cities', [CitiesController::class, 'index']);
Route::get('/cities/{id}', [CitiesController::class, 'show']);

// Guide Routes
Route::get('/guides', [GuideController::class, 'index']);
Route::get('/guides/{id}', [GuideController::class, 'show']);

// User Routes
Route::get('/users/{id}', [UserController::class, 'show']);

// Test Chat Routes (no auth required)
Route::prefix('test/chat')->group(function () {
    Route::get('/rooms/all', [TestChatController::class, 'getAllChatRooms']);
    Route::get('/rooms', [TestChatController::class, 'getChatRooms']);
    Route::get('/rooms/{chatRoom}/messages', [TestChatController::class, 'getMessages']);
    Route::post('/rooms/{chatRoom}/messages', [TestChatController::class, 'sendMessage']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::get('/user', [AuthenticatedSessionController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // Chat Routes
    Route::prefix('chat')->group(function () {
        Route::get('/search', [ChatController::class, 'searchConversations']);
        Route::get('/rooms', [ChatController::class, 'getChatRooms']);
        Route::get('/rooms/{chatRoom}', [ChatController::class, 'getChatRoomDetails']);
        Route::get('/rooms/{chatRoom}/messages', [ChatController::class, 'getMessages']);
        Route::get('/rooms/{chatRoom}/search', [ChatController::class, 'searchMessages']);
        Route::post('/rooms', [ChatController::class, 'createOrGetChatRoom']);
        Route::post('/rooms/{chatRoom}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/rooms/{chatRoom}/read', [ChatController::class, 'markAsRead']);
        Route::post('/activity', [ChatController::class, 'updateLastActivity']);
    });
});
