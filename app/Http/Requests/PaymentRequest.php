<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'payment_date' => 'required|date|before_or_equal:today',
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'payment_method' => 'required|in:Bank Transfer,Cash',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_id.required' => 'Booking ID is required.',
            'booking_id.exists' => 'The selected booking does not exist.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'proof_of_payment.required' => 'Proof of payment is required.',
            'proof_of_payment.file' => 'Proof of payment must be a file.',
            'proof_of_payment.mimes' => 'Invalid file format. Please upload an image (JPG/PNG) or PDF file.', // US017: Clear error message
            'proof_of_payment.max' => 'Proof of payment must not exceed 2MB.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be either Bank Transfer or Cash.',
        ];
    }
}
