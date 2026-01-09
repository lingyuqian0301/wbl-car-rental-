# Profile Persistence Fix - Verification Guide

## Quick Summary of Changes

### 1. Customer Model (`app/Models/Customer.php`)
- ✅ Added 13 new fields to `$fillable` array
- ✅ Now allows mass assignment of all profile fields

### 2. ProfileController (`app/Http/Controllers/ProfileController.php`)
- ✅ Rewritten `update()` method with 5-part structure:
  - Part A: User table (name, email)
  - Part B: Customer table (ALL profile fields using updateOrCreate)
  - Part C: File uploads (store files + save paths)
  - Part D: Identity records (Local or International)
  - Part E: Student information
- ✅ All wrapped in database transaction

## Testing Steps

### Test 1: Complete Profile Save
```
1. Open profile page
2. Fill ALL fields:
   - Phone number
   - License expiry date
   - Address
   - Identity (IC number or Passport number)
   - State/Country
   - College/Faculty/Program (if student)
   - Emergency contact
   - Bank name & account number
3. Click Save
4. **Expected:** Success message appears
5. Refresh page (Ctrl+R)
6. **Verify:** All fields show saved values ✓
```

### Test 2: Database Verification
```
1. Open database client (MySQL Workbench, PhpMyAdmin, etc.)
2. Query customer table:
   SELECT * FROM customer WHERE userID = [your_user_id];
3. **Verify these fields are saved:**
   ✓ phone_number
   ✓ customer_license
   ✓ address
   ✓ emergency_contact
   ✓ emergency_relationship
   ✓ default_bank_name
   ✓ default_account_no
   ✓ identity_type
   ✓ identity_value
   ✓ matric_number
   ✓ college
   ✓ faculty
   ✓ program
   ✓ state
   ✓ file_identity (if uploaded)
   ✓ file_license (if uploaded)
   ✓ file_matric (if uploaded)
```

### Test 3: File Upload
```
1. Edit profile
2. Upload any file (identity, license, or matric)
3. Click Save
4. **Verify file storage:**
   - Check: storage/app/public/profiles/{userID}/
   - File should exist there
5. **Verify in database:**
   - Check customer.file_identity (or file_license/file_matric)
   - Should contain path like: profiles/1/ABC123.pdf
```

### Test 4: Identity Type Switching
```
1. Create profile with IC
2. Save profile
3. Check local table - ic_no should exist
4. Edit profile again
5. Change to Passport
6. Click Save
7. **Verify:**
   - local record deleted (no longer needed)
   - international record created
   - database is clean (no orphaned records)
```

### Test 5: Student Information
```
1. Edit profile with IC
2. Enter matric number + college/faculty/program
3. Click Save
4. **Verify:**
   - StudentDetails created (matric_number as PK)
   - LocalStudent linked (customerID → matric_number)
   - Refresh page - all values show
```

### Test 6: Booking Flow
```
1. Complete entire profile (all required fields)
2. Save profile
3. Go to vehicle detail page
4. Click "Proceed to Booking"
5. **Expected:** Booking form shows (NOT redirected to profile)
6. **Verify:** Booking validation passed ✓
```

## Quick Checks

### ✅ Form Values Persist After Save
```
1. Edit profile
2. Fill phone_number, address, bank info
3. Save
4. Refresh (F5)
5. All values visible? → PASS ✓
```

### ✅ Database Contains Data
```
SELECT COUNT(*) FROM customer 
WHERE userID = [id] 
AND phone_number IS NOT NULL 
AND address IS NOT NULL;
```
Result: 1 row → PASS ✓

### ✅ Files Are Stored
```
ls -la storage/app/public/profiles/[userID]/
```
Result: Files present → PASS ✓

### ✅ Booking Doesn't Redirect
```
1. Complete profile
2. Save
3. Click book button
4. Sees booking form (not profile form) → PASS ✓
```

## Logs to Check

**Success indicators in `storage/logs/laravel.log`:**
```
[YYYY-MM-DD HH:MM:SS] local.INFO: Identity file stored at: profiles/1/ABC123.pdf
[YYYY-MM-DD HH:MM:SS] local.INFO: License file stored at: profiles/1/XYZ789.pdf
```

**Error indicators:**
```
[YYYY-MM-DD HH:MM:SS] local.ERROR: Profile update error: [message]
```

## Troubleshooting

### Issue: "Only phone_number saves"
**Solution:** 
1. Clear Laravel cache: `php artisan cache:clear`
2. Verify Customer.php fillable array includes all fields
3. Check database schema (all columns exist)

### Issue: "File upload fails"
**Solution:**
1. Check storage symlink: `php artisan storage:link`
2. Verify storage/app/public exists
3. Check file permissions (775)

### Issue: "Form shows empty values after save"
**Solution:**
1. Check edit() method loads data from database
2. Verify data was actually saved (check database directly)
3. Clear browser cache

### Issue: "Identity record not saved"
**Solution:**
1. Check Local/International record created
2. Verify customerID was set correctly
3. Check foreign key constraints

### Issue: "Student info doesn't persist"
**Solution:**
1. Verify StudentDetails created with matric_number
2. Check LocalStudent/InternationalStudent linked correctly
3. Verify all required fields filled (college, faculty, program)

## Success Criteria Met?

- [ ] **Data Persistence:** Form data visible after refresh ✓
- [ ] **Database Storage:** All fields in customer table ✓
- [ ] **File Uploads:** Files in storage + paths in database ✓
- [ ] **Identity Records:** Local or International created ✓
- [ ] **Student Info:** StudentDetails linked correctly ✓
- [ ] **Booking Flow:** No redirect after complete profile ✓

## Command Reference

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Check file storage
ls -la storage/app/public/profiles/

# Check logs
tail -f storage/logs/laravel.log

# Connect to database
mysql -u root car_rental_system

# Check customer data
SELECT * FROM customer LIMIT 5\G
```

## If All Tests Pass ✓

**You're done! The profile persistence is fixed:**
- ✅ All fields save correctly
- ✅ Data persists after refresh
- ✅ Files uploaded and stored
- ✅ Booking flow works
- ✅ Database clean and consistent

---

**Last Updated:** January 9, 2026
**Status:** Ready for Production
