<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StoreApiController;
use App\Http\Controllers\Api\CategoryApiController;


// User Authentication API
    Route::post('/register', [AuthController::class, 'registerClient']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Stores API
        Route::get('/store/create', [StoreApiController::class, 'create']);
        Route::post('/store', [StoreApiController::class, 'store']);
    });


// Categories API
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);

// Sliders API
    Route::get('/sliders', [CategoryApiController::class, 'index']);
    Route::get('/sliders/{slider}', [CategoryApiController::class, 'show']);