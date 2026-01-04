<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminDashboardController;

// API endpoint for getting programs by faculty (for dynamic dropdown)
Route::get('/api/programs/{facultyCode}', function ($facultyCode) {
    $faculties = config('utm.faculties');
    if (!isset($faculties[$facultyCode])) {
        return response()->json([]);
    }
    return response()->json($faculties[$facultyCode]['programs']);
});

// API endpoint for getting all faculties
Route::get('/api/faculties', function () {
    $result = [];
    foreach (config('utm.faculties') as $code => $data) {
        $result[] = ['code' => $code, 'name' => $data['name']];
    }
    return response()->json($result);
});

// API endpoint for getting all colleges
Route::get('/api/colleges', function () {
    $result = [];
    foreach (config('utm.colleges') as $code => $name) {
        $result[] = ['code' => $code, 'name' => $name];
    }
    return response()->json($result);
});

// API endpoint for getting all states
Route::get('/api/states', function () {
    return response()->json(config('utm.states'));
});
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminTopbarCalendarController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminCalendarController;
use App\Http\Controllers\AdminCancellationController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminInvoiceController;
use App\Http\Controllers\AdminRentalReportController;
use App\Http\Controllers\AdminChartsController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\AdminLeasingController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminVoucherController;
use App\Http\Controllers\StaffDashboardController;
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

// Booking route - accessible to all, but requires auth in controller
Route::post('/booking/{vehicleID}', [BookingController::class, 'store'])
    ->name('booking.store')
    ->where('vehicleID', '[0-9]+');  // <--- ADD THIS LINE

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

    // Booking Routes (Customer) - Require authentication
    Route::get('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::post('/booking/finalize', [BookingController::class, 'finalize'])->name('booking.finalize');
    
    // Debug route - catch GET requests to finalize (should only be accessed via POST)
    Route::get('/booking/finalize', function () {
        // Log that GET was received (shouldn't happen if form is POST)
        file_put_contents(storage_path('logs/route_debug.txt'), date('Y-m-d H:i:s') . " - GET request to /booking/finalize\n", FILE_APPEND);
        return redirect()->route('booking.confirm')->with('error', 'Form submitted as GET instead of POST. Please try again.');
    });

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

    // Staff-only routes
    Route::middleware('staff')->group(function () {
        Route::get('/staff/dashboard', StaffDashboardController::class)->name('staff.dashboard');
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
            Route::put('/{payment}/update-verify', [AdminPaymentController::class, 'updateVerify'])->name('update-verify');
        });

        Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/mark-as-read', [AdminNotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/mark-all-as-read', [AdminNotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
            Route::get('/unread-count', [AdminNotificationController::class, 'getUnreadCount'])->name('unread-count');
            Route::get('/dropdown-list', [AdminNotificationController::class, 'getDropdownList'])->name('dropdown-list');
        });

        Route::prefix('admin/vehicles')->name('admin.vehicles.')->group(function () {
            Route::get('/cars', [AdminVehicleController::class, 'cars'])->name('cars');
            Route::get('/motorcycles', [AdminVehicleController::class, 'motorcycles'])->name('motorcycles');
            Route::get('/others', [AdminVehicleController::class, 'others'])->name('others');
            Route::get('/cars/create', [AdminVehicleController::class, 'createCar'])->name('cars.create');
            Route::post('/cars', [AdminVehicleController::class, 'storeCar'])->name('cars.store');
            Route::get('/motorcycles/create', [AdminVehicleController::class, 'createMotorcycle'])->name('motorcycles.create');
            Route::post('/motorcycles', [AdminVehicleController::class, 'storeMotorcycle'])->name('motorcycles.store');
            Route::get('/cars/{vehicle}/edit', [AdminVehicleController::class, 'editCar'])->name('cars.edit');
            Route::put('/cars/{vehicle}', [AdminVehicleController::class, 'updateCar'])->name('cars.update');
            Route::get('/motorcycles/{vehicle}/edit', [AdminVehicleController::class, 'editMotorcycle'])->name('motorcycles.edit');
            Route::put('/motorcycles/{vehicle}', [AdminVehicleController::class, 'updateMotorcycle'])->name('motorcycles.update');
            Route::post('/{vehicle}/status', [AdminVehicleController::class, 'updateStatus'])->name('status.update');
            Route::delete('/cars/{vehicle}', [AdminVehicleController::class, 'destroyCar'])->name('cars.destroy');
            Route::delete('/motorcycles/{vehicle}', [AdminVehicleController::class, 'destroyMotorcycle'])->name('motorcycles.destroy');
            Route::get('/cars/export-pdf', [AdminVehicleController::class, 'exportCarsPdf'])->name('cars.export-pdf');
            Route::get('/cars/export-excel', [AdminVehicleController::class, 'exportCarsExcel'])->name('cars.export-excel');
            Route::get('/motorcycles/export-pdf', [AdminVehicleController::class, 'exportMotorcyclesPdf'])->name('motorcycles.export-pdf');
            Route::get('/motorcycles/export-excel', [AdminVehicleController::class, 'exportMotorcyclesExcel'])->name('motorcycles.export-excel');
            Route::get('/export-all-pdf', [AdminVehicleController::class, 'exportAllPdf'])->name('export-all-pdf');
            Route::get('/export-all-excel', [AdminVehicleController::class, 'exportAllExcel'])->name('export-all-excel');
            Route::delete('/{vehicle}', [AdminVehicleController::class, 'destroy'])->name('destroy');
            Route::get('/{vehicle}', [AdminVehicleController::class, 'show'])->name('show');
            Route::post('/{vehicle}/maintenance', [AdminVehicleController::class, 'storeMaintenance'])->name('maintenance.store');
            Route::delete('/maintenance/{maintenance}', [AdminVehicleController::class, 'destroyMaintenance'])->name('maintenance.destroy');
            Route::post('/{vehicle}/documents', [AdminVehicleController::class, 'storeDocument'])->name('documents.store');
            Route::delete('/documents/{document}', [AdminVehicleController::class, 'destroyDocument'])->name('documents.destroy');
            Route::post('/{vehicle}/photos', [AdminVehicleController::class, 'storePhoto'])->name('photos.store');
        });

        Route::prefix('admin/topbar-calendar')->name('admin.topbar-calendar.')->group(function () {
            Route::get('/', [AdminTopbarCalendarController::class, 'index'])->name('index');
            Route::post('/bookings/{booking}/mark-as-read', [AdminTopbarCalendarController::class, 'markAsRead'])->name('bookings.mark-as-read');
            Route::post('/bookings/{booking}/mark-as-served', [AdminTopbarCalendarController::class, 'markAsServed'])->name('bookings.mark-as-served');
            Route::post('/bookings/{booking}/confirm', [AdminTopbarCalendarController::class, 'confirmBooking'])->name('bookings.confirm');
            Route::post('/bookings/{booking}/complete', [AdminTopbarCalendarController::class, 'completeBooking'])->name('bookings.complete');
            Route::post('/bookings/{booking}/send-balance-reminder', [AdminTopbarCalendarController::class, 'sendBalanceReminder'])->name('bookings.send-balance-reminder');
        });

        Route::prefix('admin/bookings')->name('admin.bookings.')->group(function () {
            Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations');
            Route::post('/reservations/{booking}/update-status', [AdminReservationController::class, 'updateBookingStatus'])->name('reservations.update-status');
            Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar');
            Route::get('/cancellation', [AdminCancellationController::class, 'index'])->name('cancellation');
            Route::post('/cancellation/{booking}/update', [AdminCancellationController::class, 'updateCancellation'])->name('cancellation.update');
            Route::post('/cancellation/{booking}/send-email', [AdminCancellationController::class, 'sendEmail'])->name('cancellation.send-email');
        });

        Route::prefix('admin/manage')->name('admin.manage.')->group(function () {
            Route::get('/client', [AdminCustomerController::class, 'index'])->name('client');
        });

        Route::prefix('admin/customers')->name('admin.customers.')->group(function () {
            Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
            Route::post('/', [AdminCustomerController::class, 'store'])->name('store');
            Route::post('/delete-selected', [AdminCustomerController::class, 'deleteSelected'])->name('delete-selected');
            Route::get('/export-pdf', [AdminCustomerController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [AdminCustomerController::class, 'exportExcel'])->name('export-excel');
        });

        Route::prefix('admin/leasing')->name('admin.leasing.')->group(function () {
            Route::get('/owner', [AdminLeasingController::class, 'ownerIndex'])->name('owner');
            Route::get('/owner/create', [AdminLeasingController::class, 'ownerCreate'])->name('owner.create');
            Route::post('/owner', [AdminLeasingController::class, 'ownerStore'])->name('owner.store');
            Route::get('/owner/{owner}', [AdminLeasingController::class, 'ownerShow'])->name('owner.show');
            Route::get('/owner/{owner}/edit', [AdminLeasingController::class, 'ownerEdit'])->name('owner.edit');
            Route::put('/owner/{owner}', [AdminLeasingController::class, 'ownerUpdate'])->name('owner.update');
            Route::delete('/owner/{owner}', [AdminLeasingController::class, 'ownerDestroy'])->name('owner.destroy');
            Route::get('/vehicle', [AdminLeasingController::class, 'vehicleIndex'])->name('vehicle');
        });

        Route::prefix('admin/invoices')->name('admin.invoices.')->group(function () {
            Route::get('/', [AdminInvoiceController::class, 'index'])->name('index');
        });

        Route::prefix('admin/vouchers')->name('admin.vouchers.')->group(function () {
            Route::get('/', [AdminVoucherController::class, 'index'])->name('index');
            Route::post('/', [AdminVoucherController::class, 'store'])->name('store');
            Route::get('/{voucher}/edit-data', [AdminVoucherController::class, 'editData'])->name('edit-data');
            Route::put('/{voucher}', [AdminVoucherController::class, 'update'])->name('update');
            Route::delete('/{voucher}', [AdminVoucherController::class, 'destroy'])->name('destroy');
            Route::get('/{voucher}/used-customers', [AdminVoucherController::class, 'showUsedCustomers'])->name('used-customers');
        });

        Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
            Route::get('/rentals', [AdminRentalReportController::class, 'index'])->name('rentals');
            Route::get('/rentals/export-pdf', [AdminRentalReportController::class, 'exportPDF'])->name('rentals.export-pdf');
            Route::get('/charts', [AdminChartsController::class, 'index'])->name('charts');
            Route::get('/charts/export-pdf', [AdminChartsController::class, 'exportPdf'])->name('charts.export-pdf');
            Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance');
        });

        Route::prefix('admin/vouchers')->name('admin.vouchers.')->group(function () {
            Route::get('/', [AdminVoucherController::class, 'index'])->name('index');
            Route::post('/', [AdminVoucherController::class, 'store'])->name('store');
            Route::put('/{voucher}', [AdminVoucherController::class, 'update'])->name('update');
            Route::delete('/{voucher}', [AdminVoucherController::class, 'destroy'])->name('destroy');
            Route::get('/{voucher}/edit-data', [AdminVoucherController::class, 'editData'])->name('edit-data');
            Route::get('/{voucher}/used-customers', [AdminVoucherController::class, 'showUsedCustomers'])->name('used-customers');
        });
    });
});

require __DIR__ . '/auth.php';
