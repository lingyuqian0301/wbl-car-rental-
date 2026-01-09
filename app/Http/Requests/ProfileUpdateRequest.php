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

        'phone_number' => ['required', 'string'],
        'address' => ['nullable', 'string'],

        'identity_type' => ['required', 'in:ic,passport'],
        'identity_value' => ['required', 'string'],
        'state' => ['required', 'string'],

        'matric_number' => ['nullable', 'string'],
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