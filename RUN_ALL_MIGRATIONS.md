# Run All Migrations - Complete Setup

## ⚠️ IMPORTANT: Backup Your Data First!

If you have existing data, backup your database before running migrations.

## 🚀 Step-by-Step Migration Process

### Step 1: Check Current Migration Status
```bash
php artisan migrate:status
```

### Step 2: Run All Migrations
```bash
php artisan migrate
```

If you need to reset everything (WARNING: This will delete all data):
```bash
php artisan migrate:fresh
```

### Step 3: Verify All Tables Created
```bash
php artisan tinker
```

Then run:
```php
use Illuminate\Support\Facades\Schema;

// Check all tables
$tables = [
    'users',
    'vehicles',
    'bookings',
    'payments',
    'notifications',
    'item_categories',
    'booking_read_status',
    'booking_served_by',
    'customer_documents',
];

foreach ($tables as $table) {
    echo $table . ': ' . (Schema::hasTable($table) ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
}
```

### Step 4: Create Initial Data

#### Create Admin User
```php
\App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@hasta.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
```

#### Create Staff User
```php
\App\Models\User::create([
    'name' => 'Staff User',
    'email' => 'staff@hasta.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'staff',
    'email_verified_at' => now(),
]);
```

#### Create Categories
```php
$categories = [
    ['name' => 'Car', 'slug' => 'car', 'description' => 'Cars and automobiles'],
    ['name' => 'Motorcycle', 'slug' => 'motorcycle', 'description' => 'Motorcycles and bikes'],
    ['name' => 'Voucher', 'slug' => 'voucher', 'description' => 'Voucher items'],
];

foreach ($categories as $cat) {
    \App\Models\ItemCategory::create(array_merge($cat, ['is_active' => true]));
}
```

## 📋 Complete Migration Checklist

- [ ] Core Laravel tables (users, cache, jobs, sessions)
- [ ] Role system (customer, admin, staff)
- [ ] Vehicles table
- [ ] Item categories table
- [ ] Bookings table
- [ ] Bookings location fields (pickup, return, confirmed_by, completed_by)
- [ ] Payments table (with proof_of_payment, payment_method)
- [ ] Notifications table
- [ ] Booking read status table
- [ ] Booking served by table
- [ ] Customer fields (phone, address, faculty, college, blacklist)
- [ ] Customer documents table

## 🔍 Troubleshooting

### Error: Table already exists
If you get "Table already exists" errors:
```bash
php artisan migrate:refresh
```

### Error: Column already exists
If you get "Column already exists" errors:
```bash
php artisan migrate:rollback
php artisan migrate
```

### Error: Foreign key constraint fails
Make sure tables are created in the correct order. Run:
```bash
php artisan migrate:fresh
```

## ✅ Success Indicators

After successful migration, you should be able to:
1. Login with admin account
2. Access `/admin/dashboard`
3. See vehicles, bookings, payments pages
4. Create new categories in "Others" page
5. View receipts in top bar calendar

## 📞 Need Help?

Check the `MIGRATION_GUIDE.md` file for detailed information about each migration.








