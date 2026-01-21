<?php

namespace App\Http\Requests;

use App\Models\InternationalStudent;
use App\Models\LocalStudent;
use App\Helpers\MalaysianICHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'phone_number' => ['required', 'string'],
            'address' => ['nullable', 'string'],

            'identity_type' => ['required', 'in:ic,passport'],
            'identity_value' => [
                'required', 
                'string',
                function ($attribute, $value, $fail) {
                    // Validate IC format if identity_type is 'ic'
                    if ($this->input('identity_type') === 'ic') {
                        if (!MalaysianICHelper::validate($value)) {
                            $fail('The IC number format is invalid. Please use format: YYMMDD-PB-XXXG or 12 digits.');
                        }
                    }
                },
            ],
            'state' => ['required', 'string'],

            // Custom validation for Unique Matric Number
            'matric_number' => [
                'nullable', 
                'string',
                function ($attribute, $value, $fail) {
                    // Get current authenticated user and their customerID (if exists)
                    $user = Auth::user();
                    $currentCustomerID = $user->customer ? $user->customer->customerID : null;

                    // 1. Check LocalStudent table
                    $existsInLocal = LocalStudent::where('matric_number', $value)
                        ->when($currentCustomerID, function ($query) use ($currentCustomerID) {
                            // Exclude the current user's own record
                            return $query->where('customerID', '!=', $currentCustomerID);
                        })
                        ->exists();

                    // 2. Check InternationalStudent table
                    $existsInInternational = InternationalStudent::where('matric_number', $value)
                        ->when($currentCustomerID, function ($query) use ($currentCustomerID) {
                            // Exclude the current user's own record
                            return $query->where('customerID', '!=', $currentCustomerID);
                        })
                        ->exists();

                    if ($existsInLocal || $existsInInternational) {
                        $fail('The matric number has already been used by another student.');
                    }
                },
            ],

            'college' => ['nullable', 'string'],
            'faculty' => ['nullable', 'string'],
            'program' => ['nullable', 'string'],

            'customer_license' => ['required', 'date'],
            'emergency_contact_number' => ['required', 'string'],
            'emergency_relationship' => ['required', 'string'],

            'bank_name' => ['required', 'string'],
            'bank_account_number' => ['required', 'string'],

            'file_identity' => ['nullable', 'file'],
            'file_matric' => ['nullable', 'file'],
            'file_license' => ['nullable', 'file'],
        ];
    }
}