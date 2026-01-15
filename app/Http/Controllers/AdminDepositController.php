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
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

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

        // Show all bookings with deposits (including hold, pending, and refunded)
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

        // Filter by refund status (pending, hold, refunded)
        if ($filterRefundStatus === 'pending') {
            $query->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('deposit_refund_status')
                           ->where('deposit_customer_choice', 'refund');
                  });
            });
        } elseif ($filterRefundStatus === 'hold') {
            $query->where(function($q) {
                $q->where('deposit_customer_choice', 'hold')
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('deposit_refund_status')
                           ->where('deposit_customer_choice', 'hold');
                  });
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

    /**
     * AJAX: Update deposit refund status
     */
    public function updateStatusAjax(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'deposit_refund_status' => 'required|in:pending,hold,refunded',
        ]);

        try {
            $status = $validated['deposit_refund_status'];
            $updateData = ['lastUpdateDate' => now()];
            
            // If status is 'hold', set refund_status to null (or keep it as 'hold' if you have that in DB)
            // For 'hold', we typically don't set a refund_status, it's determined by customer_choice
            if ($status === 'hold') {
                // Keep refund_status as is, but ensure customer_choice is 'hold'
                $updateData['deposit_customer_choice'] = 'hold';
                // Optionally set refund_status to null if it exists
                $updateData['deposit_refund_status'] = null;
            } else {
                $updateData['deposit_refund_status'] = $status;
            }
            
            $booking->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Refund status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update refund status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Update deposit fine amount and refund amount
     */
    public function updateFineAmountAjax(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'deposit_fine_amount' => 'required|numeric|min:0',
            'deposit_refund_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $depositAmount = $booking->deposit_amount ?? 0;
            $fineAmount = $validated['deposit_fine_amount'];
            
            // Auto-calculate refund amount if not provided: deposit_amount - fine_amount
            $refundAmount = $validated['deposit_refund_amount'] ?? ($depositAmount - $fineAmount);
            
            // Ensure refund amount is not negative
            if ($refundAmount < 0) {
                $refundAmount = 0;
            }
            
            // Ensure refund amount doesn't exceed deposit amount
            if ($refundAmount > $depositAmount) {
                $refundAmount = $depositAmount;
            }
            
            // Validate that fine + refund doesn't exceed deposit
            if (($fineAmount + $refundAmount) > $depositAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fine amount and refund amount cannot exceed the original deposit amount (RM ' . number_format($depositAmount, 2) . ').'
                ], 422);
            }

            // Build update data - only include columns that exist
            $updateData = ['lastUpdateDate' => now()];
            
            // Check if columns exist before updating
            if (Schema::hasColumn('booking', 'deposit_fine_amount')) {
                $updateData['deposit_fine_amount'] = $fineAmount;
            } else {
                \Log::warning('Column deposit_fine_amount does not exist in booking table');
            }
            
            if (Schema::hasColumn('booking', 'deposit_refund_amount')) {
                $updateData['deposit_refund_amount'] = $refundAmount;
            } else {
                \Log::warning('Column deposit_refund_amount does not exist in booking table');
            }

            // If no valid columns to update, return error
            if (count($updateData) === 1) { // Only lastUpdateDate
                return response()->json([
                    'success' => false,
                    'message' => 'Database columns not found. Please run migrations: php artisan migrate'
                ], 500);
            }

            $booking->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Fine amount and refund amount updated successfully.',
                'deposit_fine_amount' => number_format($fineAmount, 2),
                'deposit_refund_amount' => number_format($refundAmount, 2),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update deposit amounts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deposit amounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Update deposit handled by
     */
    public function updateHandledByAjax(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'deposit_handled_by' => 'nullable|exists:user,userID',
        ]);

        try {
            $booking->update([
                'deposit_handled_by' => $validated['deposit_handled_by'] ?? null,
                'lastUpdateDate' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Handled by updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update handled by: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload deposit refund receipt
     */
    public function uploadReceipt(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
        ]);

        try {
            $destinationPath = public_path('uploads/deposit_refund_receipts');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file = $request->file('receipt');
            $filename = 'deposit_refund_receipt_' . $booking->bookingID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $receiptPath = 'uploads/deposit_refund_receipts/' . $filename;

            $booking->deposit_refund_receipt = $receiptPath;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Receipt uploaded successfully.',
                'receipt_url' => asset($receiptPath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export deposits as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildDepositQuery($request);
        $bookings = $query->get();

        $pdf = Pdf::loadView('admin.deposits.export-pdf', [
            'bookings' => $bookings,
            'filters' => $request->all(),
        ]);

        return $pdf->download('deposits-export-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export deposits as Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildDepositQuery($request);
        $bookings = $query->get();

        $data = $bookings->map(function($booking) {
            $customer = $booking->customer;
            $user = $customer->user ?? null;
            $vehicle = $booking->vehicle ?? null;
            $handledBy = $booking->deposit_handled_by ? \App\Models\User::find($booking->deposit_handled_by) : null;
            $hasReturnForm = $booking->vehicleConditionForms && $booking->vehicleConditionForms->where('form_type', 'RETURN')->first();

            return [
                'Booking ID' => $booking->bookingID,
                'Customer Name' => $user->name ?? 'N/A',
                'Customer Email' => $user->email ?? 'N/A',
                'Vehicle Plate' => $vehicle->plate_number ?? 'N/A',
                'Deposit Amount' => number_format($booking->deposit_amount ?? 0, 2),
                'Vehicle Condition Form' => $hasReturnForm ? 'Submitted' : 'Pending',
                'Customer Choice' => $booking->deposit_customer_choice ? ucfirst(str_replace('_', ' ', $booking->deposit_customer_choice)) : 'N/A',
                'Fine Amount' => number_format($booking->deposit_fine_amount ?? 0, 2),
                'Refund Amount' => number_format($booking->deposit_refund_amount ?? 0, 2),
                'Refund Status' => ucfirst($booking->deposit_refund_status ?? 'pending'),
                'Handled By' => $handledBy->name ?? 'N/A',
                'Last Updated' => $booking->lastUpdateDate ? $booking->lastUpdateDate->format('Y-m-d H:i') : 'N/A',
            ];
        });

        $filename = 'deposits-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build deposit query with filters
     */
    private function buildDepositQuery(Request $request)
    {
        $search = $request->get('search');
        $filterRefundStatus = $request->get('filter_refund_status');
        $filterHandledBy = $request->get('filter_handled_by');
        $filterCustomerChoice = $request->get('filter_customer_choice');

        $query = Booking::with(['customer.user', 'vehicle', 'payments', 'vehicleConditionForms'])
            ->whereNotNull('deposit_amount')
            ->where('deposit_amount', '>', 0)
            ->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhereNull('deposit_refund_status');
            });

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

        if ($filterRefundStatus === 'pending') {
            $query->where(function($q) {
                $q->where('deposit_refund_status', 'pending')
                  ->orWhereNull('deposit_refund_status');
            });
        } elseif ($filterRefundStatus === 'refunded') {
            $query->where('deposit_refund_status', 'refunded');
        }

        if ($filterHandledBy) {
            $query->where('deposit_handled_by', $filterHandledBy);
        }

        if ($filterCustomerChoice) {
            $query->where('deposit_customer_choice', $filterCustomerChoice);
        }

        return $query->orderBy('lastUpdateDate', 'desc')->orderBy('bookingID', 'desc');
    }
}

