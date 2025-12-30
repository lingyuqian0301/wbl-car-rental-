<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RegisteredUserController;
use Illuminate\Support\Facades\Route;


// Homepage – show approved cars
Route::get('/', [VehicleController::class, 'index'])->name('home');

// ----------------------------
// Staff Login Routes (GUESTS ONLY)
// ----------------------------
// routes/web.php
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])
    ->name('vehicles.show');

Route::middleware('guest')->group(function () {
    // Show login page
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

    // Handle login submission
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');
});



// ----------------------------
// Protected Routes (AUTHENTICATED USERS)
// ----------------------------
Route::middleware('auth')->group(function () {
    // Regular user dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Booking Routes (Customer)
    
    Route::post('/booking/{vehicleID}', [BookingController::class, 'store'])
        ->name('booking.store');

    Route::get('/booking/confirm', [BookingController::class, 'confirm'])
        ->name('booking.confirm');

    Route::post('/booking/finalize', [BookingController::class, 'finalize'])
        ->name('booking.finalize');

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');

    });

    // Payment Routes (Customer)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/create/{booking}', [PaymentController::class, 'create'])->name('create');
        Route::post('/store', [PaymentController::class, 'store'])->name('store');
        Route::post('/submit', [PaymentController::class, 'submitPayment'])->name('submit');
        Route::post('/wallet/{booking}', [PaymentController::class, 'payWithWallet'])->name('wallet');
    });

    // Invoice Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/generate/{bookingId}', [InvoiceController::class, 'generatePDF'])->name('generate');
    });

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Admin dashboard
        Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');

        // Payment verification
        Route::prefix('admin/payments')->name('admin.payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
            Route::post('/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('approve');
            Route::post('/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('reject');
        });

        // Vehicle category pages
        Route::prefix('admin/vehicles')->name('admin.vehicles.')->group(function () {
            Route::get('/cars', [AdminVehicleController::class, 'cars'])->name('cars');
            Route::get('/motorcycles', [AdminVehicleController::class, 'motorcycles'])->name('motorcycles');
            Route::get('/others', [AdminVehicleController::class, 'others'])->name('others');
            Route::get('/{vehicle}', [AdminVehicleController::class, 'show'])->name('show');
        });
    });
});

require __DIR__ . '/auth.php';
