<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminVoucherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Voucher::query();
        // Only add withCount if voucher_usage table exists
        if (\Schema::hasTable('voucher_usage')) {
            $query->withCount('usages');
        }

        // Search by voucher ID or voucher name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucherID', 'like', "%{$search}%")
                  ->orWhere('voucher_name', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('voucher_isActive', true);
                // Check if expiry_date column exists before using it
                if (\Schema::hasColumn('voucher', 'expiry_date')) {
                    $query->where(function($q) {
                          $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', Carbon::today());
                    });
                }
                // Check if num_valid and num_applied columns exist before using them
                if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
                    $query->whereRaw('(num_valid - num_applied) > 0');
                }
            } elseif ($request->status === 'inactive') {
                $query->where(function($q) {
                    $q->where('voucher_isActive', false);
                    // Check if expiry_date column exists before using it
                    if (\Schema::hasColumn('voucher', 'expiry_date')) {
                        $q->orWhere(function($q2) {
                          $q2->whereNotNull('expiry_date')
                             ->where('expiry_date', '<', Carbon::today());
                        });
                    }
                    // Check if num_valid and num_applied columns exist before using them
                    if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
                        $q->orWhereRaw('(num_valid - num_applied) <= 0');
                    }
                });
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'latest':
                $query->orderBy('voucherID', 'desc');
                break;
            case 'oldest':
                $query->orderBy('voucherID', 'asc');
                break;
            case 'code_asc':
                $query->orderBy('voucher_code', 'asc');
                break;
            case 'code_desc':
                $query->orderBy('voucher_code', 'desc');
                break;
            case 'expiry_asc':
                $query->orderBy('expiry_date', 'asc');
                break;
            case 'expiry_desc':
                $query->orderBy('expiry_date', 'desc');
                break;
            default:
                $query->orderBy('voucherID', 'desc');
        }

        $vouchers = $query->paginate(20)->withQueryString();

        // Calculate num_left for each voucher
        $vouchers->getCollection()->transform(function ($voucher) {
            // Check if num_valid and num_applied columns exist
            if (isset($voucher->num_valid) && isset($voucher->num_applied)) {
            $voucher->num_left = $voucher->num_valid - $voucher->num_applied;
            } else {
                $voucher->num_left = 0;
            }
            $voucher->is_active_status = $voucher->isActiveStatus;
            $voucher->active_status_text = $voucher->activeStatusText;
            return $voucher;
        });

        // Summary stats for header
        $today = Carbon::today();
        $totalVouchers = Voucher::count();
        $activeVouchersQuery = Voucher::where('voucher_isActive', true);
        // Check if expiry_date column exists before using it
        if (\Schema::hasColumn('voucher', 'expiry_date')) {
            $activeVouchersQuery->where(function($q) use ($today) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', $today);
            });
        }
        // Check if num_valid and num_applied columns exist before using them
        if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
            $activeVouchersQuery->whereRaw('(num_valid - num_applied) > 0');
        }
        $activeVouchers = $activeVouchersQuery->count();
        // Check if voucher_usage table exists before querying
        $totalUsed = \Schema::hasTable('voucher_usage') ? VoucherUsage::count() : 0;
        // Check if num_applied column exists before summing
        $totalApplied = \Schema::hasColumn('voucher', 'num_applied') ? Voucher::sum('num_applied') : 0;

        return view('admin.vouchers.index', [
            'vouchers' => $vouchers,
            'showHeader' => $request->get('show_header', true),
            'totalVouchers' => $totalVouchers,
            'activeVouchers' => $activeVouchers,
            'totalUsed' => $totalUsed,
            'totalApplied' => $totalApplied,
            'today' => $today,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Based on actual database structure: voucherID, loyaltyCardID, discount_type, discount_amount, voucher_isActive
        $validated = $request->validate([
            'loyaltyCardID' => 'nullable|integer|exists:loyaltycard,loyaltyCardID',
            'discount_type' => 'required|string|in:percentage,flat',
            'discount_amount' => 'required|numeric|min:0',
            'voucher_isActive' => 'required|in:0,1',
        ]);

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher created successfully.');
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        // Based on actual database structure: voucherID, loyaltyCardID, discount_type, discount_amount, voucher_isActive
        $validated = $request->validate([
            'loyaltyCardID' => 'nullable|integer|exists:loyaltycard,loyaltyCardID',
            'discount_type' => 'required|string|in:percentage,flat',
            'discount_amount' => 'required|numeric|min:0',
            'voucher_isActive' => 'required|in:0,1',
        ]);

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher updated successfully.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        // Check if voucher has been used (only if voucher_usage table exists)
        if (\Schema::hasTable('voucher_usage') && $voucher->usages()->count() > 0) {
            return redirect()->route('admin.vouchers.index')
                ->with('error', 'Cannot delete voucher that has been used.');
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher deleted successfully.');
    }

    public function editData(Voucher $voucher): JsonResponse
    {
        return response()->json($voucher);
    }

    public function showUsedCustomers(Voucher $voucher): JsonResponse
    {
        // Check if voucher_usage table exists before querying
        if (!\Schema::hasTable('voucher_usage')) {
            return response()->json([
                'success' => true,
                'customers' => [],
                'total' => 0,
            ]);
        }

        $usages = VoucherUsage::where('voucherID', $voucher->voucherID)
            ->with('customer')
            ->orderBy('used_at', 'desc')
            ->get();

        $customers = $usages->map(function ($usage) {
            return [
                'customer_name' => $usage->customer->fullname ?? 'N/A',
                'customer_id' => $usage->customerID,
                'used_at' => $usage->used_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'customers' => $customers,
            'total' => $customers->count(),
        ]);
    }
}
