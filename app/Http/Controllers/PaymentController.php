<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Notification;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request, Booking $booking): View
    {
        // 1. Security Check
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();

        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            abort(403, 'UNAUTHORIZED ACCESS TO THIS BOOKING.');
        }

        $booking->load(['vehicle']);

        $depositAmount = $this->paymentService->calculateDeposit($booking);
        $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet(Auth::user()->userID, $depositAmount);

        $walletBalance = 0;
        $customer = Auth::user()->customer;
        if($customer && $customer->walletAccount) {
            $walletBalance = $customer->walletAccount->wallet_balance;
        }

        return view('payments.create', [
            'booking' => $booking,
            'depositAmount' => $depositAmount,
            'canSkipDeposit' => $canSkipDeposit,
            'walletBalance' => $walletBalance,
        ]);
    }

    public function showPaymentForm(int $bookingID): View
    {
        $booking = Booking::where('bookingID', $bookingID)->firstOrFail();

        // Security Check
        if ($booking->customerID !== \App\Models\Customer::where('userID', Auth::user()->userID)->value('customerID')) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $booking->load(['vehicle']);
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        return view('payment.create', [
            'booking' => $booking,
            'depositAmount' => $depositAmount,
        ]);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        // Security Check
        if ($booking->customerID !== \App\Models\Customer::where('userID', Auth::user()->userID)->value('customerID')) {
             abort(403, 'Unauthorized access to this booking.');
        }

        // --- FIX: DETERMINE CORRECT AMOUNT ---
        $depositAmount = $this->paymentService->calculateDeposit($booking);
        
        $finalAmount = $depositAmount; // Default to deposit
        if ($request->input('payment_type') === 'Full Payment') {
            $finalAmount = $booking->rental_amount;
        }
        // -------------------------------------

        $proofPath = null;
        $receiptURL = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $proofPath = $file->storeAs('receipts', $fileName, 'public');
            $receiptURL = Storage::url($proofPath);
        }

        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'total_amount' => $finalAmount,
            'payment_bank_name' => $request->input('bank_name', ''),
            'payment_bank_account_no' => $request->input('bank_account_number', ''),
            'payment_status' => 'Pending',
            'transaction_reference' => $request->input('transaction_reference', ''),
            'payment_date' => $request->payment_date ?? now(),
            'isPayment_complete' => false,
            'payment_isVerify' => false,
            'latest_Update_Date_Time' => now(),
        ]);

        $booking->update([
            'keep_deposit' => $request->boolean('keep_deposit', false),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Payment submitted successfully!');
    }

    /**
     * Submit payment with DuitNow QR receipt (API endpoint).
     */
    public function submitPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingID'             => 'required|exists:booking,bookingID',
            'receipt_image'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'bank_name'             => 'required|string|max:100', // Added Validation
            'bank_account_number'   => 'required|string|max:50',  // Added Validation
            'transaction_reference' => 'nullable|string|max:100', // Added Validation
            'keep_deposit'          => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $booking = Booking::findOrFail($request->bookingID);

        // Security Check
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();
        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized access to this booking.');
        }

        // --- Amount Logic ---
        $depositAmount = $this->paymentService->calculateDeposit($booking);
        $finalAmount = $request->input('payment_type') === 'Full Payment' ? $booking->rental_amount : $depositAmount;
        // --------------------

        $file = $request->file('receipt_image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $proofPath = $file->storeAs('receipts', $fileName, 'public');
        $receiptURL = Storage::url($proofPath);

        // Create payment record
        $payment = Payment::create([
            'bookingID'             => $booking->bookingID,
            'total_amount'          => $finalAmount,
            'payment_bank_name'     => $request->bank_name,
            'payment_bank_account_no' => $request->bank_account_number,
            'transaction_reference' => $request->transaction_reference, 
            'payment_status'        => 'Pending',
            'payment_date'          => now(),
            'isPayment_complete'    => false,
            'payment_isVerify'      => false,
            'latest_Update_Date_Time' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment submitted successfully!',
                'payment' => $payment,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Payment submitted successfully! Your payment is now awaiting verification.');
    }

    public function payWithWallet(Request $request, Booking $booking)
    {
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();
        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            if ($request->expectsJson()) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            abort(403, 'Unauthorized access to this booking.');
        }

        $depositAmount = $this->paymentService->calculateDeposit($booking);

        if (!$this->paymentService->canSkipDepositWithWallet(Auth::user()->userID, $depositAmount)) {
            if ($request->expectsJson()) return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.'], 400);
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        $payment = $this->paymentService->payDepositFromWallet($booking, $depositAmount);

        if (!$payment) {
            if ($request->expectsJson()) return response()->json(['success' => false, 'message' => 'Failed to process wallet payment.'], 500);
            return redirect()->back()->with('error', 'Failed to process wallet payment.');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Deposit paid successfully!', 'payment' => $payment]);
        }

        return redirect()->route('dashboard')->with('success', 'Deposit paid successfully from wallet!');
    }

  public function processPayment(Request $request): RedirectResponse
    {
        // 1. Validate inputs (Added transaction_reference)
        $validated = $request->validate([
            'bookingID'             => 'required|exists:booking,bookingID',
            'receipt_image'         => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'bank_name'             => 'required|string|max:100',
            'bank_account_number'   => 'required|string|max:50',
            'transaction_reference' => 'nullable|string|max:100', // <--- ADDED THIS
            'amount'                => 'required|numeric|min:0',
        ]);

        $booking = Booking::where('bookingID', $request->bookingID)->firstOrFail();

        // Security Check
        $currentCustomer = \App\Models\Customer::where('userID', Auth::user()->userID)->first();
        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Amount Calculation Logic
        $finalAmount = $request->amount;
        if (!$finalAmount) {
             $depositAmount = $this->paymentService->calculateDeposit($booking);
             $finalAmount = ($request->payment_type === 'Full Payment') ? $booking->rental_amount : $depositAmount;
        }

        // File Upload
        $file = $request->file('receipt_image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $uploadPath = public_path('uploads/payments');
        
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $fileName);
        $receiptURL = 'uploads/payments/' . $fileName;

        // 2. Create Payment (Added transaction_reference)
        $payment = Payment::create([
            'bookingID'             => $booking->bookingID,
            'total_amount'          => $finalAmount,
            'payment_bank_name'     => $request->bank_name,
            'payment_bank_account_no' => $request->bank_account_number,
            'transaction_reference' => $request->transaction_reference,
            'payment_status'        => 'Pending',
            'payment_date'          => now(),
            'isPayment_complete'    => false,
            'payment_isVerify'      => false,
            'latest_Update_Date_Time' => now(),
        ]);

        Notification::createForStaff(
            "New payment uploaded for Booking #{$booking->bookingID}. Amount: RM " . number_format($finalAmount, 2),
            null
        );

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Payment submitted successfully!');
    }
}