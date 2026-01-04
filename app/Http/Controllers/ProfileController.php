<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use App\Models\Local;
use App\Models\StudentDetails;
use App\Models\LocalStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        
        // Get related data for display
        $profileData = [
            'phone_number' => $customer->phone_number ?? '',
            'customer_license' => $customer->customer_license ?? '',
            'address' => $customer->address ?? '',
            'matric_number' => '',
            'identification_card' => '',
            'college' => '',
            'faculty' => '',
            'program' => '',
            'state' => '',
        ];
        
        if ($customer) {
            // Check if customer is Local
            $local = Local::where('customerID', $customer->customerID)->first();
            if ($local) {
                $profileData['identification_card'] = $local->ic_no ?? '';
                $profileData['state'] = $local->stateOfOrigin ?? '';
                
                // Check if LocalStudent
                $localStudent = LocalStudent::where('customerID', $customer->customerID)->first();
                if ($localStudent && $localStudent->matric_number) {
                    $studentDetails = StudentDetails::where('matric_number', $localStudent->matric_number)->first();
                    if ($studentDetails) {
                        $profileData['matric_number'] = $studentDetails->matric_number ?? '';
                        $profileData['college'] = $studentDetails->college ?? '';
                        $profileData['faculty'] = $studentDetails->faculty ?? '';
                        $profileData['program'] = $studentDetails->programme ?? '';
                    }
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
            
            // Update basic user info
            $user->fill($request->only(['name', 'email']));
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
            
            // Get or create Customer record
            $customer = Customer::where('userID', $user->userID)->first();
            if ($customer) {
                // Update customer fields
                $customer->phone_number = $request->input('phone_number');
                $customer->customer_license = $request->input('customer_license');
                $customer->address = $request->input('address');
                $customer->save();
                
                // Get or create Local record
                $local = Local::where('customerID', $customer->customerID)->first();
                if (!$local && $request->filled('identification_card')) {
                    $local = new Local();
                    $local->customerID = $customer->customerID;
                }
                
                if ($local) {
                    $local->ic_no = $request->input('identification_card');
                    $local->stateOfOrigin = $request->input('state');
                    $local->save();
                    
                    // Handle StudentDetails if matric_number is provided
                    if ($request->filled('matric_number')) {
                        // Create or update StudentDetails
                        $studentDetails = StudentDetails::firstOrNew(['matric_number' => $request->input('matric_number')]);
                        $studentDetails->college = $request->input('college');
                        $studentDetails->faculty = $request->input('faculty');
                        $studentDetails->programme = $request->input('program');
                        $studentDetails->save();
                        
                        // Create LocalStudent link if not exists
                        $localStudent = LocalStudent::where('customerID', $customer->customerID)->first();
                        if (!$localStudent) {
                            $localStudent = new LocalStudent();
                            $localStudent->customerID = $customer->customerID;
                        }
                        $localStudent->matric_number = $request->input('matric_number');
                        $localStudent->save();
                    }
                }
            }
            
            DB::commit();
            
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update error: ' . $e->getMessage());
            return Redirect::route('profile.edit')->with('error', 'Failed to update profile. Please try again.');
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
