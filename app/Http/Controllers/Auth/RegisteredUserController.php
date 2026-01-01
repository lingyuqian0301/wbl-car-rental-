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
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // All new registrations are customers by default
        ]);

        // 3. Create Customer Profile
        $customer = Customer::create([
            'user_id' => $user->id,
            'fullname' => $user->name,
            'email' => $user->email,
            'registration_date' => now(),
            'customer_type' => 'regular',
        ]);

        // ---------------------------------------------------------
        // 4. CREATE WALLET IMMEDIATELY (New Addition)
        // ---------------------------------------------------------
        // This guarantees every new user has a wallet from Day 1.
        \Illuminate\Support\Facades\DB::table('walletaccount')->insert([
            'customerID'         => $customer->customerID, // Linked to the new customer
            'user_id'            => $user->id,            // Linked to the user login
            'available_balance'  => 0.00,
            'outstanding_amount' => 0.00,                 // Starts with 0 debt
            'wallet_status'      => 'Active',
            'last_update_datetime' => now()
        ]);
        // ---------------------------------------------------------

        // 5. Final Steps
        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }
}
