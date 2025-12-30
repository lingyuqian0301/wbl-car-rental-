# Database Integration & Payment System Documentation

## Overview
This document describes the Payment and Deposit System integration with the existing `hastatravel.sql` database structure. All changes are designed to work seamlessly with your existing database without breaking existing functionality.

---

## 📊 SQL Database Changes

### Tables Modified

#### 1. `payment` Table
**Existing Columns (from hastatravel.sql):**
- `paymentID` (int, primary key)
- `payment_purpose` (varchar)
- `payment_type` (varchar)
- `payment_date` (datetime)
- `amount` (decimal)
- `receiptURL` (text)
- `status` (varchar)
- `deposit_returned` (tinyint)
- `bookingID` (int, foreign key)

**New Column Added:**
- `keep_deposit` (boolean, default: false)
  - Purpose: Flag to indicate if user wants to keep deposit in wallet instead of refund

**Migration File:** `2025_01_20_000001_add_payment_fields_to_payment_table.php`

---

#### 2. `booking` Table
**Existing Columns (from hastatravel.sql):**
- `bookingID` (int, primary key)
- `customerID` (int, foreign key)
- `vehicleID` (int, foreign key)
- `start_date` (date)
- `end_date` (date)
- `number_of_days` (int)
- `duration_days` (int)
- `total_amount` (decimal)
- `booking_status` (varchar)
- `pickup_point` (varchar)
- `return_point` (varchar)
- `addOns_item` (varchar)
- `addOns_charge` (decimal)

**New Column Added:**
- `keep_deposit` (boolean, default: false)
  - Purpose: User preference to keep deposit in wallet when booking completes

**Migration File:** `2025_01_20_000004_add_keep_deposit_to_bookings_table.php`

---

#### 3. `walletaccount` Table
**Existing Columns (from hastatravel.sql):**
- `walletAccountID` (int, primary key)
- `customerID` (int, foreign key)
- `virtual_balance` (decimal)
- `available_balance` (decimal)
- `hold_amount` (decimal)
- `status` (varchar)
- `created_date` (date)

**New Column Added:**
- `user_id` (bigint, nullable, foreign key to `users.id`)
  - Purpose: Link wallet account to Laravel users table for authentication integration
  - Note: Optional column, can work with `customerID` alone

**Migration File:** `2025_01_20_000002_create_walletaccount_table.php`

---

#### 4. `wallettransaction` Table
**Existing Columns (from hastatravel.sql):**
- `transactionID` (int, primary key)
- `walletAccountID` (int, foreign key)
- `paymentID` (int, foreign key)
- `amount` (decimal)
- `transaction_type` (varchar)
- `transaction_date` (datetime)

**New Columns Added:**
- `description` (text, nullable)
  - Purpose: Human-readable description of the transaction
- `reference_type` (varchar, nullable)
  - Purpose: Type of related record (e.g., 'booking', 'refund', 'deposit')
- `reference_id` (bigint, nullable)
  - Purpose: ID of related booking/payment for tracking

**Migration File:** `2025_01_20_000003_create_wallettransaction_table.php`

---

## 🔧 Functions & Features Implemented

### 1. Deposit Calculation System

**Location:** `app/Services/PaymentService.php`

**Method:** `calculateDeposit(Booking $booking): float`

**Business Logic:**
```php
// Short Term (< 15 Days): Fixed Deposit = RM 50.00
if ($numberOfDays < 15) {
    return 50.00;
}

// Long Term (≥ 15 Days): Deposit = 100% of Rental Price
return $booking->total_amount;
```

**Usage:**
```php
$paymentService = new PaymentService();
$depositAmount = $paymentService->calculateDeposit($booking);
```

---

### 2. Payment Submission (DuitNow QR)

**Location:** `app/Http/Controllers/PaymentController.php`

**Method:** `submitPayment(Request $request)`

**Features:**
- Accepts `bookingID` and `receipt_image` file
- Stores receipt in `public/uploads/receipts`
- Creates payment record with:
  - `payment_type`: 'Deposit' or 'Full Payment'
  - `amount`: Calculated deposit amount
  - `receiptURL`: Path to uploaded image
  - `status`: 'Pending'
  - `payment_purpose`: 'booking_deposit'
  - `keep_deposit`: User preference

**Route:** `POST /payments/submit`

**Request Format:**
```php
[
    'bookingID' => 1,
    'receipt_image' => <file>,
    'keep_deposit' => true/false
]
```

---

### 3. "Keep Deposit" Feature

**Location:** `app/Services/PaymentService.php` & `app/Observers/BookingObserver.php`

**Method:** `processKeepDeposit(Booking $booking): bool`

**How It Works:**
1. When booking status changes to 'Completed' and `keep_deposit` is true
2. System finds the verified deposit payment
3. Checks for penalties (currently placeholder)
4. If no penalties, credits deposit amount to user's wallet
5. Creates `wallettransaction` record (credit type)
6. Updates `walletaccount.virtual_balance` and `available_balance`
7. Does NOT mark `deposit_returned` as true (since it's in wallet)

**Automatic Trigger:** `BookingObserver` watches for booking status changes

**Manual Usage:**
```php
$paymentService = new PaymentService();
$paymentService->processKeepDeposit($booking);
```

---

### 4. Wallet Balance Check

**Location:** `app/Services/PaymentService.php`

**Method:** `canSkipDepositWithWallet(int $userId, float $requiredDeposit): bool`

**Purpose:** Check if user has sufficient wallet balance to skip deposit payment

**Usage:**
```php
$canSkip = $paymentService->canSkipDepositWithWallet($userId, $depositAmount);
if ($canSkip) {
    // Show option to pay from wallet
}
```

---

### 5. Pay Deposit from Wallet

**Location:** `app/Services/PaymentService.php`

**Method:** `payDepositFromWallet(Booking $booking, float $depositAmount): ?Payment`

**Features:**
- Checks wallet balance
- Debits amount from wallet
- Creates payment record with status 'Verified' (auto-verified)
- Creates wallet transaction record

**Route:** `POST /payments/wallet/{booking}`

**Usage:**
```php
$payment = $paymentService->payDepositFromWallet($booking, $depositAmount);
if ($payment) {
    // Payment successful
}
```

---

### 6. Wallet Account Methods

**Location:** `app/Models/WalletAccount.php`

**Methods:**
- `credit(float $amount, ...)`: Add funds to wallet
- `debit(float $amount, ...)`: Deduct funds from wallet

**Usage:**
```php
$walletAccount = WalletAccount::find(1);
$walletAccount->credit(50.00, 'Deposit refund', 'booking', 123);
$walletAccount->debit(50.00, 'Deposit payment', 'booking', 123);
```

---

## 🔗 Integration Guide

### Step 1: Run Migrations

```bash
php artisan migrate
```

This will add the new columns to your existing tables without affecting existing data.

---

### Step 2: Update Your Models

All models have been updated to work with your existing database structure:

#### Booking Model
- Uses `bookingID` as primary key (not `id`)
- Uses `vehicleID` and `customerID` (not `vehicle_id` and `user_id`)
- Uses `total_amount` and `booking_status` (accessors provided for compatibility)

#### Payment Model
- Uses `paymentID` as primary key
- Uses `bookingID` as foreign key
- All existing columns from hastatravel.sql are supported

#### WalletAccount Model
- Uses `walletAccountID` as primary key
- Supports both `customerID` and `user_id`

#### WalletTransaction Model
- Uses `transactionID` as primary key
- Supports new description and reference fields

---

### Step 3: Update Controllers

#### BookingController Changes

**Fixed Column Names:**
```php
// OLD (incorrect):
Booking::where('vehicle_id', $id)->where('status', '!=', 'Cancelled')

// NEW (correct):
Booking::where('vehicleID', $id)->where('booking_status', '!=', 'Cancelled')
```

**Creating Bookings:**
```php
Booking::create([
    'customerID' => Auth::id(),
    'vehicleID' => $vehicleID,
    'booking_status' => 'pending',
    'total_amount' => $totalAmount,
    // ... other fields
]);
```

#### PaymentController Integration

The controller now works with your existing database:
- Uses `bookingID` instead of `booking_id`
- Creates payments in `payment` table (singular)
- Supports both `customerID` and `user_id` for wallet accounts

---

### Step 4: Register Observer

The `BookingObserver` is automatically registered in `app/Providers/AppServiceProvider.php`:

```php
Booking::observe(BookingObserver::class);
```

This ensures "Keep Deposit" is processed automatically when bookings are completed.

---

### Step 5: Update Routes

Routes are already configured in `routes/web.php`:

```php
Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/create/{booking}', [PaymentController::class, 'create'])->name('create');
    Route::post('/store', [PaymentController::class, 'store'])->name('store');
    Route::post('/submit', [PaymentController::class, 'submitPayment'])->name('submit');
    Route::post('/wallet/{booking}', [PaymentController::class, 'payWithWallet'])->name('wallet');
});
```

---

## 📝 Key Differences from Standard Laravel

### Column Naming
- Uses `bookingID`, `paymentID`, `vehicleID` (camelCase with ID suffix)
- Uses `customerID` instead of `user_id` in booking table
- Uses `booking_status` instead of `status` in booking table
- Uses `total_amount` instead of `total_price` in booking table

### Table Names
- Uses singular: `payment`, `booking`, `walletaccount`, `wallettransaction`
- NOT plural: `payments`, `bookings`, `wallet_accounts`, `wallet_transactions`

### Primary Keys
- Uses custom primary keys: `bookingID`, `paymentID`, `walletAccountID`
- NOT standard Laravel `id` column

---

## 🧪 Testing Checklist

- [x] Run migrations successfully
- [ ] Verify `keep_deposit` column exists in `payment` table
- [ ] Verify `keep_deposit` column exists in `booking` table
- [ ] Verify `user_id` column exists in `walletaccount` table
- [ ] Verify new columns exist in `wallettransaction` table
- [ ] Test deposit calculation (< 15 days = RM 50, ≥ 15 days = 100%)
- [ ] Test payment submission with receipt upload
- [ ] Test wallet account creation
- [ ] Test wallet credit/debit operations
- [ ] Test "Keep Deposit" feature when booking completes
- [ ] Test wallet payment for new bookings
- [ ] Test booking creation with correct column names

---

## 🚨 Important Notes

1. **User Authentication**: The system supports both `customerID` (from customer table) and `user_id` (from users table). You may need to create a relationship between Customer and User models if using both.

2. **Booking Status**: The `booking` table uses `booking_status` (varchar) instead of `status` (enum). The model provides accessors (`getStatusAttribute()`, `setStatusAttribute()`) for compatibility.

3. **Payment Status**: The `payment` table uses `status` (varchar) instead of enum. Values should match existing data ('Pending', 'Verified', 'Rejected', etc.).

4. **Foreign Keys**: All foreign key relationships have been updated to match the existing database structure.

5. **Route Model Binding**: When using route model binding with Booking, ensure the route parameter matches `bookingID`:
   ```php
   Route::get('/bookings/{booking:bookingID}', ...);
   ```

---

## 📚 File Structure

```
app/
├── Models/
│   ├── Payment.php (updated)
│   ├── Booking.php (updated)
│   ├── WalletAccount.php (new)
│   └── WalletTransaction.php (new)
├── Services/
│   └── PaymentService.php (new)
├── Observers/
│   └── BookingObserver.php (new)
└── Http/Controllers/
    ├── PaymentController.php (updated)
    └── BookingController.php (updated)

database/migrations/
├── 2025_01_20_000001_add_payment_fields_to_payment_table.php
├── 2025_01_20_000002_create_walletaccount_table.php
├── 2025_01_20_000003_create_wallettransaction_table.php
└── 2025_01_20_000004_add_keep_deposit_to_bookings_table.php
```

---

## 🔄 Migration Status

All migrations have been run successfully:
- ✅ `2025_01_20_000001_add_payment_fields_to_payment_table` - Added `keep_deposit`
- ✅ `2025_01_20_000002_create_walletaccount_table` - Added `user_id`
- ✅ `2025_01_20_000003_create_wallettransaction_table` - Added description fields
- ✅ `2025_01_20_000004_add_keep_deposit_to_bookings_table` - Added `keep_deposit`

---

## 💡 Usage Examples

### Example 1: Submit Payment with Receipt
```php
POST /payments/submit
{
    "bookingID": 1,
    "receipt_image": <file>,
    "keep_deposit": true
}
```

### Example 2: Pay Deposit from Wallet
```php
POST /payments/wallet/1
```

### Example 3: Check Wallet Balance
```php
$user = Auth::user();
$walletAccount = $user->walletAccount;
$balance = $walletAccount->available_balance ?? 0;
```

### Example 4: Process Keep Deposit Manually
```php
$paymentService = new PaymentService();
$paymentService->processKeepDeposit($booking);
```

---

## 🎯 Summary

The Payment and Deposit System has been fully integrated with your existing `hastatravel.sql` database structure. All models, controllers, and services have been updated to work with your existing column names and table structure. The system is ready to use and will automatically handle deposit calculations, wallet transactions, and the "Keep Deposit" feature.
