<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\OwnerCar;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'expenses-profit');

        if ($activeTab === 'expenses-profit') {
            return $this->expensesProfitTab($request);
        } elseif ($activeTab === 'monthly-income') {
            return $this->monthlyIncomeTab($request);
        } else {
            return $this->dailyIncomeTab($request);
        }
    }

    private function expensesProfitTab(Request $request): View
    {
        // Get filter type (car/motor/all)
        $vehicleType = $request->get('vehicle_type', 'all');
        
        // Get all vehicles with their owner, earnings, expenses, and profit
        $query = Vehicle::with(['owner', 'bookings.payments', 'maintenances', 'car', 'motorcycle']);
        
        // Filter by vehicle type
        if ($vehicleType === 'car') {
            $query->whereHas('car');
        } elseif ($vehicleType === 'motor') {
            $query->whereHas('motorcycle');
        }
        // If 'all', no filter needed
        
        $vehicles = $query->get()
            ->map(function ($vehicle) {
                // Get owner leasing price
                $leasingPrice = $vehicle->owner->leasing_price ?? 0;

                // Get total earnings from verified payments for this vehicle
                $earnings = $vehicle->bookings()
                    ->with('payments')
                    ->get()
                    ->flatMap(function ($booking) {
                        return $booking->payments()
                            ->where('payment_status', 'Verified')
                            ->get();
                    })
                    ->sum('total_amount');

                // Get total maintenance expenses for this vehicle
                $expenses = $vehicle->maintenances()->sum('cost');

                // Calculate profit: earnings - expenses - leasing price
                $profit = $earnings - $expenses - $leasingPrice;

                return [
                    'vehicleID' => $vehicle->vehicleID,
                    'vehicle' => ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? ''),
                    'plate_number' => $vehicle->plate_number ?? 'N/A',
                    'leasing_price' => $leasingPrice,
                    'expenses' => $expenses,
                    'profit' => $profit,
                ];
            })
            ->sortBy('vehicleID');

        return view('admin.finance.index', [
            'activeTab' => 'expenses-profit',
            'vehicles' => $vehicles,
            'vehicleType' => $vehicleType,
        ]);
    }

    private function monthlyIncomeTab(Request $request): View
    {
        $selectedYear = $request->get('year', date('Y'));

        $months = [];
        $yearTotalRentals = 0;
        $yearTotalExpenses = 0;
        $yearTotalLeasingPrice = 0;
        $yearTotalEarnings = 0;
        $yearTotalProfit = 0;

        for ($month = 1; $month <= 12; $month++) {
            $dateFrom = Carbon::create($selectedYear, $month, 1)->startOfMonth();
            $dateTo = Carbon::create($selectedYear, $month, 1)->endOfMonth();

            // Get bookings (rentals) in this month
            $bookings = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])->get();
            $totalRentals = $bookings->count();

            // Get earnings from verified payments in this month
            $payments = Payment::where('payment_status', 'Verified')
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->get();
            $totalEarnings = $payments->sum('total_amount');

            // Get maintenance expenses in this month
            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dateFrom, $dateTo])
                ->sum('cost');

            // Get leasing price for unique owners whose vehicles were rented in this month
            $vehicleIds = $bookings->pluck('vehicleID')->unique();
            $ownerIds = Vehicle::whereIn('vehicleID', $vehicleIds)->pluck('ownerID')->unique()->filter();
            $leasingPrice = OwnerCar::whereIn('ownerID', $ownerIds)->sum('leasing_price');

            // Total expenses = maintenance expenses + leasing price
            $totalExpenses = $maintenanceExpenses + $leasingPrice;

            // Profit = earnings - expenses
            $profit = $totalEarnings - $totalExpenses;

            $months[] = [
                'month' => $month,
                'monthName' => $dateFrom->format('F'),
                'totalRentals' => $totalRentals,
                'totalExpenses' => $totalExpenses,
                'totalEarnings' => $totalEarnings,
                'profit' => $profit,
            ];

            $yearTotalRentals += $totalRentals;
            $yearTotalExpenses += $totalExpenses;
            $yearTotalLeasingPrice += $leasingPrice;
            $yearTotalEarnings += $totalEarnings;
            $yearTotalProfit += $profit;
        }

        return view('admin.finance.index', [
            'activeTab' => 'monthly-income',
            'selectedYear' => $selectedYear,
            'months' => $months,
            'yearTotalRentals' => $yearTotalRentals,
            'yearTotalExpenses' => $yearTotalExpenses,
            'yearTotalLeasingPrice' => $yearTotalLeasingPrice,
            'yearTotalEarnings' => $yearTotalEarnings,
            'yearTotalProfit' => $yearTotalProfit,
        ]);
    }

    private function dailyIncomeTab(Request $request): View
    {
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', date('m'));

        $dateFrom = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $dateTo = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth();

        $days = [];
        $currentDate = $dateFrom->copy();

        while ($currentDate->lte($dateTo)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            // Get bookings (rentals) on this day
            $bookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])->get();
            
            // Get earnings from verified payments on this day
            $payments = Payment::where('payment_status', 'Verified')
                ->whereBetween('payment_date', [$dayStart, $dayEnd])
                ->get();
            $totalRentalAmount = $payments->sum('total_amount');

            // Get maintenance expenses on this day
            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])
                ->sum('cost');

            // For daily income, only include maintenance expenses (leasing price is monthly)
            $totalExpenses = $maintenanceExpenses;

            // Profit = earnings - expenses
            $profit = $totalRentalAmount - $totalExpenses;

            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dateFormatted' => $currentDate->format('d M Y'),
                'totalRentalAmount' => $totalRentalAmount,
                'totalExpenses' => $totalExpenses,
                'profit' => $profit,
            ];

            $currentDate->addDay();
        }

        return view('admin.finance.index', [
            'activeTab' => 'daily-income',
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'days' => $days,
        ]);
    }
}
