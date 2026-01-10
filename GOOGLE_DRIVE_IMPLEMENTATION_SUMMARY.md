# Google Drive Implementation Summary

## ✅ Completed Tasks

### 1. Core Services Created
- ✅ `app/Services/GoogleDriveService.php` - Main service for Google Drive operations
- ✅ `app/Traits/HandlesGoogleDriveUploads.php` - Trait for easy file upload/retrieval
- ✅ `app/Helpers/FileHelper.php` - Helper function `getFileUrl()` for view templates
- ✅ `app/Http/Controllers/GoogleDriveAuthController.php` - OAuth authorization controller

### 2. Configuration Updated
- ✅ `config/filesystems.php` - Added Google Drive disk configuration
- ✅ `composer.json` - Registered FileHelper in autoload
- ✅ `app/Providers/AppServiceProvider.php` - Added Blade directive `@fileUrl()`
- ✅ `routes/web.php` - Added Google Drive OAuth routes

### 3. Controllers Updated (All upload methods now use Google Drive)
- ✅ `AdminVehicleController.php`:
  - `storePhoto()` - Vehicle photos → `vehicle_photos/`
  - `storeDocument()` - Vehicle documents → `vehicle_documents/{type}/`
  - `uploadOwnerLicense()` - Owner licenses → `owner_documents/licenses/`
  - `uploadOwnerIc()` - Owner IC → `owner_documents/ic/`
  - `storeFuel()` - Fuel receipts → `fuel_receipts/`
  - `updateFuel()` - Fuel receipts (update)
  - `storeMaintenance()` - Maintenance images → `maintenance_images/`
  - `destroyFuel()` - Delete fuel receipt

- ✅ `AdminCustomerController.php`:
  - `uploadLicense()` - Customer licenses → `customer_documents/licenses/`
  - `uploadIc()` - Customer IC/Passport → `customer_documents/ic_passport/`

- ✅ `PaymentController.php`:
  - `submitPayment()` - Payment receipts → `payment_receipts/`

### 4. Views Update Status
⚠️ **Partial**: Some views updated, others need manual update

**Updated Views:**
- `resources/views/admin/vehicles/show.blade.php` (partially - vehicle photos section)

**Views Needing Update:**
All views that display images need to replace:
```blade
{{ asset('storage/' . $filePath) }}
```

With:
```blade
{{ getFileUrl($filePath) }}
```
or
```blade
@fileUrl($filePath)
```

**Files to update:**
- `resources/views/admin/vehicles/show.blade.php` (all document displays)
- `resources/views/admin/customers/show.blade.php` (license and IC displays)
- Any other views displaying vehicle/customer/owner images

### 5. Documentation Created
- ✅ `GOOGLE_DRIVE_SETUP.md` - Complete setup guide

## 📋 Next Steps Required

### Step 1: Google Cloud Console Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google Drive API
4. Create OAuth 2.0 credentials (Desktop app type)
5. Download credentials JSON file
6. Place it in `config/google-credentials.json`

### Step 2: First-Time Authorization
1. Visit: `http://your-domain/admin/google-drive/auth`
2. Sign in with hephaestussdt@gmail.com
3. Grant permissions
4. Token will be saved automatically

### Step 3: Update Remaining Views (Optional but Recommended)
Run a find-and-replace in your views:

**Find:** `asset('storage/' . `
**Replace:** `getFileUrl(`

Or manually update each instance to use `getFileUrl()` helper.

### Step 4: Environment Configuration
Add to `.env`:
```env
GOOGLE_DRIVE_FOLDER_ID=root
GOOGLE_DRIVE_ENABLE_CACHE=true
```

If you want files in a specific Google Drive folder, get the folder ID from the URL and set it.

### Step 5: Test Upload
1. Upload a vehicle photo through admin panel
2. Check Google Drive account (hephaestussdt@gmail.com)
3. Verify file appears in `vehicle_photos/` folder

## 🔍 How It Works

### Upload Flow
1. User uploads file through form
2. Controller uses `HandlesGoogleDriveUploads` trait
3. `uploadToGoogleDrive()` method uploads to Google Drive
4. Google Drive file ID is stored in database
5. File is organized in folder structure automatically

### Display Flow
1. View retrieves file ID/path from database
2. `getFileUrl()` helper checks if it's a Google Drive ID or local path
3. If Google Drive ID: Fetches URL from Google Drive API
4. If local path: Uses `asset('storage/...')` as fallback
5. Returns appropriate URL for display

### Folder Structure in Google Drive
```
vehicle_photos/
vehicle_documents/
  ├── insurance/
  ├── grant/
  ├── roadtax/
  └── contract/
owner_documents/
  ├── licenses/
  └── ic/
customer_documents/
  ├── licenses/
  └── ic_passport/
fuel_receipts/
maintenance_images/
payment_receipts/
```

## 🔧 Troubleshooting

### "Cannot find credentials file"
- Ensure `config/google-credentials.json` exists
- Check file permissions (readable)

### "Access token expired"
- Delete `storage/app/google-drive-token.json`
- Re-authorize via `/admin/google-drive/auth`

### Files not displaying
- Check Laravel logs: `storage/logs/laravel.log`
- Verify `getFileUrl()` helper is being used in views
- Check if file ID exists in database

### Upload fails but no error
- Check Google Drive API quota
- Verify OAuth consent screen is configured
- Check folder permissions in Google Drive

## 📝 Important Notes

1. **Backward Compatibility**: The system handles both Google Drive file IDs and local file paths, so existing local files will still work.

2. **Security**: Never commit `google-credentials.json` or `google-drive-token.json` to version control.

3. **Migration**: Existing local files can be migrated to Google Drive using a migration script (see GOOGLE_DRIVE_SETUP.md).

4. **Fallback**: If Google Drive upload fails, the system falls back to local storage automatically.

5. **Performance**: Google Drive URLs are cached to improve performance. Consider implementing a caching layer if needed.

## 🎯 File Upload Locations Summary

| File Type | Google Drive Folder | Database Column | Controller Method |
|-----------|-------------------|----------------|-------------------|
| Vehicle Photos | `vehicle_photos/` | `VehicleDocument.fileURL` | `AdminVehicleController::storePhoto()` |
| Vehicle Documents | `vehicle_documents/{type}/` | `VehicleDocument.fileURL` | `AdminVehicleController::storeDocument()` |
| Owner License | `owner_documents/licenses/` | `OwnerCar.license_img` | `AdminVehicleController::uploadOwnerLicense()` |
| Owner IC | `owner_documents/ic/` | `OwnerCar.ic_img` | `AdminVehicleController::uploadOwnerIc()` |
| Customer License | `customer_documents/licenses/` | `Customer.customer_license_img` | `AdminCustomerController::uploadLicense()` |
| Customer IC/Passport | `customer_documents/ic_passport/` | `Customer.customer_ic_img` | `AdminCustomerController::uploadIc()` |
| Fuel Receipts | `fuel_receipts/` | `Fuel.receipt_img` | `AdminVehicleController::storeFuel()` |
| Maintenance Images | `maintenance_images/` | `VehicleMaintenance.maintenance_img` | `AdminVehicleController::storeMaintenance()` |
| Payment Receipts | `payment_receipts/` | (Not stored in DB currently) | `PaymentController::submitPayment()` |

## ✨ Benefits

1. **Centralized Storage**: All files in one Google Drive account
2. **Automatic Organization**: Files organized in folders automatically
3. **Scalability**: No server storage limits
4. **Accessibility**: Access files from anywhere via Google Drive
5. **Backup**: Automatic backup through Google Drive
6. **Sharing**: Easy to share files with team members if needed

## 📞 Support

If you encounter issues:
1. Check `GOOGLE_DRIVE_SETUP.md` for detailed setup instructions
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify Google Cloud Console configuration
4. Ensure OAuth credentials are correct

