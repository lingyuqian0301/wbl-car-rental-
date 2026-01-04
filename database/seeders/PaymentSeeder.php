<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {
            $paymentStatus = match ($booking->booking_status) {
                'Completed' => 'Verified',
                'Confirmed' => 'Verified',
                'Pending' => 'Pending',
                default => 'Pending',
            };

            $isComplete = $paymentStatus === 'Verified';
            $isVerify = $paymentStatus === 'Verified';

            Payment::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'bookingID' => $booking->bookingID,
                    'payment_bank_name' => collect(['Maybank', 'CIMB', 'RHB', 'Public Bank', 'Hong Leong'])->random(),
                    'payment_bank_account_no' => str_pad(rand(1000000000, 9999999999), 12, '0', STR_PAD_LEFT),
                    'payment_date' => $booking->lastUpdateDate ?? now(),
                    'total_amount' => $booking->deposit_amount + $booking->rental_amount,
                    'payment_status' => $paymentStatus,
                    'transaction_reference' => 'TXN' . strtoupper(uniqid()),
                    'isPayment_complete' => $isComplete,
                    'payment_isVerify' => $isVerify,
                    'latest_Update_Date_Time' => now(),
                ]
            );
        }
    }
}

