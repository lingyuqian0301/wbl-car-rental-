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
        // === 1. SECURITY FIX STARTS HERE ===

        // Get the Customer profile linked to the logged-in user
        $currentCustomer = \App\Models\Customer::where('user_id', Auth::id())->first();

        // Check if the customer exists AND if they own this booking
        if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
            abort(403, 'UNAUTHORIZED ACCESS TO THIS BOOKING. (User ID: ' . Auth::id() . ' does not own Booking #' . $booking->bookingID . ')');
        }

        // === SECURITY FIX ENDS HERE ===

        // Load relationships
        $booking->load(['vehicle']); // removed 'user' as it might not be set in relation

        // Calculate deposit based on business rules
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        // Check if user can skip deposit with wallet
        $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet(Auth::id(), $depositAmount);

        // Safely access wallet balance
        $walletBalance = 0;
        if(Auth::user()->walletAccount) {
            $walletBalance = Auth::user()->walletAccount->available_balance;
        }

        return view('payments.create', [ // Make sure your folder is 'payments' or 'payment' matching your view structure
            'booking' => $booking,
            'depositAmount' => $depositAmount,
            'canSkipDeposit' => $canSkipDeposit,
            'walletBalance' => $walletBalance,
        ]);
    }

    /**
     * Show payment form for a specific booking by bookingID.
     *
     * @param int $bookingID
     * @return View
     */
    public function showPaymentForm(int $bookingID): View
    {
        $booking = Booking::where('bookingID', $bookingID)->firstOrFail();

        // Ensure the booking belongs to the authenticated user
        $isAuthorized = false;
        if ($booking->user && $booking->user->id === Auth::id()) {
            $isAuthorized = true;
        }
        if ($booking->customerID == Auth::id()) {
            $isAuthorized = true;
        }
        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Load relationships
        $booking->load(['vehicle']);

        // Calculate deposit based on business rules
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        return view('payment.create', [
            'booking' => $booking,
            'depositAmount' => $depositAmount,
        ]);
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(PaymentRequest $request): RedirectResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        // Ensure the booking belongs to the authenticated user
        $isAuthorized = false;
        if ($booking->user && $booking->user->id === Auth::id()) {
            $isAuthorized = true;
        }
        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Calculate deposit amount
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        // Handle file upload
        $proofPath = null;
        $receiptURL = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $proofPath = $file->storeAs('receipts', $fileName, 'public');
            $receiptURL = Storage::url($proofPath);
        }

        // Create payment record
        // US017: Status set to "Pending" which represents "Awaiting Payment Verification"
        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $depositAmount,
           'payment_type' => $request->input('payment_type', 'Deposit'),
            'payment_purpose' => 'booking_deposit',
            'receiptURL' => $receiptURL,
            'status' => 'Pending', // US017: This represents "Awaiting Payment Verification"
            'deposit_returned' => false,
            'keep_deposit' => $request->boolean('keep_deposit', false),
            'payment_date' => $request->payment_date ?? now(),
            'isPayment_complete' => false,
        ]);

        // Update booking keep_deposit flag
        $booking->update([
            'keep_deposit' => $request->boolean('keep_deposit', false),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Payment submitted successfully! Your payment is now awaiting verification. You will be notified once it is reviewed.');
    }

    /**
     * Submit payment with DuitNow QR receipt (API endpoint).
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function submitPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingID' => 'required|exists:booking,bookingID',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keep_deposit' => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $booking = Booking::findOrFail($request->bookingID);

        // Ensure the booking belongs to the authenticated user
         $currentCustomer = \App\Models\Customer::where('user_id', Auth::id())->first();

         if (!$currentCustomer || $booking->customerID !== $currentCustomer->customerID) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        abort(403, 'Unauthorized access to this booking.');
         }

        // Calculate deposit amount
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        // Handle file upload
        $file = $request->file('receipt_image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $proofPath = $file->storeAs('receipts', $fileName, 'public');
        $receiptURL = Storage::url($proofPath);

        // Create payment record
        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $depositAmount,
            'payment_type' => $request->input('payment_type', 'Deposit'),
            'payment_purpose' => 'booking_deposit',
            'receiptURL' => $receiptURL,
            'status' => 'Pending',
            'deposit_returned' => false,
            'keep_deposit' => $request->boolean('keep_deposit', false),
            'payment_date' => now(),
            'isPayment_complete' => false,
        ]);

        // Update booking keep_deposit flag
        $booking->update([
            'keep_deposit' => $request->boolean('keep_deposit', false),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment submitted successfully! Your payment is now awaiting verification.',
                'payment' => $payment,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Payment submitted successfully! Your payment is now awaiting verification.');
    }

    /**
     * Pay deposit using wallet balance.
     *
     * @param Request $request
     * @param Booking $booking
     * @return RedirectResponse|JsonResponse
     */
    public function payWithWallet(Request $request, Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this booking.',
                ], 403);
            }
            abort(403, 'Unauthorized access to this booking.');
        }

        // Calculate deposit amount
        $depositAmount = $this->paymentService->calculateDeposit($booking);

        // Check if user can skip deposit with wallet
        if (!$this->paymentService->canSkipDepositWithWallet(Auth::id(), $depositAmount)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance. Required: RM ' . number_format($depositAmount, 2),
                ], 400);
            }
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        // Pay deposit from wallet
        $payment = $this->paymentService->payDepositFromWallet($booking, $depositAmount);

        if (!$payment) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process wallet payment.',
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to process wallet payment.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Deposit paid successfully from wallet!',
                'payment' => $payment,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Deposit paid successfully from wallet!');
    }

    /**
     * Process payment submission with receipt and bank details.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function processPayment(Request $request): RedirectResponse
    {
        // Validate input
        $validated = $request->validate([
            'bookingID' => 'required|exists:booking,bookingID',
            'receipt_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
        ]);

        $booking = Booking::where('bookingID', $request->bookingID)->firstOrFail();

        // Ensure the booking belongs to the authenticated user
        $isAuthorized = false;
        if ($booking->user && $booking->user->id === Auth::id()) {
            $isAuthorized = true;
        }
        if ($booking->customerID == Auth::id()) {
            $isAuthorized = true;
        }
        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Calculate deposit amount (use provided amount or calculate)
        $depositAmount = $request->amount ?? $this->paymentService->calculateDeposit($booking);

        // Handle file upload to public/uploads/payments
        $file = $request->file('receipt_image');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Create directory if it doesn't exist
        $uploadPath = public_path('uploads/payments');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move file to public/uploads/payments
        $file->move($uploadPath, $fileName);
        $receiptURL = 'uploads/payments/' . $fileName;

        // Create payment record
        $payment = Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $depositAmount,
            'payment_type' => $request->input('payment_type', 'Deposit'),
            'payment_purpose' => 'booking_deposit',
            'receiptURL' => $receiptURL,
            'deposit_bank_name' => $request->bank_name,
            'deposit_bank_number' => $request->bank_account_number,
            'status' => 'Pending Verification',
            'payment_date' => now(),
            'deposit_returned' => false,
            'isPayment_complete' => false,
        ]);

        // Create notification for staff
        Notification::createForStaff(
            "New payment uploaded for Booking #{$booking->bookingID}. Amount: RM " . number_format($depositAmount, 2),
            null // staffID can be null for general staff notifications
        );

        // Redirect to booking history
        return redirect()
            ->route('bookings.index')
            ->with('success', 'Payment submitted successfully! Your payment is now pending verification. You will be notified once it is reviewed.');
    }
}
