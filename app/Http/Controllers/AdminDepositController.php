<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminDepositController extends Controller
{
    /**
     * Display a listing of all deposits.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filterRefundStatus = $request->get('filter_refund_status');
        $filterHandledBy = $request->get('filter_handled_by');
        $filterCustomerChoice = $request->get('filter_customer_choice');

        $query = Booking::with(['customer.user', 'vehicle', 'payments'])
            ->whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0);

        // Search by booking ID or customer name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by refund status
        if ($filterRefundStatus !== null && $filterRefundStatus !== '') {
            if ($filterRefundStatus === 'refunded') {
                $query->whereHas('payments', function($q) {
                    $q->where('payment_status', 'Refunded')
                      ->where('total_amount', '>', 0);
                });
            } elseif ($filterRefundStatus === 'no_action') {
                $query->whereDoesntHave('payments', function($q) {
                    $q->where('payment_status', 'Refunded');
                });
            }
        }

        // Filter by handled by
        if ($filterHandledBy) {
            $query->where('deposit_handled_by', $filterHandledBy);
        }

        // Filter by customer choice
        if ($filterCustomerChoice !== null && $filterCustomerChoice !== '') {
            $query->where('deposit_customer_choice', $filterCustomerChoice);
        }

        // Sort by customer choice update date/time (descending), then by booking ID
        $bookings = $query->orderByRaw('
            CASE 
                WHEN deposit_customer_choice IS NOT NULL THEN 1 
                ELSE 2 
            END
        ')
        ->orderBy('lastUpdateDate', 'desc')
        ->orderBy('bookingID', 'desc')
        ->paginate(20)->withQueryString();

        // Calculate statistics
        $today = Carbon::today();
        $totalDeposits = Booking::whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->count();
        
        $totalDepositAmount = Booking::whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->sum('deposit_amount');

        $refundedCount = Booking::whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->whereHas('payments', function($q) {
                $q->where('payment_status', 'Refunded');
            })
            ->count();

        $noActionCount = $totalDeposits - $refundedCount;

        // Get staff users for filter
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        return view('admin.deposits.index', [
            'bookings' => $bookings,
            'search' => $search,
            'filterRefundStatus' => $filterRefundStatus,
            'filterHandledBy' => $filterHandledBy,
            'filterCustomerChoice' => $filterCustomerChoice,
            'totalDeposits' => $totalDeposits,
            'totalDepositAmount' => $totalDepositAmount,
            'refundedCount' => $refundedCount,
            'noActionCount' => $noActionCount,
            'staffUsers' => $staffUsers,
            'today' => $today,
        ]);
    }

    /**
     * Show refund detail page for a booking.
     */
    public function show(Booking $booking): View
    {
        $booking->load([
            'customer.user',
            'vehicle',
            'payments'
        ]);

        // Get deposit payment
        $depositPayment = $booking->payments()
            ->where('payment_status', '!=', 'Refunded')
            ->orderBy('payment_date', 'asc')
            ->first();

        // Get refund payment if exists
        $refundPayment = $booking->payments()
            ->where('payment_status', 'Refunded')
            ->first();

        return view('admin.deposits.show', [
            'booking' => $booking,
            'depositPayment' => $depositPayment,
            'refundPayment' => $refundPayment,
        ]);
    }

    /**
     * Update deposit refund status and handled by.
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'refund_status' => 'required|in:no_action,refunded',
            'handled_by' => 'nullable|exists:user,userID',
            'fine_amount' => 'nullable|numeric|min:0',
            'refund_amount' => 'nullable|numeric|min:0',
            'customer_choice' => 'nullable|in:hold,refund',
        ]);

        DB::beginTransaction();
        try {
            // Update booking deposit fields
            $updateData = [
                'deposit_refund_status' => $validated['refund_status'],
                'deposit_handled_by' => $validated['handled_by'] ?? Auth::user()->userID,
                'deposit_fine_amount' => $validated['fine_amount'] ?? 0,
                'deposit_refund_amount' => $validated['refund_amount'] ?? 0,
            ];

            // Only update customer choice if it's being changed
            if (isset($validated['customer_choice']) && $validated['customer_choice'] !== $booking->deposit_customer_choice) {
                $updateData['deposit_customer_choice'] = $validated['customer_choice'];
                $updateData['deposit_customer_choice_updated_at'] = now();
            }

            $booking->update($updateData);

            // If refunded, create refund payment
            if ($validated['refund_status'] === 'refunded' && $validated['refund_amount'] > 0) {
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'total_amount' => -$validated['refund_amount'], // Negative for refund
                    'payment_status' => 'Refunded',
                    'payment_date' => now(),
                    'payment_isVerify' => true,
                    'verify_by' => Auth::user()->userID,
                    'isPayment_complete' => true,
                    'latest_Update_Date_Time' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Deposit information updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update deposit: ' . $e->getMessage());
        }
    }
}

