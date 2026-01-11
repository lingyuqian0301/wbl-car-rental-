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
use Illuminate\Support\Facades\File;
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
            'license_img' => $customer?->customer_license_img,
            'ic_img' => null,
            'passport_img' => null,
            'matric_img' => null,
        ];

        if ($customer) {
            // ===== IDENTITY =====
            $local = Local::where('customerID', $customer->customerID)->first();
            $international = International::where('customerID', $customer->customerID)->first();

            if ($local) {
                $profileData['identity_type'] = 'ic';
                $profileData['identity_value'] = $local->ic_no;
                $profileData['state'] = $local->stateOfOrigin;
                $profileData['ic_img'] = $local->ic_img;
            } elseif ($international) {
                $profileData['identity_type'] = 'passport';
                $profileData['identity_value'] = $international->passport_no;
                $profileData['state'] = $international->countryOfOrigin;
                $profileData['passport_img'] = $international->passport_img;
            }

            // ===== STUDENT =====
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
                    
                    // Access the new column name if it exists
                    $profileData['matric_img'] = $studentDetails->matric_card_img ?? null; 
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
            DB::beginTransaction();

            $user = $request->user();

            // 1. Update User Name
            $user->update(['name' => $request->name]);

            // 2. Prepare Customer Data
            $customerData = [
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'customer_license' => $request->customer_license,
                'emergency_contact' => $request->emergency_contact_number,
                'default_bank_name' => $request->bank_name,
                'default_account_no' => $request->bank_account_number,
            ];

            // 📂 LICENSE IMAGE
            if ($request->hasFile('file_license')) {
                $customerData['customer_license_img'] = $this->handleImageUpload(
                    $request->file('file_license'), 
                    'licenses'
                );
            }

            $customer = Customer::updateOrCreate(
                ['userID' => $user->userID],
                $customerData
            );

            // 📂 IDENTITY IMAGE
            $identityImgPath = null;
            if ($request->hasFile('file_identity')) {
                $identityImgPath = $this->handleImageUpload(
                    $request->file('file_identity'), 
                    'identity_docs'
                );
            }

            // 3. Handle Identity
            $identityType = $request->identity_type ?? 'ic';
            $identityValue = $request->identity_value;
            $state = $request->state;

            if ($identityValue) {
                if ($identityType === 'ic') {
                    // Update PersonDetails
                    DB::table('persondetails')->updateOrInsert(
                        ['ic_no' => $identityValue],
                        ['fullname' => $request->name]
                    );

                    $localData = [
                        'ic_no' => $identityValue,
                        'stateOfOrigin' => $state,
                    ];
                    
                    if ($identityImgPath) {
                        $localData['ic_img'] = $identityImgPath;
                    }

                    // Handle existing record without image overwrite
                    if (!$identityImgPath && !Local::where('customerID', $customer->customerID)->exists()) {
                         $localData['ic_img'] = ''; 
                    }

                    Local::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        $localData
                    );

                    // Cleanup International
                    International::where('customerID', $customer->customerID)->delete();
                    InternationalStudent::where('customerID', $customer->customerID)->delete();

                } else {
                    // Passport Logic
                    $passportData = [
                        'passport_no' => $identityValue,
                        'countryOfOrigin' => $state,
                    ];

                    if ($identityImgPath) {
                        $passportData['passport_img'] = $identityImgPath;
                    }

                    International::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        $passportData
                    );

                    // Cleanup Local
                    Local::where('customerID', $customer->customerID)->delete();
                    LocalStudent::where('customerID', $customer->customerID)->delete();
                }
            }

            // 4. Handle Student Info
            if ($request->filled('matric_number')) {
                
                $studentData = [
                    'college' => $request->college,
                    'faculty' => $request->faculty,
                    'programme' => $request->program,
                ];

                // 📂 MATRIC IMAGE
                if ($request->hasFile('file_matric')) {
                    // Save to database column 'matric_card_img'
                    $studentData['matric_card_img'] = $this->handleImageUpload(
                        $request->file('file_matric'), 
                        'matric_cards'
                    );
                }

                StudentDetails::updateOrCreate(
                    ['matric_number' => $request->matric_number],
                    $studentData
                );

                if ($identityType === 'ic') {
                    LocalStudent::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        ['matric_number' => $request->matric_number]
                    );
                    InternationalStudent::where('customerID', $customer->customerID)->delete();
                } else {
                    InternationalStudent::updateOrCreate(
                        ['customerID' => $customer->customerID],
                        ['matric_number' => $request->matric_number]
                    );
                    LocalStudent::where('customerID', $customer->customerID)->delete();
                }
            } else {
                LocalStudent::where('customerID', $customer->customerID)->delete();
                InternationalStudent::where('customerID', $customer->customerID)->delete();
            }

            DB::commit();
            return Redirect::route('profile.edit')->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed: ' . $e->getMessage());
            return Redirect::route('profile.edit')->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to handle image uploads to public/images directory
     */
    private function handleImageUpload($file, $folder)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = public_path("images/{$folder}");
        
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);

        // Store relative path in DB
        return "images/{$folder}/{$fileName}";
    }

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