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

        // user API
        Route::get('/user/{id}', [AuthController::class, 'edit']);
        Route::put('/user/{id}', [AuthController::class, 'update']);

        // Stores API
        Route::get('/store', [StoreApiController::class, 'index'])->name('stores.index');
        Route::get('/store/create', [StoreApiController::class, 'create']);
        Route::post('/store', [StoreApiController::class, 'store']);
        Route::get('/store/{id}', [StoreApiController::class, 'show']);
        Route::put('/store/{id}', [StoreApiController::class, 'update']);
        Route::delete('/store/{id}', [StoreApiController::class, 'destroy']);
    });


// Categories API
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);

// Sliders API
    Route::get('/sliders', [CategoryApiController::class, 'index']);
    Route::get('/sliders/{slider}', [CategoryApiController::class, 'show']);