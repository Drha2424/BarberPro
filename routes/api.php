<?php

use App\Http\Controllers\Api\Admin\AdminReservationController;
use App\Http\Controllers\Api\Admin\AdminServiceController;
use App\Http\Controllers\Api\Admin\AdminSlotController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Publik
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show'])->whereUuid('id');
Route::get('/slots', [ReservationController::class, 'availability']);

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Wajib login (pelanggan dan admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/my-reservations', [ReservationController::class, 'mine']);
    Route::get('/my-reservations/{id}', [ReservationController::class, 'showMine'])->whereUuid('id');

    // Khusus admin
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/services', [AdminServiceController::class, 'index']);
        Route::post('/services', [AdminServiceController::class, 'store']);
        Route::put('/services/{id}', [AdminServiceController::class, 'update'])->whereUuid('id');
        Route::delete('/services/{id}', [AdminServiceController::class, 'destroy'])->whereUuid('id');

        Route::get('/slots', [AdminSlotController::class, 'index']);
        Route::post('/slots', [AdminSlotController::class, 'store']);
        Route::put('/slots/{id}', [AdminSlotController::class, 'update'])->whereUuid('id');
        Route::delete('/slots/{id}', [AdminSlotController::class, 'destroy'])->whereUuid('id');

        Route::get('/reservations', [AdminReservationController::class, 'index']);
        Route::get('/reservations/{id}', [AdminReservationController::class, 'show'])->whereUuid('id');
        Route::put('/reservations/{id}', [AdminReservationController::class, 'update'])->whereUuid('id');
    });
});