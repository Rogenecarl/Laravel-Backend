<?php

use App\Http\Controllers\Category;
use App\Http\Controllers\ProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('providers', ProviderController::class);
Route::apiResource('categories', Category::class);
