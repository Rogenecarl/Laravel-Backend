<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Http\Controllers\Service;
use App\Http\Controllers\Category;
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
// show all providers
Route::apiResource('providers', ProviderController::class)->only(['index', 'show']);

// show providers by category filter
Route::get('category/{categoryId}', [Category::class, 'indexByCategory']);
Route::apiResource('categories', Category::class)->only(['index', 'show']);

//get services by provider id
Route::apiResource('services', Service::class)->only(['index', 'show']);

// --- General Authenticated Routes (Any Role) ---
// Any logged-in user (admin, provider, or user) can access these.
Route::middleware('auth:sanctum')->group(function () {

    Route::get('user', [UserController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});

//admin middleware to protect routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::apiResource('categories', Category::class)->except(['index', 'show']);

    Route::get('statistics', function () {
        $userCount = \App\Models\User::count();
        $providerCount = \App\Models\Provider::count();
        $categoryCount = \App\Models\Categories::count();

        return response()->json([
            'users' => $userCount,
            'providers' => $providerCount,
            'categories' => $categoryCount,
        ]);
    });
});

//provider middleware to protect routes
Route::middleware(['auth:sanctum', 'role:provider'])->group(function () {


    // Providers can manage their own profiles and services
    Route::apiResource('providers', ProviderController::class)->except(['index', 'show']);

    // Providers can manage their own services
    Route::apiResource('services', Service::class)->except(['index', 'show']);
});

//user middleware to protect routes
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Example: A regular user can view their booking history
    // Route::get('my-bookings', [BookingController::class, 'index']);
});

