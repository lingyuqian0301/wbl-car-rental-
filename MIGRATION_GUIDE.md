# Complete Migration Guide

This guide lists ALL migrations needed for the car rental system, including all features from previous conversations.

## 🚀 Quick Start

Run all migrations:
```bash
php artisan migrate
```

If you need to reset and re-run:
```bash
php artisan migrate:fresh
```

## 📋 Complete Migration List

### 1. Core Laravel Migrations (Default)
- `0001_01_01_000000_create_users_table.php` - Users table
- `0001_01_01_000001_create_cache_table.php` - Cache table
- `0001_01_01_000002_create_jobs_table.php` - Jobs table

### 2. Role System
- `2025_12_07_115851_add_role_to_users_table.php` - Adds role field (customer, admin, staff)
- `2025_01_16_000000_update_role_to_include_staff.php` - Updates role to include 'staff'

### 3. Vehicle System
- `2025_12_07_112910_create_vehicles_table.php` - Vehicles table
- `2025_01_15_000000_create_item_categories_table.php` - Item categories (Car, Motorcycle, Other, Voucher, etc.)
- `2025_01_15_000001_add_item_category_id_to_vehicles_table.php` - Links vehicles to categories

### 4. Booking System
- `2025_12_07_112911_create_bookings_table.php` - Bookings table
- `2025_01_15_000007_add_location_fields_to_bookings_table.php` - Adds pickup/return location, time, confirmed_by, completed_by

### 5. Payment System
- `2025_12_07_112920_create_payments_table.php` - Payments table with proof_of_payment, payment_method

### 6. Notification System
- `2025_01_15_000004_create_notifications_table.php` - Admin notifications table

### 7. Booking Status Tracking
- `2025_01_15_000002_create_booking_read_status_table.php` - Tracks read/unread bookings
- `2025_01_15_000003_create_booking_served_by_table.php` - Tracks who served bookings

### 8. Customer Management
- `2025_01_15_000005_add_customer_fields_to_users_table.php` - Adds phone, address, faculty, college, blacklist fields
- `2025_01_15_000006_create_customer_documents_table.php` - Customer documents (IC, License, Matric Card, Staff Card)

## 🔧 Migration Order

Migrations should run in this order (Laravel handles this automatically by timestamp):

1. Core Laravel tables
2. Users table
3. Role system
4. Vehicles and categories
5. Bookings
6. Payments
7. Notifications
8. Booking status tracking
9. Customer management

## ✅ Verification

After running migrations, verify all tables exist:

```bash
php artisan tinker
```

Then check:
```php
Schema::hasTable('users');
Schema::hasTable('vehicles');
Schema::hasTable('bookings');
Schema::hasTable('payments');
Schema::hasTable('notifications');
Schema::hasTable('item_categories');
Schema::hasTable('booking_read_status');
Schema::hasTable('booking_served_by');
Schema::hasTable('customer_documents');
```

## 🔄 If Migrations Fail

If you get errors:

1. **Check database connection** in `.env`
2. **Drop all tables** (if safe to do so):
   ```bash
   php artisan migrate:fresh
   ```
3. **Run migrations again**:
   ```bash
   php artisan migrate
   ```

## 📝 Important Notes

- **Role Values**: The role field accepts: `customer`, `admin`, `staff`
- **Default Role**: All new users default to `customer`
- **Payment Methods**: Bank Transfer, Cash
- **Payment Types**: Deposit, Full Payment, Balance
- **Booking Status**: Pending, Confirmed, Cancelled, Completed

## 🎯 After Migration

1. Create an admin user:
   ```php
   php artisan tinker
   \App\Models\User::create([
       'name' => 'Admin',
       'email' => 'admin@hasta.com',
       'password' => \Hash::make('password'),
       'role' => 'admin',
   ]);
   ```

2. Create categories:
   ```php
   \App\Models\ItemCategory::create(['name' => 'Car', 'slug' => 'car', 'is_active' => true]);
   \App\Models\ItemCategory::create(['name' => 'Motorcycle', 'slug' => 'motorcycle', 'is_active' => true]);
   \App\Models\ItemCategory::create(['name' => 'Voucher', 'slug' => 'voucher', 'is_active' => true]);
   ```








