<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OperatingHoursController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

/*
|--------------------------------------------------------------------------
| Publicly Accessible Routes
|--------------------------------------------------------------------------
| These routes do not require authentication.
| Includes providers, services, categories, and schedules.
*/
Route::prefix('providers')->controller(ProviderController::class)->group(function () {
    Route::get('search', 'search');
    Route::get('suggestions', 'searchSuggestions');
    Route::get('locations', 'getLocations');
});

Route::prefix('providers')->controller(AppointmentController::class)->group(function () {
    Route::get('{providerId}/available-slots', 'getAvailableSlots');
    Route::get('{providerId}/available-slots-range', 'getAvailableSlotsForRange');
    Route::get('{providerId}/schedule-info', 'getProviderScheduleInfo');
});

Route::prefix('categories')->controller(CategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{category}', 'show');
    Route::get('/{categoryId}/providers', 'indexByCategory'); // Renamed for clarity
});

// Route::get('{providerId}/operating-hours', [OperatingHoursController::class, 'index']);

Route::apiResource('providers', ProviderController::class)->only(['index', 'show']);

//get services by provider id
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::get('providers/{providerId}/services', [ServiceController::class, 'getServicesByProvider']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Roles)
|--------------------------------------------------------------------------
*/
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

    // ProviderController Routes
    Route::controller(ProviderController::class)->group(function () {
        Route::apiResource('providers', ProviderController::class)->except(['index', 'show']);
    });

    // ServiceController Routes - Full CRUD for providers
    Route::controller(ServiceController::class)->group(function () {
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::get('provider/services', 'getMyServices'); // Get services for authenticated provider
    });

    Route::controller(AppointmentController::class)->group(function () {
        Route::get('provider/calendar-appointments', 'indexForCalendar');
        Route::get('provider/appointments', 'indexForProvider');
        Route::get('provider/appointments/counts', 'getProviderAppointmentCounts');
        Route::post('appointments/{appointment}/confirm', 'confirmBookingProvider');
        Route::post('appointments/{appointment}/complete', 'completeBookingProvider');
        Route::post('appointments/{appointment}/cancel', 'cancelBookingProvider');
    });

    // OperatingHoursController Routes - For providers to manage their operating hours
    Route::controller(OperatingHoursController::class)->group(function () {
        Route::get('provider/operating-hours', 'getMyOperatingHours'); // Get operating hours for authenticated provider
        Route::put('provider/operating-hours', 'updateMyOperatingHours'); // Update operating hours for authenticated provider
    });
});

//user middleware to protect routes
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Example: A regular user can view their booking history
    // Route::get('my-bookings', [BookingController::class, 'index']);

    Route::controller(AppointmentController::class)->group(function () {
        Route::post('appointments', 'store');
        Route::get('user/appointments', 'indexForUser');
        Route::post('appointments/{appointment}/canceluser', 'cancelForUser');
    });
});

