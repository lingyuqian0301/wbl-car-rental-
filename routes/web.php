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
use Illuminate\Support\Facades\Hash;
use App\Mail\BookingInvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\CustomerDashboardController;
Route::get('/fix-admin-password', function () {
    $user = \App\Models\User::where('email', 'yuqian@hasta.com')->first();
    if ($user) {
        $user->password = Hash::make('password123');
        $user->save();
        return "Fixed! You can now log in with password: password123";
    }
    return "User not found!";
});
Route::get('/mail-preview', function () {
    // 1. Get the latest booking
    $booking = \App\Models\Booking::with(['customer', 'vehicle'])->latest()->first();
    
    if (!$booking) return "No bookings found in database to preview.";

    // 2. Mock Invoice Data (So the PDF view doesn't crash)
    $invoiceData = new \App\Models\Invoice([
        'invoice_number' => 'PREVIEW-123',
        'issue_date'     => now(),
        'totalAmount'    => $booking->total_price,
        'bookingID'      => $booking->bookingID
    ]);

    // 3. Generate the PDF (Crucial Step: Do not pass null!)
    $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

    // 4. Return the Mail View
    return new \App\Mail\BookingInvoiceMail($booking, $pdf);
});




Route::get('/send-test', function () {
    $booking = \App\Models\Booking::with(['customer', 'vehicle'])->latest()->first();
    
    if (!$booking) return "No booking found.";

    // Generate PDF
    $invoiceData = \App\Models\Invoice::firstOrNew(['bookingID' => $booking->bookingID]);
    $invoiceData->invoice_number = 'TEST-SEND';
    $invoiceData->issue_date = now();
    $invoiceData->totalAmount = $booking->total_price;
    
    $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

    // Send to YOUR email (Replace with your actual email)
    \Illuminate\Support\Facades\Mail::to('yqling29@gmail.com')
        ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));

    return "Test email sent to yqling29@gmail.com! Check your inbox.";
});
Route::get('/', [VehicleController::class, 'index'])->name('home');
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');
});

// ----------------------------
// Protected Routes (AUTHENTICATED USERS)
// ----------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Group under 'auth' so only logged-in users can access

    Route::get('/my-wallet', [CustomerDashboardController::class, 'wallet'])->name('wallet.show');
    Route::get('/my-loyalty', [CustomerDashboardController::class, 'loyalty'])->name('loyalty.show');


    // Booking Routes (Customer)
    Route::get('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::post('/booking/finalize', [BookingController::class, 'finalize'])->name('booking.finalize');
    Route::post('/booking/{vehicleID}', [BookingController::class, 'store'])->name('booking.store');

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    });

    // Customer Invoice Download Route
    Route::get('/booking/{id}/invoice', [BookingController::class, 'downloadInvoice'])->name('booking.invoice');

    // Payment Routes (Customer)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/create/{booking}', [PaymentController::class, 'create'])->name('create');
        Route::post('/store', [PaymentController::class, 'store'])->name('store');
        Route::post('/submit', [PaymentController::class, 'submitPayment'])->name('submit');
        Route::post('/wallet/{booking}', [PaymentController::class, 'payWithWallet'])->name('wallet');
    });

    // Fix Storage Link Route
    Route::get('/fix-storage', function () {
        $target = storage_path('app/public');
        $link = public_path('storage');
        if (file_exists($link)) {
            return "The link already exists!";
        }
        symlink($target, $link);
        return "Success! The storage link has been created.";
    });

    // Payment Routes (Alternative)
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/{bookingID}', [PaymentController::class, 'showPaymentForm'])->name('create');
        Route::post('/submit', [PaymentController::class, 'processPayment'])->name('submit');
    });

    // Invoice Routes (General)
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/generate/{bookingId}', [InvoiceController::class, 'generatePDF'])->name('generate');
    });

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');

        Route::prefix('admin/payments')->name('admin.payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/{id}/invoice', [AdminPaymentController::class, 'generateInvoice'])->name('invoice');
            Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
            Route::post('/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('approve');
            Route::post('/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('reject');
        });

        Route::prefix('admin/vehicles')->name('admin.vehicles.')->group(function () {
            Route::get('/cars', [AdminVehicleController::class, 'cars'])->name('cars');
            Route::get('/motorcycles', [AdminVehicleController::class, 'motorcycles'])->name('motorcycles');
            Route::get('/others', [AdminVehicleController::class, 'others'])->name('others');
            Route::get('/{vehicle}', [AdminVehicleController::class, 'show'])->name('show');
        });
    });
});

require __DIR__ . '/auth.php';
