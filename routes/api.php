<?php

use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\DashboardController;

// Service Endpoints
Route::apiResource('services', ServiceController::class);

// Reservation Endpoints
Route::apiResource('reservations', ReservationController::class);

// Dashboard Admin Overview
Route::get('dashboard/overview', [DashboardController::class, 'index']);