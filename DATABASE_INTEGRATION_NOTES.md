# Database Integration Notes

## Overview
The migrations and models have been updated to work with the existing `hastatravel.sql` database structure.

## Key Changes Made

### 1. Payment Table
- **Existing Structure**: Uses `paymentID` as primary key, `bookingID` as foreign key
- **Existing Columns**: `receiptURL`, `deposit_returned`, `payment_purpose` already exist
- **Added**: `keep_deposit` column only

### 2. Booking Table
- **Existing Structure**: Uses `bookingID` as primary key, `customerID` and `vehicleID` as foreign keys
- **Existing Columns**: `number_of_days` already exists
- **Added**: `keep_deposit` column only
- **Note**: Table uses `total_amount` instead of `total_price`, and `booking_status` instead of `status`

### 3. WalletAccount Table
- **Existing Structure**: Uses `walletAccountID` as primary key, `customerID` as foreign key
- **Existing Columns**: `virtual_balance`, `available_balance` already exist
- **Added**: `user_id` column for Laravel users integration (optional, nullable)

### 4. WalletTransaction Table
- **Existing Structure**: Uses `transactionID` as primary key, `walletAccountID` and `paymentID` as foreign keys
- **Existing Columns**: `amount`, `transaction_type`, `transaction_date` already exist
- **Added**: `description`, `reference_type`, `reference_id` columns

## Model Updates

### Payment Model
- Primary key: `paymentID`
- Foreign key: `bookingID` (references `booking.bookingID`)
- Removed: `payment_method`, `verified_by`, `rejected_reason` (not in existing table)
- Added: All existing columns from hastatravel.sql

### Booking Model
- Primary key: `bookingID`
- Foreign keys: `customerID`, `vehicleID`
- Added accessors: `getTotalPriceAttribute()`, `getStatusAttribute()` for compatibility
- Uses `total_amount` and `booking_status` internally

### WalletAccount Model
- Primary key: `walletAccountID`
- Supports both `customerID` and `user_id` for flexibility
- Methods: `credit()`, `debit()` work with existing structure

### WalletTransaction Model
- Primary key: `transactionID`
- Foreign keys: `walletAccountID`, `paymentID`
- Supports new fields: `description`, `reference_type`, `reference_id`

## Migration Strategy

All migrations now:
1. Check if tables exist before modifying
2. Only add missing columns
3. Never drop existing columns
4. Support both singular (`booking`, `payment`) and plural (`bookings`, `payments`) tables

## Running Migrations

```bash
php artisan migrate
```

The migrations will:
- Add `keep_deposit` to `payment` table
- Add `keep_deposit` to `booking` table
- Add `user_id` to `walletaccount` table (optional)
- Add `description`, `reference_type`, `reference_id` to `wallettransaction` table

## Important Notes

1. **User Authentication**: The system supports both `customerID` (from customer table) and `user_id` (from users table). You may need to create a relationship between Customer and User models.

2. **Booking Status**: The `booking` table uses `booking_status` (varchar) instead of `status` (enum). The model provides accessors for compatibility.

3. **Payment Status**: The `payment` table uses `status` (varchar) instead of enum. Values should match existing data.

4. **Foreign Keys**: All foreign key relationships have been updated to match the existing database structure.

## Testing Checklist

- [ ] Run migrations successfully
- [ ] Verify `keep_deposit` column exists in `payment` table
- [ ] Verify `keep_deposit` column exists in `booking` table
- [ ] Verify `user_id` column exists in `walletaccount` table (optional)
- [ ] Verify new columns exist in `wallettransaction` table
- [ ] Test payment creation with existing booking structure
- [ ] Test wallet operations with existing walletaccount structure
What the migrations will add
keep_deposit column to payment table
keep_deposit column to booking table
user_id column to walletaccount table (optional, for Laravel users)
description, reference_type, reference_id to wallettransaction table
2025_01_20_000001_add_payment_fields_to_payment_table — Added keep_deposit column
2025_01_20_000002_create_walletaccount_table — Added user_id column to existing table
2025_01_20_000003_create_wallettransaction_table — Added description, reference_type, reference_id columns
2025_01_20_000004_add_keep_deposit_to_bookings_table — Added keep_deposit column
2025_12_07_115851_add_role_to_users_table — Fixed to check if column exists (already existed)
2025_12_08_000000_add_item_category_id_to_vehicles_table — Completed
