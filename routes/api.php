<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductStatsController;

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [UserController::class, 'user']);
        Route::get('/stats', [ProductStatsController::class, 'index']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/user/products', [ProductController::class, 'userProducts']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });
});