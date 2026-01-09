# Profile Validation Before Booking Implementation

## Overview
This implementation enforces profile validation at the **controller level** (not JavaScript) to ensure customers complete required profile information before proceeding with vehicle bookings.

## Implementation Details

### 1. BookingController Changes (`app/Http/Controllers/BookingController.php`)

#### Added Profile Validation in `store()` Method
```php
// ===== PROFILE VALIDATION =====
// Check if user has completed required profile fields before booking
$user = Auth::user();
$customer = Customer::where('userID', $user->userID)->first();

if (!$customer || $this->isProfileIncomplete($customer)) {
    return redirect()
        ->route('profile.edit')
        ->with('warning', 'Please complete your profile information before proceeding with booking.');
}
```

This validation happens **immediately after authentication check** and **before any booking data is processed**.

#### Added `isProfileIncomplete()` Helper Method
The method checks these required fields:

**Basic Customer Information:**
- `phone_number` (required)
- `customer_license` (required)
- `address` (required)

**Identity Information (must have Local OR International):**
- **For Local Users (IC):**
  - `ic_no` from Local table
  - `stateOfOrigin` from Local table
  
- **For International Users (Passport):**
  - `passport_no` from International table
  - `countryOfOrigin` from International table

**Student Information (if applicable):**
- `matric_number` from LocalStudent/InternationalStudent
- `college` from StudentDetails
- `faculty` from StudentDetails
- `programme` from StudentDetails

**Returns:**
- `true` if ANY required field is missing → profile incomplete
- `false` if ALL required fields are present → profile complete

### 2. ProfileController Changes (`app/Http/Controllers/ProfileController.php`)

#### Updated `edit()` Method
Now properly populates `profileData` array with:
- `phone_number`, `customer_license`, `address`
- `identity_type` (ic or passport)
- `identity_value` (ic_no or passport_no)
- `state` (stateOfOrigin or countryOfOrigin)
- `matric_number`, `college`, `faculty`, `program`
- `emergency_contact_number`, `emergency_relationship`, `bank_name`, `bank_account_number`

#### Updated `update()` Method
Now handles:
- **Identity Type Selection:** Properly creates Local (IC) or International (Passport) records
- **Cleanup:** Removes conflicting records (e.g., removes International when switching to IC)
- **Student Data:** Creates StudentDetails and links via LocalStudent/InternationalStudent
- **Emergency Contact:** Stores in Customer.emergency_contact field
- **Transaction Safety:** Uses DB::beginTransaction() for data integrity

### 3. Profile Form View (`resources/views/profile/partials/update-profile-information-form.blade.php`)

#### Already Includes:
- **Warning Message Display:** Shows session warning at the top
```php
@if (session('warning'))
    <div class="mt-4 p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50">
        <span class="font-medium">Notice:</span> {{ session('warning') }}
    </div>
@endif
```

- **All Required Fields:**
  - Phone Number ✓
  - Identity Type Selector (IC/Passport) ✓
  - Identity Value (IC Number/Passport Number) ✓
  - File Upload for Identity ✓
  - Driving License Number ✓
  - File Upload for License ✓
  - Matric Number (if applicable) ✓
  - File Upload for Matric ✓
  - College, Faculty, Program selectors ✓
  - Emergency Contact (Number + Relationship) ✓
  - Bank Name & Account Number ✓
  - Address ✓
  - State/Country of Origin ✓

## Workflow

### Scenario 1: User with Incomplete Profile
1. Customer clicks "Proceed to Booking" on vehicle detail page
2. Form POSTs to `POST /booking/{vehicleID}` (BookingController@store)
3. Authentication check passes
4. **Profile validation check: FAILS** (missing required fields)
5. **Redirect to profile edit page** with warning message
6. Warning displays: "Please complete your profile information before proceeding with booking."
7. Customer fills out profile form and saves
8. Booking attempt is retried → now passes validation

### Scenario 2: User with Complete Profile
1. Customer clicks "Proceed to Booking"
2. Form POSTs to `POST /booking/{vehicleID}` (BookingController@store)
3. Authentication check passes
4. **Profile validation check: PASSES** (all required fields present)
5. Booking proceeds normally through confirmation page
6. Customer can complete booking and payment

## Database Schema Used (No Changes Required)

### Customer Table
```
- customerID (PK)
- userID (FK)
- phone_number
- address
- customer_license
- emergency_contact
```

### Local Table (for IC/Local Identity)
```
- customerID (PK, FK)
- ic_no
- stateOfOrigin
```

### International Table (for Passport/International Identity)
```
- customerID (PK, FK)
- passport_no
- countryOfOrigin
```

### LocalStudent Table
```
- customerID (PK, FK)
- matric_number (FK)
```

### InternationalStudent Table
```
- customerID (PK, FK)
- matric_number (FK)
```

### StudentDetails Table
```
- matric_number (PK)
- college
- faculty
- programme
```

## Key Design Decisions

1. **Controller-Level Validation Only**
   - No JavaScript validation blocks submissions
   - Server enforces rules for security
   - Clean separation of concerns

2. **Identity Type Flexibility**
   - Supports both IC (Local) and Passport (International)
   - Automatically creates appropriate records
   - Handles switching between types

3. **Optional Student Information**
   - Students must complete all student fields
   - Non-students can leave matric_number empty
   - Automatic validation based on whether matric_number is provided

4. **File Uploads Not Validated at Controller**
   - File upload fields are present in form
   - Currently not saved to database (no schema columns)
   - Can be extended to save file paths if needed

5. **Emergency Contact Storage**
   - Stored in Customer.emergency_contact (single field)
   - Form splits into relationship + number for UX
   - Currently relationship is validated but not stored

6. **Bank Information**
   - Form fields are present for UX
   - Currently validated but not persisted (no schema columns)
   - Can be extended to save to database if payment system requires

## Testing the Implementation

### Test Case 1: Block Incomplete Profile
1. Create new user with empty customer record
2. Try to book a vehicle
3. **Expected:** Redirect to profile.edit with warning message
4. **Result:** ✓

### Test Case 2: Allow Complete Profile
1. User completes all required profile fields:
   - Phone number
   - License number
   - Address
   - IC number + State (OR Passport + Country)
   - Optional: Matric number + College/Faculty/Program if student
2. Try to book a vehicle
3. **Expected:** Proceed to booking confirmation
4. **Result:** ✓

### Test Case 3: Incomplete Student Info Blocks Booking
1. User provides matric_number but missing college/faculty
2. Try to book
3. **Expected:** Redirect to profile.edit with warning
4. **Result:** ✓

### Test Case 4: Profile Not Updated But Required
1. User skips profile completion
2. Multiple booking attempts
3. **Expected:** Each attempt redirects to profile.edit
4. **Result:** ✓

## API Routes Added

No new API routes were required. Uses existing endpoints:
- `POST /booking/{vehicleID}` - Submit booking
- `GET /profile` - Edit profile page
- `PATCH /profile` - Update profile

## Error Handling

### If Customer Record Missing
```php
if (!$customer) {
    // Treated as incomplete profile
    // Redirects to profile.edit
}
```

### If Identity Record Missing (neither Local nor International)
```php
if (!$local && !$international) {
    return true; // Profile incomplete
}
```

### If Student Fields Incomplete
```php
if (empty($studentDetails->college) || empty($studentDetails->faculty) || empty($studentDetails->programme)) {
    return true; // Profile incomplete
}
```

## Logging & Monitoring

The implementation uses existing error handling:
- No errors logged for validation redirects (expected behavior)
- Catches exceptions during profile update with detailed logging
- Uses Laravel's Log facade for any unexpected errors

## Future Enhancements

1. **File Storage:** Implement file uploads for identity, license, and matric documents
2. **Bank Information:** Persist bank details to database if payment processing requires it
3. **Emergency Contact:** Create separate EmergencyContact table with relationship field
4. **Validation Messages:** Add field-specific error messages during profile validation
5. **Partial Updates:** Allow users to update profiles in steps
6. **Audit Trail:** Log all profile updates for compliance

## Notes

- The implementation prioritizes **server-side security** over client-side convenience
- All validation happens at controller level before any booking creation
- Profile data is reusable and follows existing database relationships
- No breaking changes to existing routes or database schema
- Message is user-friendly and actionable
