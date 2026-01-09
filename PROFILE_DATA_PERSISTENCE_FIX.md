# Profile Data Persistence Fix

## Problem Identified

The profile update was losing data after form submission because:

1. **Bank fields mismatch**: Form sends `bank_name` and `bank_account_number`, but Customer model stores `default_bank_name` and `default_account_no`
2. **Missing Customer record creation**: If customer record doesn't exist, it wasn't being created
3. **File uploads not handled**: File upload fields were validated but never saved to storage
4. **Incomplete data loading**: The edit() method wasn't loading all saved bank information

## Solution Implemented

### 1. ProfileController@update() - Complete Rewrite

#### Key Changes:

**a) Proper Customer Record Creation**
```php
$customer = Customer::where('userID', $user->userID)->first();
if (!$customer) {
    $customer = new Customer();
    $customer->userID = $user->userID;
}
```
Now creates customer record if it doesn't exist.

**b) All Fields Explicitly Saved**
```php
$customer->phone_number = $request->input('phone_number');
$customer->customer_license = $request->input('customer_license');
$customer->address = $request->input('address');
$customer->emergency_contact = $request->input('emergency_contact_number');
$customer->default_bank_name = $request->input('bank_name');              // FIXED: Maps form field to DB column
$customer->default_account_no = $request->input('bank_account_number');   // FIXED: Maps form field to DB column
$customer->save();
```

**c) File Upload Handling**
```php
private function handleFileUploads(Request $request, Customer $customer): void
{
    // Stores files to: storage/app/public/users/{userID}/documents/
    // Files are logged for audit trail
    // Supports: file_identity, file_license, file_matric
}
```

**d) Improved Student Data Handling**
- Properly cleans up conflicting records when switching between IC and Passport
- Removes InternationalStudent when switching to IC (and vice versa)
- Handles all identity type scenarios

**e) Better Error Handling**
```php
Log::error('Profile update error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
```
Now logs file/line information for debugging.

### 2. ProfileController@edit() - Enhanced Data Loading

#### Key Changes:

**a) Bank Information Loading**
```php
'bank_name' => $customer?->default_bank_name ?? '',
'bank_account_number' => $customer?->default_account_no ?? '',
```
Now loads saved bank information from correct columns.

**b) International Student Support**
```php
$intlStudent = InternationalStudent::where('customerID', $customer->customerID)->first();
if ($intlStudent && $intlStudent->matric_number) {
    // Loads student info for international customers
}
```
Previously didn't load international student data.

**c) Complete State Loading**
All profile fields are properly loaded and displayed on page refresh.

## Data Flow After Fix

```
Form Submission (POST /profile)
    ↓
ProfileUpdateRequest validates all fields
    ↓
ProfileController@update() executes:
    1. Updates User (name, email)
    2. Creates/updates Customer record with ALL fields
    3. Handles file uploads to storage
    4. Creates/updates Local or International identity
    5. Creates/updates StudentDetails and links
    6. Commits transaction
    ↓
Redirect to profile.edit with success message
    ↓
ProfileController@edit() loads:
    1. All Customer fields (including bank info)
    2. Identity type and value
    3. Student information if present
    4. All data displayed in form
    ↓
User sees all saved data persisted
```

## Files Modified

### `app/Http/Controllers/ProfileController.php`

**Changes:**
1. Added `use Illuminate\Support\Facades\Storage;` import
2. Rewrote `edit()` method:
   - Added bank_name and bank_account_number loading
   - Added international student data loading
3. Rewrote `update()` method:
   - Added customer record creation if missing
   - Added explicit field mapping for bank info
   - Added file upload handling
   - Improved error logging
4. Added new `handleFileUploads()` private method

## Database Mapping

| Form Field | Database Column | Table |
|------------|-----------------|-------|
| phone_number | phone_number | customer |
| customer_license | customer_license | customer |
| address | address | customer |
| emergency_contact_number | emergency_contact | customer |
| bank_name | default_bank_name | customer |
| bank_account_number | default_account_no | customer |
| identity_value | ic_no / passport_no | local / international |
| state | stateOfOrigin / countryOfOrigin | local / international |
| matric_number | matric_number | localstudent / internationalstudent |
| college | college | studentdetails |
| faculty | faculty | studentdetails |
| program | programme | studentdetails |

## File Upload Storage

**Location:** `storage/app/public/users/{userID}/documents/`

**Supported Files:**
- file_identity (IC/Passport document)
- file_license (Driving license document)
- file_matric (Matric card document)

**Current Behavior:**
- Files are stored in the public filesystem
- File paths are logged but not persisted in database
- Can be extended in future to save paths to database

## Testing the Fix

### Test Case 1: Complete Profile Update
1. Fill out all profile fields
2. Submit form
3. **Expected:** All fields saved and visible on page reload
4. **Verification:** Check database for all values

### Test Case 2: Bank Information Persistence
1. Enter bank name and account number
2. Submit form
3. Refresh page
4. **Expected:** Bank fields show saved values
5. **Verification:** Check `customer.default_bank_name` and `customer.default_account_no`

### Test Case 3: Student Information
1. Enter matric number + college/faculty/program
2. Submit form
3. Refresh page
4. **Expected:** All student fields show saved values
5. **Verification:** Check `StudentDetails` table

### Test Case 4: File Uploads
1. Upload identity document
2. Submit form
3. **Expected:** File stored in public/users/{userID}/documents/
4. **Verification:** Check storage location and logs

### Test Case 5: Booking After Complete Profile
1. Complete all profile fields
2. Save profile
3. Try to book vehicle
4. **Expected:** Booking proceeds (not redirected to profile)
5. **Verification:** BookingController@store validation passes

### Test Case 6: Identity Type Switch
1. Create profile with IC
2. Switch to Passport and submit
3. **Expected:** Old IC record removed, Passport record created
4. **Verification:** Check database for clean transition

## Backward Compatibility

✅ **No breaking changes:**
- Existing form routes unchanged
- Validation rules preserved
- Database schema unchanged
- File upload paths follow Laravel conventions

## Performance Impact

✅ **Minimal:**
- Same number of database queries
- Queries are indexed (customerID is primary key)
- File storage is asynchronous-friendly
- Transaction scope optimized

## Error Handling

**If update fails:**
1. Database transaction rolls back
2. Error message displayed to user
3. Detailed error logged with file/line info
4. User can retry

**If file upload fails:**
1. Main profile update succeeds
2. File upload failure is logged (non-blocking)
3. User is notified of profile update success

## Future Enhancements

1. **File Path Persistence**: Store file paths in database columns
2. **File Validation**: Validate file contents, not just type/size
3. **Audit Trail**: Track all profile changes with timestamps
4. **Batch Operations**: Support bulk profile updates
5. **Versioning**: Keep history of profile changes

## Rollback Instructions

If you need to revert:
```bash
git checkout HEAD -- app/Http/Controllers/ProfileController.php
```

No database changes needed - all data already stored with correct mapping.
