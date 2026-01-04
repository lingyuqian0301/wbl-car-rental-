<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;

// Vehicle Management Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('vehicles', VehicleController::class);
    
    // Photo Management
    Route::post('vehicles/{vehicle}/photos', [VehicleController::class, 'uploadPhoto'])->name('vehicles.photos.store');
    Route::delete('vehicles/photos/{photo}', [VehicleController::class, 'deletePhoto'])->name('vehicles.photos.destroy');
    Route::post('vehicles/photos/{photo}/primary', [VehicleController::class, 'setPrimaryPhoto'])->name('vehicles.photos.primary');
    
    // Document Management
    Route::post('vehicles/{vehicle}/documents', [VehicleController::class, 'uploadDocument'])->name('vehicles.documents.store');
    Route::delete('vehicles/documents/{document}', [VehicleController::class, 'deleteDocument'])->name('vehicles.documents.destroy');
    
    // Location Management
    Route::post('vehicles/{vehicle}/location', [VehicleController::class, 'updateLocation'])->name('vehicles.location.update');
    
    // API Endpoints
    Route::prefix('api')->group(function () {
        Route::get('vehicles/{vehicle}/maintenance', [VehicleController::class, 'maintenanceHistory']);
        Route::get('vehicles/{vehicle}/calendar', [VehicleController::class, 'bookingCalendar']);
        Route::get('vehicles/{vehicle}/service-calendar', [VehicleController::class, 'serviceCalendar']);
    });
});
