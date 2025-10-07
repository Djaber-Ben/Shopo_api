<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StoreApiController;
use App\Http\Controllers\Api\SliderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\WishlistApiController;


// User Authentication API
    Route::post('/register', [AuthController::class, 'registerClient']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // user API
        Route::get('/user/{id}', [AuthController::class, 'edit'])->name('user.edit');
        Route::put('/user/{id}', [AuthController::class, 'update'])->name('user.update');

        // Stores API
        Route::get('/store/create', [StoreApiController::class, 'create'])->name('store.create');
        Route::post('/store/store', [StoreApiController::class, 'store'])->name('store.store');
        Route::get('/store/{id}', [StoreApiController::class, 'edit'])->name('store.edit');
        Route::put('/store/{id}', [StoreApiController::class, 'update'])->name('store.update');
        Route::delete('/store/{id}', [StoreApiController::class, 'destroy'])->name('store.destroy');
        
        
        // Products API
        Route::get('/product/create', [ProductApiController::class, 'create'])->name('product.create');
        Route::post('/product/store', [ProductApiController::class, 'store'])->name('product.store');
        Route::get('/product/{id}', [ProductApiController::class, 'edit'])->name('product.edit');
        Route::put('/product/{id}', [ProductApiController::class, 'update'])->name('product.update');
        Route::delete('/product/{id}', [ProductApiController::class, 'destroy'])->name('product.destroy');

        // Wishlist API
        Route::get('/wishlist', [WishlistApiController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/store', [WishlistApiController::class, 'store'])->name('wishlist.store');
        Route::delete('/wishlist/{product_id}', [WishlistApiController::class, 'destroy'])->name('wishlist.destroy');
    });
    
    // Stores API
    // Display nearby stores.
    Route::get('/store/nearby', [StoreApiController::class, 'nearby'])->name('store.nearby');
    Route::get('/store/{id}', [StoreApiController::class, 'show'])->name('store.show');
    // Display all stores randomly.
    Route::get('/store', [StoreApiController::class, 'index'])->name('store.index');
    
    // Products API
    // Display all products of the store.
    Route::get('/product', [ProductApiController::class, 'index'])->name('product.index');
    Route::get('/product/{id}', [ProductApiController::class, 'show'])->name('product.show');

// Categories API
    Route::get('/categories', [CategoryApiController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryApiController::class, 'show'])->name('categories.show');

// Sliders API
    Route::get('/sliders', [SliderApiController::class, 'index'])->name('sliders.index');
    Route::get('/sliders/{slider}', [SliderApiController::class, 'show'])->name('sliders.show');