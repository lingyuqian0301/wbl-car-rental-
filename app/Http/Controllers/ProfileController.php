<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use App\Models\Local;
use App\Models\International;
use App\Models\StudentDetails;
use App\Models\LocalStudent;
use App\Models\InternationalStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $customer = Customer::where('userID', $user->userID)->first();
        
        // Initialize profileData with all fields
        $profileData = [
            'phone_number' => $customer?->phone_number,
            'customer_license' => $customer?->customer_license,
            'address' => $customer?->address,
            'emergency_contact_number' => $customer?->emergency_contact,
            'bank_name' => $customer?->default_bank_name,
            'bank_account_number' => $customer?->default_account_no,
            'identity_type' => null,
            'identity_value' => null,
            'matric_number' => null,
            'college' => null,
            'faculty' => null,
            'program' => null,
            'state' => null,
        ];

        if ($customer) {
            // ===== IDENTITY (LOCAL / INTERNATIONAL) =====
            $local = Local::where('customerID', $customer->customerID)->first();
            $international = International::where('customerID', $customer->customerID)->first();

            if ($local) {
                $profileData['identity_type'] = 'ic';
                $profileData['identity_value'] = $local->ic_no;
                $profileData['state'] = $local->stateOfOrigin;
            } elseif ($international) {
                $profileData['identity_type'] = 'passport';
                $profileData['identity_value'] = $international->passport_no;
                $profileData['state'] = $international->countryOfOrigin;
            }

            // ===== STUDENT (LOCAL / INTERNATIONAL) =====
            $localStudent = LocalStudent::where('customerID', $customer->customerID)->first();
            $intlStudent = InternationalStudent::where('customerID', $customer->customerID)->first();

            $matricNumber = ($localStudent ? $localStudent->matric_number : null)
                ?? ($intlStudent ? $intlStudent->matric_number : null)
                ?? null;

            if ($matricNumber) {
                $profileData['matric_number'] = $matricNumber;

                $studentDetails = StudentDetails::where('matric_number', $matricNumber)->first();
                if ($studentDetails) {
                    $profileData['college'] = $studentDetails->college;
                    $profileData['faculty'] = $studentDetails->faculty;
                    $profileData['program'] = $studentDetails->programme;
                }
            }
        }
 
        return view('profile.edit', [
            'user' => $user,
            'profileData' => $profileData,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        
        try {
            \Log::info('Profile update request received', $request->all());
            
            DB::beginTransaction();
            
            $user = $request->user();
            \Log::info("Updating profile for user: {$user->userID}");
            
            // ===== PART A: UPDATE USER TABLE =====
            $user->update([
                'name' => $request->name,
            ]);
            \Log::info("User updated: {$user->userID}");
            
            // ===== PART B: UPDATE CUSTOMER TABLE (ONLY VALID CUSTOMER COLUMNS) =====
            $customerData = [
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'customer_license' => $request->customer_license,
                'emergency_contact' => $request->emergency_contact_number,
                'default_bank_name' => $request->bank_name,
                'default_account_no' => $request->bank_account_number,
            ];
            
            \Log::info("Customer data to save", $customerData);
            
            $customer = Customer::updateOrCreate(
                ['userID' => $user->userID],
                $customerData
            );
            
            \Log::info("Customer record updated/created: {$customer->customerID}");
            
            // ===== PART C: HANDLE IDENTITY RECORDS (LOCAL / INTERNATIONAL) =====
     // ===== PART C: HANDLE IDENTITY RECORDS (LOCAL / INTERNATIONAL) =====
$identityType = $request->identity_type ?? 'ic';
$identityValue = $request->identity_value;
$state = $request->state;

if ($identityType === 'ic') {

    /**
     * 🔥 CRITICAL FIX
     * PersonDetails MUST exist before Local
     * because Local.ic_no is a FOREIGN KEY
     */
    DB::table('persondetails')->updateOrInsert(
        ['ic_no' => $identityValue],
        ['fullname' => $request->name]
    );

    // Now it is SAFE to write to Local
    Local::updateOrCreate(
        ['customerID' => $customer->customerID],
        [
            'ic_no' => $identityValue,
            'stateOfOrigin' => $state,
        ]
    );

    // Clean up International + student records
    International::where('customerID', $customer->customerID)->delete();
    InternationalStudent::where('customerID', $customer->customerID)->delete();

} else {

    // Passport users (NO PersonDetails relation)
    International::updateOrCreate(
        ['customerID' => $customer->customerID],
        [
            'passport_no' => $identityValue,
            'countryOfOrigin' => $state,
        ]
    );

    Local::where('customerID', $customer->customerID)->delete();
    LocalStudent::where('customerID', $customer->customerID)->delete();
}


            if ($identityType === 'ic') {
                // Create/update Local identity
                Local::updateOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'ic_no' => $identityValue,
                        'stateOfOrigin' => $state,
                    ]
                );
                
                \Log::info("Local identity created/updated for customer: {$customer->customerID}");
                
                // Clean up International and related student records if switching
                International::where('customerID', $customer->customerID)->delete();
                InternationalStudent::where('customerID', $customer->customerID)->delete();
            } else {
                // Create/update International identity
                International::updateOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'passport_no' => $identityValue,
                        'countryOfOrigin' => $state,
                    ]
                );
                
                \Log::info("International identity created/updated for customer: {$customer->customerID}");
                
                // Clean up Local and related student records if switching
                Local::where('customerID', $customer->customerID)->delete();
                LocalStudent::where('customerID', $customer->customerID)->delete();
            }
            
            // ===== PART D: HANDLE STUDENT INFORMATION =====
            if ($request->filled('matric_number')) {
                \Log::info("Processing student information for matric: {$request->matric_number}");
                
                // Create/update StudentDetails
                StudentDetails::updateOrCreate(
                    ['matric_number' => $request->matric_number],
                    [
                        'college' => $request->college,
                        'faculty' => $request->faculty,
                        'programme' => $request->program,
                    ]
                );
                
                \Log::info("StudentDetails created/updated: {$request->matric_number}");
                
                // Link student to identity type
                if ($identityType === 'ic') {
                    LocalStudent::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        ['matric_number' => $request->matric_number]
                    );
                    \Log::info("LocalStudent linked for customer: {$customer->customerID}");
                    
                    // Clean up international student if switching
                    InternationalStudent::where('customerID', $customer->customerID)->delete();
                } else {
                    InternationalStudent::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        ['matric_number' => $request->matric_number]
                    );
                    \Log::info("InternationalStudent linked for customer: {$customer->customerID}");
                    
                    // Clean up local student if switching
                    LocalStudent::where('customerID', $customer->customerID)->delete();
                }
            } else {
                \Log::info("No matric number provided, removing student links");
                
                // Remove student links if matric_number is empty
                LocalStudent::where('customerID', $customer->customerID)->delete();
                InternationalStudent::where('customerID', $customer->customerID)->delete();
            }
            
            DB::commit();
            \Log::info("Profile update transaction committed successfully");
            
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Profile update error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return Redirect::route('profile.edit')->with('error', 'Failed to update profile. Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}