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

        // 3. Check if the authenticated user is a Runner - redirect directly to runner dashboard
        // (Check runner first because runners are also staff members)
        if ($request->user()->isRunner()) {
            return redirect()->route('runner.dashboard');
        }

        // 4. Check if the authenticated user is StaffIT - redirect directly to staffit dashboard
        if ($request->user()->isStaffIT()) {
            return redirect()->route('staffit.dashboard');
        }

        // 5. Check if the authenticated user is an Admin - redirect directly to admin dashboard
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // 6. Check if the authenticated user is a Staff member - redirect directly to staff dashboard
        if ($request->user()->isStaff()) {
            return redirect()->route('staff.dashboard');
        }

        // 5. If not Admin or Staff, redirect to intended URL or customer dashboard
        $intendedUrl = $request->session()->pull('url.intended', null);
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

