# Car and Motorcycle Controller Update Summary

## ✅ Changes Completed

### 1. Created Car and Motorcycle Models
- ✅ `app/Models/Car.php` - Points to `cars` table with `vehicleID` as primary key
- ✅ `app/Models/Motorcycle.php` - Points to `motorcycles` table
- ✅ Both models have accessor methods to map field names:
  - `vehicle_brand` → `brand` (accessor)
  - `vehicle_model` → `model` (accessor)
  - `plate_number` → `registration_number` (accessor)
  - `rental_price` → `daily_rate` (accessor)
  - `availability_status` → `status` (accessor with mapping)

### 2. Updated AdminVehicleController
- ✅ `cars()` method now uses `Car` model and `filteredCars()` method
- ✅ `motorcycles()` method now uses `Motorcycle` model and `filteredMotorcycles()` method
- ✅ `others()` method still uses `Vehicle` model (for other categories)
- ✅ `show()` method updated to find vehicle in cars, motorcycles, or vehicles tables
- ✅ `store()` method updated to create in appropriate table based on `vehicle_type`
- ✅ `update()` method updated to handle all three types
- ✅ `updateRentalPrice()` method updated to handle all three types

### 3. Updated Views
- ✅ `cars.blade.php` - Updated form fields to use `vehicle_brand`, `vehicle_model`, `plate_number`, `rental_price`, `availability_status`
- ✅ `motorcycles.blade.php` - Updated form fields to use correct field names
- ✅ Both views use accessor methods so `$vehicle->brand`, `$vehicle->daily_rate`, etc. still work

## 📋 Field Name Mapping

### Car/Motorcycle Table Fields → Accessor Methods
- `vehicle_brand` → `$vehicle->brand`
- `vehicle_model` → `$vehicle->model`
- `plate_number` → `$vehicle->registration_number`
- `rental_price` → `$vehicle->daily_rate`
- `availability_status` → `$vehicle->status` (with value mapping)
- `vehicleID` (cars) or `id` (motorcycles) → `$vehicle->id`

## 🔧 Database Tables

### Cars Table
- Primary Key: `vehicleID`
- Fields: `vehicle_brand`, `vehicle_model`, `plate_number`, `rental_price`, `availability_status`, etc.
- No timestamps (uses `created_date`)

### Motorcycles Table
- Primary Key: `id`
- Fields: `vehicle_brand`, `vehicle_model`, `plate_number`, `rental_price`, `availability_status`, etc.
- Has timestamps

### Vehicles Table (for "Others")
- Primary Key: `id`
- Fields: `brand`, `model`, `registration_number`, `daily_rate`, `status`
- Has timestamps

## 🚀 Usage

### Creating a Car
```php
Car::create([
    'vehicle_brand' => 'Toyota',
    'vehicle_model' => 'Vios',
    'plate_number' => 'ABC1234',
    'rental_price' => 150.00,
    'availability_status' => 'Available',
]);
```

### Creating a Motorcycle
```php
Motorcycle::create([
    'vehicle_brand' => 'Honda',
    'vehicle_model' => 'CBR',
    'plate_number' => 'XYZ5678',
    'rental_price' => 80.00,
    'availability_status' => 'Available',
]);
```

### Accessing Fields (Works with Accessors)
```php
$car = Car::find(1);
echo $car->brand; // Returns vehicle_brand
echo $car->daily_rate; // Returns rental_price
echo $car->status; // Returns mapped availability_status
```

## ⚠️ Important Notes

1. **Car table uses `vehicleID` as primary key**, not `id`
2. **Motorcycle table uses `id` as primary key**
3. **Views use accessor methods** so they can still use `$vehicle->brand`, `$vehicle->daily_rate`, etc.
4. **Forms must use actual field names** (`vehicle_brand`, `rental_price`, etc.) when submitting
5. **Status mapping**: `availability_status` values are mapped to standard status values (Available, Rented, Maintenance)








