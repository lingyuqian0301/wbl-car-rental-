<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'matric_number' => ['nullable', 'string', 'max:50'],
            'identification_card' => ['nullable', 'string', 'max:20'],
            'customer_license' => ['nullable', 'string', 'max:50'],
            'college' => ['nullable', 'string', 'max:255'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'state' => ['nullable', 'string', 'max:100'],
        ];
    }
}
