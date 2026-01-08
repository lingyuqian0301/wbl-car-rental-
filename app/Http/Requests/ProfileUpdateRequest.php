<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->userID, 'userID'),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            
            // Identity Logic
            'identity_type' => ['required', 'in:ic,passport'],
            'identity_value' => ['nullable', 'string', 'max:20'], 
            
            'customer_license' => ['nullable', 'string', 'max:50'],
            'matric_number' => ['nullable', 'string', 'max:50'],
            'college' => ['nullable', 'string', 'max:255'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'state' => ['nullable', 'string', 'max:100'],

            // Emergency Contact (Combined fields in UI)
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
            'emergency_relationship' => ['nullable', 'string', 'in:Mother,Father,Relative,Other'],

            // Bank Details
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],

            // File Uploads
            'file_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'file_matric' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'file_identity' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}