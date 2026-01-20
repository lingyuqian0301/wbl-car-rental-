# Google Drive Integration Setup Guide

This guide will help you set up Google Drive integration to store all vehicle photos, customer documents, owner documents, and other files in Google Drive instead of local storage.

## Prerequisites

- A Google account (hephaestussdt@gmail.com)
- Access to Google Cloud Console
- Laravel application with `google/apiclient` and `masbug/flysystem-google-drive-ext` packages (already installed)

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account (hephaestussdt@gmail.com)
3. Click on the project dropdown and select "New Project"
4. Name it "Hasta System" or any name you prefer
5. Click "Create"

## Step 2: Enable Google Drive API

1. In the Google Cloud Console, navigate to "APIs & Services" > "Library"
2. Search for "Google Drive API"
3. Click on it and click "Enable"
4. Wait for it to be enabled (may take a minute)

## Step 3: Create OAuth 2.0 Credentials

1. Navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. If prompted, configure the OAuth consent screen first:
   - Choose "External" (unless you have a Google Workspace account)
   - Fill in the required fields:
     - App name: "Hasta System"
     - User support email: hephaestussdt@gmail.com
     - Developer contact: hephaestussdt@gmail.com
   - Click "Save and Continue"
   - Add scopes: Select "Drive API" > ".../auth/drive" (full access)
   - Click "Save and Continue"
   - Add test users: hephaestussdt@gmail.com
   - Click "Save and Continue"
   - Review and click "Back to Dashboard"

4. Now create OAuth client ID:
   - Application type: "Desktop app"
   - Name: "Hasta System Desktop Client"
   - Click "Create"

5. Download the credentials JSON file:
   - Click "Download JSON" next to your newly created OAuth client
   - Save it as `google-credentials.json`

## Step 4: Install Credentials File

1. Copy `google-credentials.json` to your Laravel project's `config/` directory:
   ```
   config/google-credentials.json
   ```

2. **Important:** Add this file to `.gitignore` to keep credentials secure:
   ```
   /config/google-credentials.json
   /storage/app/google-drive-token.json
   ```

## Step 5: Configure Environment Variables

Add the following to your `.env` file:

```env
# Google Drive Configuration
GOOGLE_DRIVE_FOLDER_ID=root
GOOGLE_DRIVE_ENABLE_CACHE=true

# Optional: If you want to use a specific folder in Google Drive
# Get the folder ID from the Google Drive URL:
# https://drive.google.com/drive/folders/FOLDER_ID_HERE
```

## Step 6: First-Time Authorization

1. Run the Laravel application:
   ```bash
   php artisan serve
   ```

2. You'll need to authorize the application on first use. The GoogleDriveService will automatically handle this:
   - When you try to upload a file for the first time, check your Laravel logs
   - If you see an authorization URL, visit it in your browser
   - Sign in with hephaestussdt@gmail.com
   - Grant permissions to the application
   - Copy the authorization code from the redirect URL

3. Create a simple route/controller to handle authorization (temporary):

   Add this to `routes/web.php`:
   ```php
   Route::get('/admin/google-drive/auth', function() {
       $service = new \App\Services\GoogleDriveService();
       $authUrl = $service->getAuthUrl();
       return redirect($authUrl);
   });

   Route::get('/admin/google-drive/callback', function(\Illuminate\Http\Request $request) {
       $code = $request->get('code');
       if (!$code) {
           return 'Authorization failed. No code received.';
       }

       $service = new \App\Services\GoogleDriveService();
       if ($service->handleCallback($code)) {
           return 'Authorization successful! You can now upload files to Google Drive.';
       }

       return 'Authorization failed.';
   });
   ```

4. Visit `http://your-domain/admin/google-drive/auth`
5. Authorize the application
6. You'll be redirected back with a code
7. The token will be saved to `storage/app/google-drive-token.json`

## Step 7: Test Upload

1. Try uploading a vehicle photo or customer document through the admin panel
2. Check your Google Drive account (hephaestussdt@gmail.com)
3. Files should appear in folders:
   - `vehicle_photos/`
   - `vehicle_documents/` (with subfolders: insurance, grant, roadtax, contract)
   - `owner_documents/licenses/`
   - `owner_documents/ic/`
   - `customer_documents/licenses/`
   - `customer_documents/ic_passport/`
   - `fuel_receipts/`
   - `maintenance_images/`
   - `payment_receipts/`

## Step 8: Update Views (If Needed)

The views have been updated to use the `getFileUrl()` helper function, which automatically handles both Google Drive file IDs and local file paths. However, you may need to update views manually if they still use `asset('storage/...')`.

To update a view, replace:
```blade
{{ asset('storage/' . $filePath) }}
```

With:
```blade
{{ getFileUrl($filePath) }}
```

Or use the Blade directive:
```blade
@fileUrl($filePath)
```

## Troubleshooting

### Error: "Cannot find credentials file"
- Ensure `google-credentials.json` is in the `config/` directory
- Check file permissions (should be readable)

### Error: "Access token expired"
- The service automatically refreshes tokens, but if it fails:
  - Delete `storage/app/google-drive-token.json`
  - Re-authorize using the auth route

### Error: "Insufficient permissions"
- Ensure you granted all requested permissions during authorization
- Check that the Google Drive API is enabled in Google Cloud Console

### Files not appearing in Google Drive
- Check Laravel logs for errors
- Verify the folder structure is created correctly
- Ensure the OAuth consent screen is configured properly

### Files uploaded but images not displaying
- Check that the file URL is correct (use `getFileUrl()` helper)
- For Google Drive files, they need to be publicly accessible or use the webContentLink
- The helper function automatically handles this

## Security Notes

- **Never commit** `google-credentials.json` or `google-drive-token.json` to version control
- Keep your OAuth credentials secure
- Regularly rotate credentials if compromised
- Use service account for production if possible (more secure than OAuth for server-to-server)

## Folder Structure in Google Drive

All files will be organized in the following structure:

```
Google Drive Root/
├── vehicle_photos/
│   └── [vehicle photos]
├── vehicle_documents/
│   ├── insurance/
│   ├── grant/
│   ├── roadtax/
│   └── contract/
├── owner_documents/
│   ├── licenses/
│   └── ic/
├── customer_documents/
│   ├── licenses/
│   └── ic_passport/
├── fuel_receipts/
├── maintenance_images/
└── payment_receipts/
```

## Support

If you encounter any issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Google Cloud Console > APIs & Services > Credentials (verify OAuth client is active)
3. Google Drive account storage (ensure you have space)

## Migration from Local Storage

If you have existing files in local storage that you want to migrate to Google Drive:

1. Create a migration script (optional):
   ```php
   // In tinker: php artisan tinker
   $driveService = new \App\Services\GoogleDriveService();
   $files = Storage::disk('public')->allFiles();
   foreach ($files as $file) {
       $fileId = $driveService->uploadFile(storage_path('app/public/' . $file), dirname($file));
       // Update database record with $fileId
   }
   ```

2. Update all database records to use Google Drive file IDs instead of local paths


