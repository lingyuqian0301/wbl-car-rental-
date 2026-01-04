# Complete Database Fix - Booking & Payment Tables

## ✅ All Changes Completed

### 1. Booking Table: `bookings` → `booking`, `status` → `booking_status`
- ✅ Migration updated
- ✅ Model updated with accessor/mutator
- ✅ All controllers updated
- ✅ All views updated (with backward compatibility)

### 2. Payment Table: `payments` → `payment`, `status` → `payment_status`
- ✅ Migration updated
- ✅ Model updated with accessor/mutator
- ✅ All controllers updated
- ✅ All views updated (with backward compatibility)

## 🚀 How to Fix Your Existing Database

### Step 1: Fix Booking Table
```sql
-- Rename table
RENAME TABLE bookings TO booking;

-- Rename column
ALTER TABLE booking CHANGE status booking_status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending';

-- Update foreign keys
ALTER TABLE payments DROP FOREIGN KEY IF EXISTS payments_booking_id_foreign;
ALTER TABLE payments ADD CONSTRAINT payments_booking_id_foreign 
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE;
```

### Step 2: Fix Payment Table
```sql
-- Rename table
RENAME TABLE payments TO payment;

-- Rename column
ALTER TABLE payment CHANGE status payment_status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending';

-- Update foreign keys
ALTER TABLE notifications DROP FOREIGN KEY IF EXISTS notifications_payment_id_foreign;
ALTER TABLE notifications ADD CONSTRAINT notifications_payment_id_foreign 
    FOREIGN KEY (payment_id) REFERENCES payment(id) ON DELETE CASCADE;
```

### Or Use Provided SQL Files:
1. Run `FIX_EXISTING_DATABASE.sql` for booking table
2. Run `FIX_PAYMENT_TABLE.sql` for payment table

Or combine both in one script:
```sql
-- Fix Booking Table
RENAME TABLE bookings TO booking;
ALTER TABLE booking CHANGE status booking_status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending';

-- Fix Payment Table
RENAME TABLE payments TO payment;
ALTER TABLE payment CHANGE status payment_status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending';

-- Update all foreign keys
ALTER TABLE payment DROP FOREIGN KEY IF EXISTS payments_booking_id_foreign;
ALTER TABLE payment ADD CONSTRAINT payment_booking_id_foreign 
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE;

ALTER TABLE booking_read_status DROP FOREIGN KEY IF EXISTS booking_read_status_booking_id_foreign;
ALTER TABLE booking_read_status ADD CONSTRAINT booking_read_status_booking_id_foreign 
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE;

ALTER TABLE booking_served_by DROP FOREIGN KEY IF EXISTS booking_served_by_booking_id_foreign;
ALTER TABLE booking_served_by ADD CONSTRAINT booking_served_by_booking_id_foreign 
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE;

ALTER TABLE notifications DROP FOREIGN KEY IF EXISTS notifications_booking_id_foreign;
ALTER TABLE notifications ADD CONSTRAINT notifications_booking_id_foreign 
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE;

ALTER TABLE notifications DROP FOREIGN KEY IF EXISTS notifications_payment_id_foreign;
ALTER TABLE notifications ADD CONSTRAINT notifications_payment_id_foreign 
    FOREIGN KEY (payment_id) REFERENCES payment(id) ON DELETE CASCADE;
```

## ✅ Verification

After fixing, verify:
```sql
-- Check tables exist
SHOW TABLES LIKE 'booking';
SHOW TABLES LIKE 'payment';

-- Check column names
DESCRIBE booking;
-- Should show 'booking_status' not 'status'

DESCRIBE payment;
-- Should show 'payment_status' not 'status'

-- Check foreign keys
SHOW CREATE TABLE payment;
-- Should reference 'booking' not 'bookings'

SHOW CREATE TABLE notifications;
-- Should reference 'booking' and 'payment'
```

## 📝 Important Notes

### Backward Compatibility
Both models have accessor/mutator methods:

**Booking Model:**
- `$booking->status` → works (maps to `booking_status`)
- `$booking->booking_status` → works (direct access)

**Payment Model:**
- `$payment->status` → works (maps to `payment_status`)
- `$payment->payment_status` → works (direct access)

### Database Queries
- **Must use**: `->where('booking_status', ...)` for bookings
- **Must use**: `->where('payment_status', ...)` for payments
- **Can use**: `$booking->status` or `$payment->status` in views (accessor handles it)

## 🔍 If Still Getting Errors

1. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. Check tables exist:
   ```bash
   php artisan tinker
   Schema::hasTable('booking'); // Should return true
   Schema::hasTable('payment'); // Should return true
   ```

3. Check columns exist:
   ```bash
   Schema::hasColumn('booking', 'booking_status'); // Should return true
   Schema::hasColumn('payment', 'payment_status'); // Should return true
   ```








