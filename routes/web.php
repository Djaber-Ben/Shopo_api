<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OfflinePaymentController;
use App\Http\Controllers\Auth\Admin\AdminController;
use App\Http\Controllers\SubscriptionPlanController;


// Route::group(['middleware' => 'guest'], function(){
    Route::get('/login',[AdminController::class, 'Login'])->name('admin.login');
    Route::post('/authenticate',[AdminController::class, 'authenticate'])->name('admin.authenticate');
// });

Route::middleware(['admin'])->group(function () {
    Route::get('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Route::get('/admin/resetpassword', [ResetController::class, 'showResetForm'])->name('admin.user.resetpassword.form');
    // Route::post('/admin/resetpassword',[ResetController::class, 'ResetPassword'])->name('admin.user.resetpassword');

    ### Category ###
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories/upload', [CategoryController::class, 'upload'])->name('categories.upload');
        // Route::resource('categories', CategoryController::class);
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');
    ### Category ###
    
    ### Slider ###
        Route::get('/sliders', [SliderController::class, 'index'])->name('sliders.index');
        Route::get('/sliders/create', [SliderController::class, 'create'])->name('sliders.create');
        Route::post('/sliders/upload', [SliderController::class, 'upload'])->name('sliders.upload');
        // Route::resource('sliders', SliderController::class);
        Route::post('/sliders/store', [SliderController::class, 'store'])->name('sliders.store');
        Route::get('/sliders/{slider}/edit', [SliderController::class, 'edit'])->name('sliders.edit');
        Route::put('/sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
        Route::delete('/sliders/{slider}', [SliderController::class, 'destroy'])->name('sliders.delete');
    ### Slider ###
    
    ### Store ###
        Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
        // Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
        // Route::post('/stores/upload', [StoreController::class, 'upload'])->name('stores.upload');
        // Route::resource('stores', StoreController::class);
        Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
        Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
        // Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.delete');
    ### Store ###
    
    ### Payment info ###
    Route::get('/offline-payments', [OfflinePaymentController::class, 'index'])->name('offline-payments.index');
    Route::get('/offline-payments/{id}/edit', [OfflinePaymentController::class, 'edit'])->name('offline-payments.edit');
    Route::post('/offline-payments', [OfflinePaymentController::class, 'update'])->name('offline-payments.update');
    ### Payment info ###
    
    ### Subscription plans ###
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('/subscription-plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
    Route::post('/subscription-plans/store', [SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
    Route::get('/subscription-plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
    Route::delete('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('subscription-plans.delete');
    ### Subscription plans ###
    
});
