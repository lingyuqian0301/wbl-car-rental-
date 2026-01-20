# Google Drive Integration - Quick Start

## ✅ What's Been Done

All file uploads now automatically save to Google Drive:
- ✅ Vehicle photos → `vehicle_photos/`
- ✅ Vehicle documents → `vehicle_documents/insurance/`, `grant/`, `roadtax/`, `contract/`
- ✅ Owner documents (license & IC) → `owner_documents/licenses/` & `ic/`
- ✅ Customer documents (license & IC) → `customer_documents/licenses/` & `ic_passport/`
- ✅ Fuel receipts → `fuel_receipts/`
- ✅ Maintenance images → `maintenance_images/`
- ✅ Payment receipts → `payment_receipts/`

## 🚀 Setup Steps

### 1. Install Credentials (5 minutes)
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create project → Enable "Google Drive API"
3. Create OAuth 2.0 credentials (Desktop app)
4. Download JSON and save as: `config/google-credentials.json`
5. Add to `.gitignore`:
   ```
   /config/google-credentials.json
   /storage/app/google-drive-token.json
   ```

### 2. Authorize (2 minutes)
1. Visit: `http://your-domain/admin/google-drive/auth`
2. Sign in with **hephaestussdt@gmail.com**
3. Grant permissions
4. Done! Token saved automatically

### 3. Test Upload
1. Go to any vehicle/customer page
2. Upload a photo or document
3. Check Google Drive → Files should appear automatically!

## 📝 Update Views (Optional)

To display Google Drive images, replace in your Blade templates:

**Before:**
```blade
<img src="{{ asset('storage/' . $filePath) }}">
```

**After:**
```blade
<img src="{{ getFileUrl($filePath) }}">
```

**Or use Blade directive:**
```blade
<img src="@fileUrl($filePath)">
```

The helper automatically handles:
- Google Drive file IDs (new uploads)
- Local file paths (existing files)
- Thumbnails for images

## 📚 Full Documentation

See `GOOGLE_DRIVE_SETUP.md` for detailed setup instructions.

See `GOOGLE_DRIVE_IMPLEMENTATION_SUMMARY.md` for technical details.

## ⚡ Quick Commands

**Check if credentials exist:**
```bash
ls -la config/google-credentials.json
```

**Check if authorized:**
```bash
ls -la storage/app/google-drive-token.json
```

**Re-authorize if needed:**
1. Delete: `storage/app/google-drive-token.json`
2. Visit: `/admin/google-drive/auth`

## 🎯 That's It!

Your files will now automatically save to Google Drive at **hephaestussdt@gmail.com**!


