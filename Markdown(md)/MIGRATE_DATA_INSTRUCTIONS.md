# Data Migration Instructions

## Overview
This migration script will:
1. Update the `users` table structure to match the required format
2. Create `customer`, `staff`, and `admin` tables
3. Copy user data to appropriate role tables based on their role
4. Update `vehicles`, `cars`, and `motorcycles` table structures
5. Migrate vehicle data to cars and motorcycles based on vehicle type

## Database Structure

### 1. Users Table
- `user_id` (id)
- `username`
- `email`
- `email_verified_at`
- `password`
- `remember_token`
- `role`
- `created_at`
- `updated_at`

### 2. Customer Table
- `customerID`
- `matric_number`
- `fullname`
- `ic_number`
- `phone`
- `email`
- `college`
- `faculty`
- `customer_type`
- `registration_date`
- `emergency_contact`
- `country`
- `customer_license`

### 3. Staff Table
- `staffID`
- `user_id` (foreign key to users)
- `ic_no`
- `fullname`
- `email`
- `phone`
- `position`
- `department`
- `hire_date`
- `is_active`

### 4. Admin Table
- `AdminID`
- `user_id` (foreign key to users)
- `ic_no`
- `fullname`
- `email`
- `phone`
- `position`
- `department`
- `hire_date`
- `is_active`

### 5. Vehicles Table
- `vehicleID`
- `plate_no`
- `available_status`
- `createdDate`
- `vehicle_brand`
- `vehicle_model`
- `manufacturing_year`
- `color`
- `engine_Capacity`
- `vehicleType`
- `rental_price`
- `isActive`

### 6. Cars Table
- All fields from vehicles table
- `seat_capacity`
- `transmission`
- `model`
- `car_type`

### 7. Motorcycles Table
- All fields from vehicles table
- `motor_type`

## How to Run Migration

### Step 1: Run Migrations
```bash
php artisan migrate
```

This will:
- Create/update all table structures
- Run the data migration automatically

### Step 2: Verify Data Migration

Check the migrated data:
```bash
php artisan tinker
```

```php
// Check users
\App\Models\User::count();
\App\Models\User::where('role', 'customer')->count();
\App\Models\User::where('role', 'staff')->count();
\App\Models\User::where('role', 'admin')->count();

// Check customer table
\App\Models\Customer::count();

// Check staff table
DB::table('staff')->count();

// Check admin table
DB::table('admin')->count();

// Check vehicles
DB::table('vehicles')->count();
\App\Models\Car::count();
\App\Models\Motorcycle::count();
```

## Migration Details

### User Data Migration
- All users are kept in the `users` table
- Users with role='customer' → copied to `customer` table
- Users with role='staff' → copied to `staff` table
- Users with role='admin' → copied to `admin` table
- If username is null, it's set to email or name

### Vehicle Data Migration
- Vehicles with vehicleType='car' or similar → copied to `cars` table
- Vehicles with vehicleType='motorcycle' or similar → copied to `motorcycles` table
- All vehicle data is preserved with proper field mapping

## Important Notes

1. **Data Duplication**: Users are duplicated in role-specific tables. The `users` table remains the primary authentication table.

2. **Foreign Keys**: Staff and Admin tables have `user_id` foreign keys linking back to users table.

3. **Vehicle Type Detection**: The migration automatically detects vehicle type from:
   - `vehicleType` field
   - `vehicle_type` field
   - Defaults to 'Car' if not specified

4. **Field Mapping**: The migration handles various field name variations:
   - `plate_number` → `plate_no`
   - `availability_status` → `available_status`
   - `created_date` → `createdDate`
   - `daily_rate` → `rental_price`
   - etc.

## Troubleshooting

If migration fails:
1. Check database connection
2. Verify existing table structures
3. Check for duplicate entries (email, plate numbers)
4. Review migration logs: `php artisan migrate:status`

## Rollback

To rollback migrations:
```bash
php artisan migrate:rollback --step=8
```

Note: This will NOT delete the migrated data, only the table structures. Data will remain in the tables.







