# Run Data Migration

## Quick Start

Run this command to migrate all your data:

```bash
php artisan migrate
```

This will:
1. Create/update all table structures
2. Automatically migrate data from existing tables to new structure

## What Gets Migrated

### Users → Customer/Staff/Admin
- All users with `role='customer'` → copied to `customer` table
- All users with `role='staff'` → copied to `staff` table  
- All users with `role='admin'` → copied to `admin` table
- Users table remains intact (for authentication)

### Vehicles → Cars/Motorcycles
- Vehicles with `vehicleType='car'` or similar → copied to `cars` table
- Vehicles with `vehicleType='motorcycle'` or similar → copied to `motorcycles` table
- Field names are automatically mapped (e.g., `registration_number` → `plate_no`)

## Verification

After migration, verify data:

```bash
php artisan tinker
```

```php
// Check users
User::count();
User::where('role', 'customer')->count();
User::where('role', 'staff')->count();
User::where('role', 'admin')->count();

// Check customer table
DB::table('customer')->count();
DB::table('customer')->first();

// Check staff table
DB::table('staff')->count();
DB::table('staff')->first();

// Check admin table
DB::table('admin')->count();
DB::table('admin')->first();

// Check vehicles
DB::table('vehicles')->count();
Car::count();
Motorcycle::count();
```

## Important Notes

1. **Data is duplicated** - Users exist in both `users` and role-specific tables
2. **No data loss** - Original data remains in source tables
3. **Field mapping** - Migration handles various field name variations automatically
4. **Safe to re-run** - Uses `updateOrInsert` so it won't create duplicates







