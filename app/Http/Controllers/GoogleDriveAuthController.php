<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleDriveAuthController extends Controller
{
    /**
     * Redirect to Google OAuth authorization page
     */
    public function auth()
    {
        try {
            $service = new GoogleDriveService();
            $authUrl = $service->getAuthUrl();
            return redirect($authUrl);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to initialize Google Drive authorization: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback and save token
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            Log::error('Google Drive OAuth error: ' . $error);
            return redirect('/admin')->with('error', 'Google Drive authorization failed: ' . $error);
        }

        if (!$code) {
            return redirect('/admin')->with('error', 'Authorization failed. No authorization code received.');
        }

        try {
            $service = new GoogleDriveService();
            if ($service->handleCallback($code)) {
                return redirect('/admin')->with('success', 'Google Drive authorization successful! You can now upload files to Google Drive.');
            }

            return redirect('/admin')->with('error', 'Authorization failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('Google Drive callback error: ' . $e->getMessage());
            return redirect('/admin')->with('error', 'Authorization failed: ' . $e->getMessage());
        }
    }
}


