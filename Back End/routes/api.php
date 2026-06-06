<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\LogController;

Route::middleware(['cors'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('admin/login', [AuthController::class, 'adminLogin']);
        Route::get('profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::apiResource('factories', FactoryController::class);
        Route::apiResource('employees', EmployeeController::class);
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('logs', [LogController::class, 'index']);
    });
});
