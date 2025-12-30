# 🛠️ Laravel Tinker Guide - Quick Reference

## 🚀 How to Use Tinker

### **Start Tinker:**
```bash
php artisan tinker
```

### **Exit Tinker:**
```bash
exit
```
or press `Ctrl + C`

---

## 📝 **Common Commands for Payment & Billing Modules**

### **1. Check Data Counts**
```php
// Count users
\App\Models\User::count();

// Count bookings
\App\Models\Booking::count();

// Count payments
\App\Models\Payment::count();

// Count vehicles
\App\Models\Vehicle::count();
```

### **2. View All Users**
```php
\App\Models\User::all(['id', 'name', 'email']);
```

### **3. View All Bookings**
```php
\App\Models\Booking::with('vehicle', 'user')->get();
```

### **4. View All Payments**
```php
\App\Models\Payment::with('booking', 'verifier')->get();
```

### **5. View Bookings for Specific User**
```php
// Replace 1 with user ID
\App\Models\Booking::where('user_id', 1)->with('vehicle')->get();
```

### **6. View Pending Payments**
```php
\App\Models\Payment::where('status', 'Pending')->with('booking.vehicle')->get();
```

---

## ➕ **Create Test Data Manually**

### **Create a Vehicle:**
```php
$vehicle = \App\Models\Vehicle::create([
    'brand' => 'Toyota',
    'model' => 'Vios',
    'registration_number' => 'ABC1234',
    'daily_rate' => 150.00,
    'status' => 'Available',
]);
```

### **Create a Booking:**
```php
// Replace 1 with your user ID, and vehicle ID
$booking = \App\Models\Booking::create([
    'user_id' => 1,
    'vehicle_id' => 1,
    'start_date' => now()->addDays(7),
    'end_date' => now()->addDays(14),
    'duration_days' => 7,
    'total_price' => 1050.00,
    'status' => 'Pending',
]);
```

### **Create a Payment:**
```php
// Replace 1 with booking ID
$payment = \App\Models\Payment::create([
    'booking_id' => 1,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'proof_of_payment' => 'receipts/sample.jpg',
    'status' => 'Pending',
    'payment_date' => now(),
]);
```

### **Verify a Payment (as Admin):**
```php
// Replace 1 with payment ID, and 1 with admin user ID
$payment = \App\Models\Payment::find(1);
$payment->update([
    'status' => 'Verified',
    'verified_by' => 1, // Admin user ID
]);

// Also update booking status
$payment->booking->update(['status' => 'Confirmed']);
```

---

## 🔍 **Find Specific Records**

### **Find by ID:**
```php
$booking = \App\Models\Booking::find(1);
$payment = \App\Models\Payment::find(1);
$user = \App\Models\User::find(1);
```

### **Find by Email:**
```php
$user = \App\Models\User::where('email', 'customer@hasta.com')->first();
```

### **Find Booking with Payments:**
```php
$booking = \App\Models\Booking::with('payments')->find(1);
$booking->payments; // Access payments
```

---

## 🗑️ **Delete Data**

### **Delete a Record:**
```php
$booking = \App\Models\Booking::find(1);
$booking->delete();
```

### **Delete All Payments:**
```php
\App\Models\Payment::truncate();
```

### **Delete All Bookings:**
```php
\App\Models\Booking::truncate();
```

---

## 🔄 **Update Records**

### **Update Booking Status:**
```php
$booking = \App\Models\Booking::find(1);
$booking->update(['status' => 'Confirmed']);
```

### **Update Payment Status:**
```php
$payment = \App\Models\Payment::find(1);
$payment->update(['status' => 'Verified', 'verified_by' => 1]);
```

---

## 📊 **Useful Queries**

### **Get Bookings with Pending Payments:**
```php
\App\Models\Booking::whereHas('payments', function($q) {
    $q->where('status', 'Pending');
})->get();
```

### **Get Verified Payments:**
```php
\App\Models\Payment::where('status', 'Verified')->with('booking.vehicle')->get();
```

### **Get Bookings Ready for Invoice (Verified Payment):**
```php
\App\Models\Booking::whereHas('payments', function($q) {
    $q->where('status', 'Verified');
})->get();
```

---

## 💡 **Quick Tips**

1. **Use `->first()`** to get a single record
2. **Use `->get()`** to get multiple records
3. **Use `->count()`** to get the number of records
4. **Use `->with()`** to eager load relationships
5. **Use `->find(id)`** to find by primary key

---

## 🎯 **Example Session**

```php
// Start tinker
php artisan tinker

// Check what we have
\App\Models\Booking::count();

// View a booking
$booking = \App\Models\Booking::with('vehicle', 'payments')->first();
$booking->vehicle->full_model;
$booking->payments;

// Create a payment
$payment = \App\Models\Payment::create([
    'booking_id' => $booking->id,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'status' => 'Pending',
    'payment_date' => now(),
]);

// Exit
exit
```

---

## ✅ **Quick Test Data Script**

Instead of using tinker manually, you can run:
```bash
php create_test_data.php
```

This creates all the test data automatically!

---

## 🆘 **Common Issues**

**"Class not found"**
- Make sure you're in the project directory
- Run `composer dump-autoload`

**"Table doesn't exist"**
- Run migrations: `php artisan migrate`

**"Connection refused"**
- Make sure MySQL is running in XAMPP

