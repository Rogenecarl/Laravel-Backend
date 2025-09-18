<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\Auth\AuthController;

// Login and Registration Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

// --- Publicly Viewable Data (Read-Only) ---
// We only allow the 'index' (list) and 'show' (single item) methods.
Route::get('providers/search', [ProviderController::class, 'search']);

// Add this new route for real-time search suggestions.
Route::get('providers/suggestions', [ProviderController::class, 'searchSuggestions']);

// 2. Now define the resource routes. Laravel will check for 'search' first.
Route::apiResource('providers', ProviderController::class)->only(['index', 'show']);

// It's good practice to group related routes.
Route::prefix('categories')->controller(CategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{category}', 'show');
    Route::get('/{categoryId}/providers', 'indexByCategory'); // Renamed for clarity
});

//get services by provider id
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);

// --- General Authenticated Routes (Any Role) ---
// Any logged-in user (admin, provider, or user) can access these.
Route::middleware('auth:sanctum')->group(function () {

    Route::get('user', [UserController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});

//admin middleware to protect routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

});

//provider middleware to protect routes
Route::middleware(['auth:sanctum', 'role:provider'])->group(function () {


    // Providers can manage their own profiles and services
    Route::apiResource('providers', ProviderController::class)->except(['index', 'show']);

    // Providers can manage their own services
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
});

//user middleware to protect routes
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Example: A regular user can view their booking history
    // Route::get('my-bookings', [BookingController::class, 'index']);
});

