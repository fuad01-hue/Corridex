<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\ExceptionApiController;
use App\Http\Controllers\Api\LocationApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RESTful API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Master Carriers & Locations
    Route::get('/carriers', [CarrierApiController::class, 'index']);
    Route::get('/locations', [LocationApiController::class, 'index']);
    Route::get('/locations/geocode', [LocationApiController::class, 'geocode'])
        ->middleware('role:admin,operator');

    // Shipments & Route Telemetry
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::post('/shipments', [ShipmentApiController::class, 'store'])
        ->middleware('role:admin,operator');
    Route::get('/shipments/{id}', [ShipmentApiController::class, 'show']);
    Route::get('/shipments/{id}/route', [ShipmentApiController::class, 'route']);
    Route::patch('/shipments/{id}', [ShipmentApiController::class, 'update'])
        ->middleware('role:admin,operator');

    // System Analytics & Audit Trails (RBAC Scoped)
    Route::get('/analytics', [ShipmentApiController::class, 'analytics'])
        ->middleware('role:admin,operator');
    Route::get('/audit-logs', [ShipmentApiController::class, 'auditLogs'])
        ->middleware('role:admin');

    // Tracking Events
    Route::get('/shipments/{id}/tracking', [TrackingApiController::class, 'index']);
    Route::post('/shipments/{id}/tracking', [TrackingApiController::class, 'store'])
        ->middleware('role:admin,operator');

    // Exceptions
    Route::get('/shipments/{id}/exceptions', [ExceptionApiController::class, 'index']);
    Route::post('/shipments/{id}/exceptions', [ExceptionApiController::class, 'store'])
        ->middleware('role:admin,operator');
    Route::patch('/exceptions/{id}', [ExceptionApiController::class, 'update'])
        ->middleware('role:admin,operator');
});
