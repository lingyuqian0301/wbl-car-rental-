<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Show only bookings where customer requested deposit return
        // (deposit_refund_status = 'pending' OR deposit_refund_status IS NULL)
        $query = Booking::with(['customer.user', 'vehicle', 'payments'])
            ->whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhereNull('deposit_refund_status');
            });

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

        // Filter by refund status (only pending/refunded)
        if ($filterRefundStatus === 'pending') {
            $query->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhereNull('deposit_refund_status');
            });
        } elseif ($filterRefundStatus === 'refunded') {
            $query->where('deposit_refund_status', 'refunded');
        }

        // Filter by handled by
        if ($filterHandledBy) {
            $query->where('deposit_handled_by', $filterHandledBy);
        }

        // Filter by customer choice
        if ($filterCustomerChoice) {
            $query->where('deposit_customer_choice', $filterCustomerChoice);
        }

        // Sort by last update date/time (descending), then by booking ID
        $bookings = $query;
        
        $bookings = $bookings
        ->orderBy('lastUpdateDate', 'desc')
        ->orderBy('bookingID', 'desc')
        ->paginate(20)->withQueryString();

        // Calculate statistics
        $today = Carbon::today();
        
        // Deposit Hold: Sum of deposits where refund_status is null (held, not requested for refund)
        $depositHold = Booking::whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->whereNull('deposit_refund_status')
            ->sum('deposit_amount');

        // Deposit Not Yet Process: Count of deposit requests where refund_status is 'pending'
        $depositNotYetProcess = Booking::whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhereNull('deposit_refund_status');
            })
            ->count();

        // Get staff users for filter (exclude runner - only staffit and admin)
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->whereDoesntHave('runner'); // Exclude runners
            })->orWhereHas('admin');
        })->with(['staff.runner', 'staff.staffIt', 'admin'])->orderBy('name')->get();

        return view('admin.deposits.index', [
            'bookings' => $bookings,
            'search' => $search,
            'filterRefundStatus' => $filterRefundStatus,
            'filterHandledBy' => $filterHandledBy,
            'filterCustomerChoice' => $filterCustomerChoice,
            'depositHold' => $depositHold,
            'depositNotYetProcess' => $depositNotYetProcess,
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
            'refund_status' => 'required|in:pending,refunded',
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
            
            // Create notification for deposit return request if status is pending
            if ($validated['refund_status'] === 'pending' && $booking->deposit_refund_status !== 'pending') {
                try {
                    $vehicle = $booking->vehicle;
                    $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . $vehicle->plate_number . ')') : 'N/A';
                    $customer = $booking->customer;
                    
                    \App\Models\AdminNotification::create([
                        'type' => 'deposit_return_request',
                        'notifiable_type' => 'admin',
                        'notifiable_id' => null,
                        'user_id' => $customer->userID ?? null,
                        'booking_id' => $booking->bookingID,
                        'payment_id' => null,
                        'message' => "Deposit return request: Booking #{$booking->bookingID} - {$vehicleInfo}",
                        'data' => [
                            'booking_id' => $booking->bookingID,
                            'vehicle_info' => $vehicleInfo,
                            'deposit_amount' => $booking->deposit_amount,
                            'customer_name' => $customer->user->name ?? 'Customer',
                        ],
                        'is_read' => false,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to create deposit return notification: ' . $e->getMessage());
                }
            }

            // Only update customer choice if it's being changed
            if (isset($validated['customer_choice']) && $validated['customer_choice'] !== $booking->deposit_customer_choice) {
                $updateData['deposit_customer_choice'] = $validated['customer_choice'];
                $updateData['deposit_customer_choice_updated_at'] = now();
                
                // Create notification for deposit return request
                if ($validated['customer_choice'] === 'refund') {
                    try {
                        $vehicle = $booking->vehicle;
                        $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . $vehicle->plate_number . ')') : 'N/A';
                        $customer = $booking->customer;
                        
                        \App\Models\AdminNotification::create([
                            'type' => 'deposit_return_request',
                            'notifiable_type' => 'admin',
                            'notifiable_id' => null,
                            'user_id' => $customer->userID ?? null,
                            'booking_id' => $booking->bookingID,
                            'payment_id' => null,
                            'message' => "Deposit return request: Booking #{$booking->bookingID} - {$vehicleInfo}",
                            'data' => [
                                'booking_id' => $booking->bookingID,
                                'vehicle_info' => $vehicleInfo,
                                'deposit_amount' => $booking->deposit_amount,
                                'customer_name' => $customer->user->name ?? 'Customer',
                            ],
                            'is_read' => false,
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to create deposit return notification: ' . $e->getMessage());
                    }
                }
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

