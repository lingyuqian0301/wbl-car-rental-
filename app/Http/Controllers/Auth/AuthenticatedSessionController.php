<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // Store the redirect URL if provided as query parameter
        if ($request->has('redirect')) {
            $request->session()->put('url.intended', $request->get('redirect'));
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Authenticate the user
        $request->authenticate();

        // 2. Regenerate session ID (Security standard)
        $request->session()->regenerate();

        // 3. Get the intended URL (previous page before login)
        $intendedUrl = $request->session()->pull('url.intended', null);

        // 4. Check if the authenticated user is an Admin
        if ($request->user()->isAdmin()) {
            // If there's an intended URL and it's not the admin dashboard, redirect there
            if ($intendedUrl && !str_contains($intendedUrl, '/admin/dashboard')) {
                return redirect($intendedUrl);
            }
            // Otherwise redirect to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // 5. If not Admin, redirect to intended URL or standard dashboard
        if ($intendedUrl) {
            return redirect($intendedUrl);
        }
        
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
