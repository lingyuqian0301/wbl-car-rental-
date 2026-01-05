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
        // 1. Validation
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'ic_no' => ['required', 'string', 'max:20', 'unique:PersonDetails,ic_no'], // Validates IC is unique
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'matric_number' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            DB::beginTransaction();

            // 2. Create User Account
            $user = User::create([
                'username' => $request->email,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dateRegistered' => now(),
                'isActive' => true,
            ]);

            // 3. Create Customer Profile
            // We use $user->getKey() to automatically get the correct ID (id or userID)
            $customer = Customer::create([
                'userID' => $user->getKey(), 
                'fullname' => $request->name,
                'phone_number' => '',
                'address' => '',
                'customer_license' => '',
                'emergency_contact' => '',
            ]);

            // 4. Create PersonDetails FIRST (Fixes Foreign Key Error)
            \App\Models\PersonDetails::create([
                'ic_no' => $request->ic_no,
                'fullname' => $request->name,
            ]);

            // 5. Create Local record using the IC No
            $local = Local::create([
                'customerID' => $customer->customerID,
                'ic_no' => $request->ic_no,
                'stateOfOrigin' => '',
            ]);

            // 6. If matric_number is provided, create StudentDetails and LocalStudent
            if ($request->filled('matric_number')) {
                // Find faculty from program code
                $faculty = '';
                $program = $request->input('program', '');
                foreach (config('utm.faculties', []) as $facultyCode => $facultyData) {
                    if (in_array($program, $facultyData['programs'] ?? [])) {
                        $faculty = $facultyCode;
                        break;
                    }
                }

                // Create or update StudentDetails
                $studentDetails = StudentDetails::firstOrNew(['matric_number' => $request->matric_number]);
                $studentDetails->college = '';
                $studentDetails->faculty = $faculty;
                $studentDetails->programme = $program;
                $studentDetails->save();

                // Create LocalStudent link
                LocalStudent::create([
                    'customerID' => $customer->customerID,
                    'matric_number' => $request->matric_number,
                ]);
            }

            // 7. Create Wallet
            \App\Models\WalletAccount::create([
                'customerID'         => $customer->customerID,
                'wallet_balance'     => 0.00,
                'outstanding_amount' => 0.00,
                'wallet_status'      => 'Active',
                'wallet_lastUpdate_Date_Time' => now()
            ]);

            DB::commit();

            // 8. Final Steps
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