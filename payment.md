# Payment and Deposit System Implementation Guide

## Overview
This document describes the implementation of the Payment and Deposit System for the Laravel Car Rental application, including wallet functionality and "Keep Deposit" feature.

## Database Schema

### Tables Used
- **`payment`** (singular) - Stores payment records
- **`bookings`** (plural) - Stores booking records  
- **`walletaccount`** (singular) - Stores user wallet accounts
- **`wallettransaction`** (singular) - Stores wallet transaction history

### New Columns Added

#### Payment Table
- `receiptURL` (string, nullable) - Path to uploaded receipt image
- `deposit_returned` (boolean, default: false) - Flag indicating if deposit was returned
- `payment_purpose` (string, nullable) - Purpose of payment (e.g., 'booking_deposit')
- `keep_deposit` (boolean, default: false) - Flag for "Keep Deposit" feature

#### Bookings Table
- `keep_deposit` (boolean, default: false) - User preference to keep deposit in wallet
- `number_of_days` (integer, nullable) - Number of days for deposit calculation

#### WalletAccount Table
- `user_id` (foreign key, unique) - Links to users table
- `virtual_balance` (decimal) - Total wallet balance
- `available_balance` (decimal) - Available balance for use

#### WalletTransaction Table
- `walletaccount_id` (foreign key) - Links to walletaccount
- `transaction_type` (enum: 'credit', 'debit')
- `amount` (decimal)
- `description` (string, nullable)
- `reference_type` (string, nullable) - Type of related record (e.g., 'booking')
- `reference_id` (unsignedBigInteger, nullable) - ID of related record

## Business Logic

### 1. Deposit Calculation
The deposit amount is calculated based on rental duration:

```php
// Short Term (< 15 Days): Fixed Deposit = RM 50.00
if ($numberOfDays < 15) {
    return 50.00;
}

// Long Term (≥ 15 Days): Deposit = 100% of Rental Price
return $booking->total_price;
```

**Implementation:** `PaymentService::calculateDeposit()`

### 2. Payment Submission (DuitNow QR)
The `submitPayment()` method handles payment submission:

- Accepts `bookingID` and `receipt_image` file
- Stores receipt in `public/uploads/receipts`
- Creates payment record with:
  - `payment_type`: 'Deposit' or 'Full Payment'
  - `amount`: Calculated deposit amount
  - `receiptURL`: Path to uploaded image
  - `status`: 'pending'
  - `payment_purpose`: 'booking_deposit'

**Route:** `POST /payments/submit`

**Controller Method:** `PaymentController::submitPayment()`

### 3. Keep Deposit Feature
When a booking is completed and `keep_deposit` is true:

1. System finds the verified deposit payment
2. Checks for penalties (currently placeholder - returns false)
3. If no penalties, credits the deposit amount to user's wallet
4. Creates a `wallettransaction` record (credit type)
5. Updates `walletaccount.virtual_balance` and `available_balance`
6. Does NOT mark `deposit_returned` as true (since it's in wallet)

**Implementation:** `PaymentService::processKeepDeposit()`

**Trigger:** `BookingObserver` watches for booking status changes to 'Completed'

### 4. Wallet Usage for New Bookings
When creating a new booking:

1. System calculates required deposit
2. Checks `walletaccount.available_balance`
3. If balance >= deposit amount, user can skip deposit payment
4. Option to pay deposit directly from wallet

**Implementation:** 
- `PaymentService::canSkipDepositWithWallet()`
- `PaymentService::payDepositFromWallet()`

**Route:** `POST /payments/wallet/{booking}`

## Models

### Payment Model
- Uses `payment` table (singular)
- Relationships: `booking()`, `verifier()`
- New fillable fields: `receiptURL`, `deposit_returned`, `payment_purpose`, `keep_deposit`

### Booking Model
- Uses `bookings` table (plural)
- New fillable fields: `keep_deposit`, `number_of_days`
- New method: `getNumberOfDays()` - Returns `number_of_days` or `duration_days`

### WalletAccount Model
- Uses `walletaccount` table (singular)
- Relationships: `user()`, `transactions()`
- Methods:
  - `credit()` - Add funds to wallet
  - `debit()` - Deduct funds from wallet

### WalletTransaction Model
- Uses `wallettransaction` table (singular)
- Relationships: `walletAccount()`

### User Model
- New relationship: `walletAccount()`
- New method: `getOrCreateWalletAccount()` - Gets or creates wallet account

## Services

### PaymentService
Located at: `app/Services/PaymentService.php`

**Methods:**
1. `calculateDeposit(Booking $booking): float` - Calculate deposit based on duration
2. `processKeepDeposit(Booking $booking): bool` - Process keep deposit transfer
3. `canSkipDepositWithWallet(int $userId, float $requiredDeposit): bool` - Check if user can skip deposit
4. `payDepositFromWallet(Booking $booking, float $depositAmount): ?Payment` - Pay deposit from wallet

## Controllers

### PaymentController
**New Methods:**
1. `submitPayment(Request $request)` - Submit payment with DuitNow QR receipt
2. `payWithWallet(Request $request, Booking $booking)` - Pay deposit using wallet balance

**Updated Methods:**
1. `create()` - Now shows wallet balance and skip deposit option
2. `store()` - Now handles `keep_deposit` flag and `receiptURL`

### BookingController
**Updated Methods:**
1. `confirm()` - Now calculates deposit and checks wallet balance
2. `finalize()` - Now sets `number_of_days` and `keep_deposit` flag

## Observers

### BookingObserver
Located at: `app/Observers/BookingObserver.php`

- Watches for booking status changes
- When status changes to 'Completed' and `keep_deposit` is true, processes the deposit transfer

**Registered in:** `AppServiceProvider::boot()`

## Routes

### Payment Routes
```php
POST /payments/submit          - Submit payment with receipt
POST /payments/wallet/{booking} - Pay deposit from wallet
GET  /payments/create/{booking} - Show payment form
POST /payments/store           - Store payment (existing)
```

## Migrations

### Migration Files Created
1. `2025_01_20_000001_add_payment_fields_to_payment_table.php`
2. `2025_01_20_000002_create_walletaccount_table.php`
3. `2025_01_20_000003_create_wallettransaction_table.php`
4. `2025_01_20_000004_add_keep_deposit_to_bookings_table.php`

## Usage Examples

### 1. Submit Payment with Receipt
```php
POST /payments/submit
{
    "bookingID": 1,
    "receipt_image": <file>,
    "keep_deposit": true
}
```

### 2. Pay Deposit from Wallet
```php
POST /payments/wallet/1
```

### 3. Check Wallet Balance
```php
$user = Auth::user();
$walletAccount = $user->walletAccount;
$balance = $walletAccount->available_balance ?? 0;
```

### 4. Process Keep Deposit (Automatic)
When booking status changes to 'Completed' and `keep_deposit` is true, the observer automatically processes the transfer.

## Testing Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Test deposit calculation (< 15 days = RM 50, ≥ 15 days = 100%)
- [ ] Test payment submission with receipt upload
- [ ] Test wallet account creation
- [ ] Test wallet credit/debit operations
- [ ] Test "Keep Deposit" feature when booking completes
- [ ] Test wallet payment for new bookings
- [ ] Test wallet balance check in booking confirmation

## Notes

1. The system uses singular table names (`payment`, `walletaccount`, `wallettransaction`) as specified
2. The `number_of_days` field in bookings can fall back to `duration_days` if not set
3. Wallet accounts are created automatically when needed
4. The "Keep Deposit" feature only processes if there are no penalties (currently placeholder)
5. Receipt images are stored in `storage/app/public/receipts`

## Future Enhancements

1. Implement penalty checking logic in `processKeepDeposit()`
2. Add email notifications for wallet transactions
3. Add wallet transaction history view
4. Add admin interface for wallet management
5. Add refund processing for `deposit_returned = true` cases

