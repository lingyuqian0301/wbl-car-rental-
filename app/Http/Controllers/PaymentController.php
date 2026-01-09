<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Notification; // Ensure this is imported
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function create(Request $request, Booking $booking): View
    {
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();

        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            abort(403, 'UNAUTHORIZED ACCESS TO THIS BOOKING.');
        }

        $booking->load(['vehicle']);

        $depositAmount = $this->paymentService->calculateDeposit($booking);
        $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet(Auth::user()->userID, $depositAmount);

        $walletBalance = 0;
        if($currentCustomer && $currentCustomer->walletAccount) {
            $walletBalance = $currentCustomer->walletAccount->wallet_balance ?? 0;
        }

        return view('payments.create', [
            'booking' => $booking,
            'depositAmount' => $depositAmount,
            'canSkipDeposit' => $canSkipDeposit,
            'walletBalance' => $walletBalance,
        ]);
    }

    /**
     * MAIN FUNCTION: Submit Manual Payment (Bank Transfer / QR)
     */
    public function submitPayment(Request $request): RedirectResponse
    {
        // 1. Validate Inputs
        $validator = Validator::make($request->all(), [
            'bookingID'             => 'required|exists:booking,bookingID',
            'receipt_image'         => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'bank_name'             => 'required|string|max:100',
            'bank_account_number'   => 'required|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
            'amount'                => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $booking = Booking::findOrFail($request->bookingID);

        // 2. Security Check
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();
        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // 3. File Upload
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('receipts', $fileName, 'public');
        }

        // 4. Create Payment Record
        $payment = Payment::create([
            'bookingID'               => $booking->bookingID,
            'total_amount'            => $request->amount,
            'payment_bank_name'       => $request->bank_name,
            'payment_bank_account_no' => $request->bank_account_number,
            'transaction_reference'   => $request->transaction_reference,
            'payment_status'          => 'Pending',
            'payment_date'            => now(),
            'isPayment_complete'      => false,
            'payment_isVerify'        => false,
            'latest_Update_Date_Time' => now(),
        ]);

        // 5. Update Booking
        $booking->update([
            'booking_status' => 'Pending Payment Verification',
            'keep_deposit'   => $request->boolean('keep_deposit', false),
        ]);

        // 6. Create Admin Notification for New Payment
        try {
            \App\Models\AdminNotification::create([
                'type' => 'new_payment',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => Auth::id(),
                'booking_id' => $booking->bookingID,
                'payment_id' => $payment->paymentID ?? null,
                'message' => "New payment uploaded for Booking #{$booking->bookingID}. Amount: RM " . number_format($request->amount, 2),
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'amount' => $request->amount,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Notification Error (Ignored): ' . $e->getMessage());
        }

        // 7. Redirect to Agreement Step with proper defensive checks
        // Try to get the booking ID using the correct column name
        $bookingId = $booking->bookingID ?? $booking->id ?? null;
        
        if ($bookingId) {
            return redirect()
                ->route('agreement.show', $bookingId)
                ->with('success', 'Payment submitted successfully! Please review and accept the rental agreement.');
        } else {
            // Fallback if booking ID cannot be determined
            Log::error('Unable to determine booking ID after payment submission');
            return redirect()->back()->with('error', 'Payment recorded but unable to proceed. Please try again.');
        }
    }

    public function payWithWallet(Request $request, Booking $booking)
    {
        $user = Auth::user();
        $currentCustomer = \App\Models\Customer::where('userID', $user->userID)->first();

        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $depositAmount = $this->paymentService->calculateDeposit($booking);
        $amountToPay = $depositAmount;
        
        $wallet = $currentCustomer->walletAccount;

        if (!$wallet || $wallet->wallet_balance < $amountToPay) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        // DB Transaction for safety
        DB::beginTransaction();
        try {
            // 1. Deduct from Wallet
            $wallet->wallet_balance -= $amountToPay;
            // Also reduce outstanding amount as payment is made
            $wallet->outstanding_amount = max(0, $wallet->outstanding_amount - $amountToPay);
            $wallet->wallet_lastUpdate_Date_Time = now();
            $wallet->save();

            // 2. Create Payment Record
            // Check if this payment completes the full rental amount
            $isFullPayment = $amountToPay >= ($booking->rental_amount - 0.01);

            $payment = Payment::create([
                'bookingID'               => $booking->bookingID,
                'total_amount'            => $amountToPay,
                'payment_bank_name'       => 'My Wallet',
                'payment_bank_account_no' => 'WALLET-' . $currentCustomer->customerID,
                'transaction_reference'   => 'WAL-' . time() . '-' . $booking->bookingID,
                'payment_status'          => 'Verified', // Auto-verified
                'payment_date'            => now(),
                'isPayment_complete'      => $isFullPayment,
                'payment_isVerify'        => true,
                'latest_Update_Date_Time' => now(),
            ]);

            // 3. Create Wallet Transaction
            \App\Models\WalletTransaction::create([
                'walletAccountID'  => $wallet->walletAccountID,
                'paymentID'        => $payment->paymentID,
                'transaction_type' => 'Payment',
                'amount'           => $amountToPay,
                'transaction_date' => now(),
                'description'      => 'Payment for Booking #' . $booking->bookingID,
                'reference_type'   => 'Booking',
                'reference_id'     => $booking->bookingID,
            ]);

            // 4. Update Booking
            $booking->update(['booking_status' => 'Confirmed']);

            // 5. Generate Invoice
            \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                    'issue_date'     => now(),
                    'totalAmount'    => $booking->total_amount ?? $booking->rental_amount,
                ]
            );

            DB::commit();

            // Redirect to Agreement Step with proper defensive checks
            // Try to get the booking ID using the correct column name
            $bookingId = $booking->bookingID ?? $booking->id ?? null;
            
            if ($bookingId) {
                return redirect()->route('agreement.show', $bookingId)
                    ->with('success', 'Deposit paid successfully from wallet! Please review and accept the rental agreement.');
            } else {
                // Fallback if booking ID cannot be determined
                Log::error('Unable to determine booking ID after wallet payment');
                return redirect()->back()->with('error', 'Payment completed but unable to proceed. Please try again.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Wallet Payment Failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Payment failed. Please try again.');
        }
    }
}
