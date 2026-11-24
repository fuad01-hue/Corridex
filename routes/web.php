<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ArchitectureController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/architecture', [ArchitectureController::class, 'index'])->name('architecture');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipment Directory & Management
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments', [ShipmentController::class, 'store'])
        ->middleware('role:admin,operator')
        ->name('shipments.store');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');

    // Operator Control Panel Actions
    Route::post('/shipments/{id}/tracking', [ShipmentController::class, 'appendTracking'])
        ->middleware('role:admin,operator')
        ->name('shipments.tracking.store');
    Route::post('/shipments/{id}/exceptions', [ShipmentController::class, 'storeException'])
        ->middleware('role:admin,operator')
        ->name('shipments.exceptions.store');
    Route::patch('/exceptions/{id}', [ShipmentController::class, 'resolveException'])
        ->middleware('role:admin,operator')
        ->name('exceptions.resolve');

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Enterprise Audit Logs (Admin Only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('role:admin')
        ->name('audit-logs.index');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
