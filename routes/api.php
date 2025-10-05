<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StoreApiController;
use App\Http\Controllers\Api\SliderApiController;
use App\Http\Controllers\Api\ProductApiController;
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
        Route::get('/store/create', [StoreApiController::class, 'create']);
        Route::post('/store/store', [StoreApiController::class, 'store']);
        Route::get('/store/{id}', [StoreApiController::class, 'show']);
        Route::put('/store/{id}', [StoreApiController::class, 'update']);
        Route::delete('/store/{id}', [StoreApiController::class, 'destroy']);
        
        
        // Products API
        Route::get('/product/create', [ProductApiController::class, 'create']);
        Route::post('/product/store', [ProductApiController::class, 'store']);
        Route::get('/product/{id}', [ProductApiController::class, 'show']);
        Route::put('/product/{id}', [ProductApiController::class, 'update']);
        Route::delete('/product/{id}', [ProductApiController::class, 'destroy']);
    });
    
// Stores API
    // Display nearby stores.
    Route::get('/store/nearby', [StoreController::class, 'nearby']);
    // Display all stores randomly.
    Route::get('/store', [StoreApiController::class, 'index']);

// Products API
    // Display all products of the store.
    Route::get('/product', [ProductApiController::class, 'index']);

// Categories API
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);

// Sliders API
    Route::get('/sliders', [SliderApiController::class, 'index']);
    Route::get('/sliders/{slider}', [SliderApiController::class, 'show']);