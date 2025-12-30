<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Calculate deposit amount based on booking duration.
     *
     * @param Booking $booking
     * @return float
     */
    public function calculateDeposit(Booking $booking): float
    {
        $numberOfDays = $booking->getNumberOfDays();

        // Short Term (< 15 Days): Fixed Deposit = RM 50.00
        if ($numberOfDays < 15) {
            return 50.00;
        }

        // Long Term (≥ 15 Days): Deposit = 100% of the Rental Price
        return (float) ($booking->total_amount ?? $booking->total_price ?? 0);
    }

    /**
     * Process "Keep Deposit" feature when booking is completed.
     *
     * @param Booking $booking
     * @return bool
     */
    public function processKeepDeposit(Booking $booking): bool
    {
        if (!$booking->keep_deposit) {
            return false;
        }

        // Find the deposit payment for this booking
        $depositPayment = Payment::where('bookingID', $booking->bookingID)
            ->where('payment_purpose', 'booking_deposit')
            ->where('status', 'Verified')
            ->where('deposit_returned', false)
            ->first();

        if (!$depositPayment) {
            Log::warning("No deposit payment found for booking {$booking->id}");
            return false;
        }

        // Check if there are any penalties (you can add penalty logic here)
        $hasPenalties = false; // TODO: Implement penalty check

        if ($hasPenalties) {
            // If there are penalties, don't transfer to wallet
            return false;
        }

        // Get or create wallet account for the user
        // Try to find by user_id first, then customerID
        $walletAccount = null;
        if ($booking->user) {
            $walletAccount = WalletAccount::where('user_id', $booking->user->id)->first();
        }
        if (!$walletAccount && $booking->customerID) {
            $walletAccount = WalletAccount::where('customerID', $booking->customerID)->first();
        }

        if (!$walletAccount) {
            $walletAccount = WalletAccount::create([
                'customerID' => $booking->customerID,
                'user_id' => $booking->user ? $booking->user->id : null,
                'virtual_balance' => 0.00,
                'available_balance' => 0.00,
                'hold_amount' => 0.00,
                'status' => 'active',
                'created_date' => now(),
            ]);
        }

        // Credit the deposit amount to wallet
        $walletAccount->credit(
            $depositPayment->amount,
            "Deposit refund from booking #{$booking->id}",
            'booking',
            $booking->id
        );

        // Mark deposit as processed (but not returned to bank)
        $depositPayment->update([
            'deposit_returned' => false, // Keep as false since it's in wallet
        ]);

        Log::info("Deposit transferred to wallet for booking {$booking->id}");

        return true;
    }

    /**
     * Check if user can skip deposit payment using wallet balance.
     *
     * @param int $userId
     * @param float $requiredDeposit
     * @return bool
     */
    public function canSkipDepositWithWallet(int $userId, float $requiredDeposit): bool
    {
        $walletAccount = WalletAccount::where('user_id', $userId)->first();

        if (!$walletAccount) {
            return false;
        }

        return $walletAccount->available_balance >= $requiredDeposit;
    }

    /**
     * Use wallet balance to pay deposit.
     *
     * @param Booking $booking
     * @param float $depositAmount
     * @return Payment|null
     */
    public function payDepositFromWallet(Booking $booking, float $depositAmount): ?Payment
    {
        // Try to find wallet account by user_id or customerID
        $walletAccount = null;
        if ($booking->user) {
            $walletAccount = WalletAccount::where('user_id', $booking->user->id)->first();
        }
        if (!$walletAccount && $booking->customerID) {
            $walletAccount = WalletAccount::where('customerID', $booking->customerID)->first();
        }

        if (!$walletAccount) {
            return null; // No wallet account found
        }

        if ($walletAccount->available_balance < $depositAmount) {
            return null;
        }

        // Debit from wallet
        $walletAccount->debit(
            $depositAmount,
            "Deposit payment for booking #{$booking->id}",
            'booking',
            $booking->id
        );

        // Create payment record
        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $depositAmount,
            'payment_type' => $request->input('payment_type', 'Deposit'),
            'payment_purpose' => 'booking_deposit',
            'status' => 'Verified', // Auto-verified since it's from wallet
            'receiptURL' => null,
            'deposit_returned' => false,
            'keep_deposit' => false,
            'payment_date' => now(),
            'isPayment_complete' => true,
        ]);

        return $payment;
    }
}

