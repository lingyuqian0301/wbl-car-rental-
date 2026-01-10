<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Added for Auth
use Illuminate\Support\Facades\DB;   // Added for DB queries

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Start the query as usual
        $query = Vehicle::with('car')->whereIn('isActive', [1, 'true']);

        // 2. Check filters
        $hasFilters = $request->filled('brand') || 
                      $request->filled('vehicleType') || 
                      ($request->filled('start_date') && $request->filled('end_date'));

        if ($hasFilters) {
            if ($request->filled('brand')) {
                $query->where('vehicle_brand', $request->brand);
            }
            if ($request->filled('vehicleType')) {
                $query->where('vehicleType', $request->vehicleType);
            }
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $query->whereDoesntHave('bookings', function ($q) use ($startDate, $endDate) {
                    $q->where('booking_status', '!=', 'Cancelled')
                        ->where(function ($overlap) use ($startDate, $endDate) {
                            $overlap->where('rental_start_date', '<=', $endDate)
                                ->where('rental_end_date', '>=', $startDate);
                        });
                });
            }

            $cars = $query->get();

            if ($request->filled('start_date') && $request->filled('end_date') && $cars->isEmpty()) {
                session()->flash('unavailable', 'No vehicles available for the selected dates.');
            }

        } else {
            // Return empty if no filters (or change to Vehicle::all() if you want to show all by default)
            $cars = collect([]); 
        }

        // Dropdown data
        $vehicleTypes = Vehicle::whereIn('isActive', [1, 'true'])
            ->whereNotNull('vehicleType')
            ->distinct()
            ->pluck('vehicleType')
            ->filter()
            ->values();

        $brands = Vehicle::whereIn('isActive', [1, 'true'])
            ->whereNotNull('vehicle_brand')
            ->distinct()
            ->pluck('vehicle_brand')
            ->filter()
            ->values();

        // ==========================================
        // FETCH ACTIVE VOUCHER LOGIC (For Index Page)
        // ==========================================
        $activeVoucher = null;
        if (Auth::check()) {
            $customer = \App\Models\Customer::where('userID', Auth::id())->first();
            if ($customer) {
                // Join voucher table with loyalty card to find active vouchers for this customer
                $activeVoucher = DB::table('voucher')
                    ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                    ->where('loyaltycard.customerID', $customer->customerID)
                    ->where('voucher.voucher_isActive', 1)
                    ->select('voucher.*')
                    ->first();
            }
        }

        // Pass 'activeVoucher' to the view
        return view('welcome', compact('cars', 'vehicleTypes', 'brands', 'activeVoucher'));
    }

    public function show($id, Request $request)
    {
        $vehicle = Vehicle::with(['car', 'motorcycle'])->findOrFail($id);

        // 1. Calculate blocked dates (active bookings)
        $bookings = \App\Models\Booking::where('vehicleID', $vehicle->vehicleID)
            ->where('booking_status', '!=', 'Cancelled')
            ->select('rental_start_date', 'rental_end_date')
            ->get();

        $blockedDates = $bookings->map(function ($booking) {
            return [
                'from' => Carbon::parse($booking->rental_start_date)->format('Y-m-d'),
                'to'   => Carbon::parse($booking->rental_end_date)->format('Y-m-d'),
            ];
        });

        // 2. Capture dates from URL (if available) to pre-fill form
        $start_date = $request->query('start_date');
        $end_date   = $request->query('end_date');

        // ==========================================
        // FETCH ACTIVE VOUCHER LOGIC (For Show Page)
        // ==========================================
        $activeVoucher = null;
        if (Auth::check()) {
            $customer = \App\Models\Customer::where('userID', Auth::id())->first();
            if ($customer) {
                $activeVoucher = DB::table('voucher')
                    ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                    ->where('loyaltycard.customerID', $customer->customerID)
                    ->where('voucher.voucher_isActive', 1)
                    ->select('voucher.*')
                    ->first();
            }
        }
            // dd($activeVoucher);
        // Pass everything to the view
        return view('vehicles.show', compact('vehicle', 'blockedDates', 'start_date', 'end_date', 'activeVoucher'));
    }
}