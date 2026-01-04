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
        return (float) ($booking->rental_amount ?? 0);
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
            ->where('payment_status', 'Verified')
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

        // Get or create wallet account for the customer
        $walletAccount = WalletAccount::where('customerID', $booking->customerID)->first();

        if (!$walletAccount) {
            $walletAccount = WalletAccount::create([
                'customerID' => $booking->customerID,
                'wallet_balance' => 0.00,
                'outstanding_amount' => 0.00,
                'wallet_status' => 'Active',
                'wallet_lastUpdate_Date_Time' => now(),
            ]);
        }

        // Credit the deposit amount to wallet
        $walletAccount->wallet_balance = ($walletAccount->wallet_balance ?? 0) + $depositPayment->total_amount;
        $walletAccount->wallet_lastUpdate_Date_Time = now();
        $walletAccount->save();

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
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->customer) {
            return false;
        }
        
        $walletAccount = $user->customer->walletAccount;

        if (!$walletAccount) {
            return false;
        }

        return ($walletAccount->wallet_balance ?? 0) >= $requiredDeposit;
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
        // Find wallet account by customerID
        $walletAccount = WalletAccount::where('customerID', $booking->customerID)->first();

        if (!$walletAccount) {
            return null; // No wallet account found
        }

        if (($walletAccount->wallet_balance ?? 0) < $depositAmount) {
            return null;
        }

        // Debit from wallet
        $walletAccount->wallet_balance = ($walletAccount->wallet_balance ?? 0) - $depositAmount;
        $walletAccount->wallet_lastUpdate_Date_Time = now();
        $walletAccount->save();

        // Create payment record
        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'total_amount' => $depositAmount,
            'payment_bank_name' => 'Wallet',
            'payment_bank_account_no' => '',
            'payment_status' => 'Verified', // Auto-verified since it's from wallet
            'payment_date' => now(),
            'isPayment_complete' => true,
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        return $payment;
    }
}

