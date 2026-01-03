<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   
   
   public function store(Request $request): RedirectResponse
    {
        // 1. Validation
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Create User Account
        $user = User::create([
            'username' => $request->email, // Use email as username for now
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dateRegistered' => now(),
            'isActive' => true,
        ]);

        // 3. Create Customer Profile
        $customer = Customer::create([
            'userID' => $user->userID,
            'phone_number' => $request->phone ?? '',
            'address' => '',
            'customer_license' => '',
            'emergency_contact' => '',
            'booking_times' => 0,
        ]);

        // ---------------------------------------------------------
        // 4. CREATE WALLET IMMEDIATELY (New Addition)
        // ---------------------------------------------------------
        // This guarantees every new user has a wallet from Day 1.
        \App\Models\WalletAccount::create([
            'customerID'         => $customer->customerID,
            'wallet_balance'     => 0.00,
            'outstanding_amount' => 0.00,
            'wallet_status'      => 'Active',
            'wallet_lastUpdate_Date_Time' => now()
        ]);
        // ---------------------------------------------------------

        // 5. Final Steps
        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }
}
