# Model Update Summary

## Overview
All models have been updated to match the new database structure as specified.

## Updated Models

### Core Models
1. **User** - Updated with new structure:
   - Primary Key: `userID`
   - Fields: `username`, `password`, `email`, `phone`, `name`, `lastLogin`, `dateRegistered`, `DOB`, `age`, `isActive`
   - Relationships: `customer()`, `admin()`, `staff()`

2. **Customer** - Completely restructured:
   - Primary Key: `customerID`
   - Fields: `phone_number`, `address`, `customer_license`, `emergency_contact`, `booking_times`, `userID`
   - Relationships: `user()`, `local()`, `localStudent()`, `localUtmStaff()`, `international()`, `internationalStudent()`, `internationalUtmStaff()`, `studentDetail()`, `bookings()`, `walletAccount()`, `loyaltyCard()`, `browseHistory()`

3. **Admin** - New model:
   - Primary Key: `adminID`
   - Fields: `ic_no`, `userID`
   - Relationship: `user()`

4. **Staff** - New model:
   - Primary Key: `staffID`
   - Fields: `ic_no`, `userID`
   - Relationships: `user()`, `staffDetail()`, `staffIt()`, `runner()`

### Customer Type Models
5. **Local** - New model for local customers
6. **LocalStudent** - New model for local students
7. **LocalUtmStaff** - New model for local UTM staff
8. **International** - New model for international customers
9. **InternationalStudent** - New model for international students
10. **InternationalUtmStaff** - New model for international UTM staff
11. **StudentDetail** - New model for student details

### Staff Related Models
12. **StaffDetail** - New model for staff details
13. **StaffIt** - New model for IT staff
14. **Runner** - New model for runner staff

### Booking and Payment Models
15. **Booking** - Updated with new structure:
   - Fields: `lastUpdateDate`, `rental_start_date`, `rental_end_date`, `duration`, `deposit_amount`, `rental_amount`, `pickup_point`, `return_point`, `addOns_item`, `booking_status`, `customerID`, `vehicleID`

16. **Payment** - Updated with new structure:
   - Fields: `payment_bank_name`, `payment_bank_account_no`, `payment_date`, `total_amount`, `payment_status`, `transaction_reference`, `isPayment_complete`, `payment_isVerify`, `latest_Update_Date_Time`, `bookingID`

17. **AdditionalCharge** - New model for additional charges

### Vehicle Models
18. **Car** - Simplified structure:
   - Fields: `vehicleID`, `seating_capacity`, `transmission`, `model`, `car_type`

19. **Motorcycle** - Simplified structure:
   - Fields: `vehicleID`, `motor_type`

20. **VehicleDocument** - Updated structure:
   - Fields: `maintenanceID`, `mileage`, `service_date`, `service_type`, `next_due_date`, `cost`, `service_center`, `description`, `vehicleID`, `staffID`

### Other Models
21. **Invoice** - Updated structure
22. **LoyaltyCard** - New model
23. **WalletAccount** - Updated structure
24. **Review** - New model
25. **SystemLog** - New model
26. **OwnerCar** - New model
27. **CarImg** - New model
28. **GrantDoc** - New model
29. **Insurance** - New model
30. **Roadtax** - New model
31. **BrowseHistory** - New model

## Next Steps Required

### Controllers
All controllers need to be updated to:
- Use new field names (e.g., `userID` instead of `id`, `customerID` instead of `customer_id`)
- Use new relationships (e.g., `customer()->user()` instead of direct user access)
- Handle new model structures
- Update validation rules

### Views
All views need to be updated to:
- Use new field names in forms and displays
- Handle new relationship structures
- Update form field names and validation

### Authentication
The authentication system needs updating to:
- Use `userID` as primary key
- Use `username` for login
- Update password hashing if needed

### Middleware
Role checking middleware needs updating to use new relationships:
- Check `user()->admin()` instead of `role === 'admin'`
- Check `user()->staff()` instead of `role === 'staff'`
- Check `user()->customer()` instead of `role === 'customer'`

## Important Notes

1. **Primary Keys**: All models now use specific primary keys (e.g., `userID`, `customerID`, `bookingID`) instead of generic `id`

2. **Relationships**: Many relationships have changed - Customer now relates to User, not the other way around in many cases

3. **Field Names**: Field names have changed throughout (e.g., `phone_number` instead of `phone`, `rental_amount` instead of `total_price`)

4. **Composite Keys**: BrowseHistory uses a composite primary key which requires special handling

5. **Timestamps**: Many tables don't have `created_at`/`updated_at` - models have `public $timestamps = false` where appropriate

