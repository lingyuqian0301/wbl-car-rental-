<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Local;
use App\Models\LocalStudent;
use App\Models\StudentDetails;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // 1. Validation - Removed 'name'
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            DB::beginTransaction();

            // 2. Create User Account - Removed 'name' assignment
            $user = User::create([
                'username' => $request->email,
                // 'name' => $request->name, // REMOVED
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dateRegistered' => now(),
                'isActive' => true,
            ]);

            // 3. Create Customer Profile
            // Removed 'fullname' assignment
            $customer = Customer::create([
                'userID' => $user->getKey(), 
                // 'fullname' => $request->name, // REMOVED
                'phone_number' => '',
                'address' => '',
                'customer_license' => '',
                'emergency_contact' => '',
            ]);

            // 4. Create Wallet
            \App\Models\WalletAccount::create([
                'customerID'         => $customer->customerID,
                'wallet_balance'     => 0.00,
                'outstanding_amount' => 0.00,
                'wallet_status'      => 'Active',
                'wallet_lastUpdate_Date_Time' => now()
            ]);

            DB::commit();

            // 5. Final Steps
            event(new Registered($user));
            Auth::login($user);

            return redirect('/');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration error: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ])->withInput();
        }
    }
}