<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\LeasingExpense;
use App\Models\MaintenanceRecord;
use App\Models\Car;
use App\Models\Motorcycle;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    public function index(Request $request): View
    {
        // Get filter parameters
        $periodType = $request->get('period_type', 'monthly'); // daily, weekly, monthly, yearly
        $selectedDate = $request->get('selected_date', date('Y-m-d'));
        $weekFrom = $request->get('week_from');
        $weekTo = $request->get('week_to');
        $selectedMonth = $request->get('selected_month', date('Y-m'));
        $selectedYear = $request->get('selected_year', date('Y'));
        $vehicleType = $request->get('vehicle_type', 'all'); // all, car, motorcycle
        $vehicleBrand = $request->get('vehicle_brand');
        $vehicleModel = $request->get('vehicle_model');

        // Calculate date range based on period type
        $dateFrom = null;
        $dateTo = null;

        switch ($periodType) {
            case 'daily':
                $dateFrom = $selectedDate;
                $dateTo = $selectedDate;
                break;
            case 'weekly':
                $dateFrom = $weekFrom ?? Carbon::now()->startOfWeek()->format('Y-m-d');
                $dateTo = $weekTo ?? Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $monthParts = explode('-', $selectedMonth);
                $dateFrom = Carbon::create($monthParts[0], $monthParts[1], 1)->startOfMonth()->format('Y-m-d');
                $dateTo = Carbon::create($monthParts[0], $monthParts[1], 1)->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $dateFrom = Carbon::create($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
                $dateTo = Carbon::create($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');
                break;
        }

        // Get earnings (payments)
        $earningsQuery = Payment::where('payment_status', 'Verified')
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        // Filter by vehicle if needed
        if ($vehicleType !== 'all' || $vehicleBrand || $vehicleModel) {
            $earningsQuery->whereHas('booking', function($q) use ($vehicleType, $vehicleBrand, $vehicleModel) {
                if ($vehicleType !== 'all') {
                    // Will filter in PHP after getting results
                }
            });
        }

        $allPayments = $earningsQuery->with('booking')->get();

        // Filter payments by vehicle type/brand/model
        $filteredPayments = $allPayments->filter(function($payment) use ($vehicleType, $vehicleBrand, $vehicleModel) {
            $vehicle = $payment->booking->vehicle ?? null;
            if (!$vehicle) return false;

            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) return false;
                if ($vehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) return false;
            }

            if ($vehicleBrand && stripos($vehicle->vehicle_brand ?? '', $vehicleBrand) === false) return false;
            if ($vehicleModel && stripos($vehicle->vehicle_model ?? '', $vehicleModel) === false) return false;

            return true;
        });

        // Calculate earnings breakdown
        $depositEarnings = $filteredPayments->where('payment_type', 'Deposit')->sum('amount');
        $balanceEarnings = $filteredPayments->where('payment_type', 'Balance')->sum('amount');
        $fullPaymentEarnings = $filteredPayments->where('payment_type', 'Full Payment')->sum('amount');
        $totalEarnings = $depositEarnings + $balanceEarnings + $fullPaymentEarnings;

        // Get expenses (only maintenance, no leasing)
        $maintenanceExpenses = MaintenanceRecord::whereBetween('service_date', [$dateFrom, $dateTo])->sum('cost');
        $totalExpenses = $maintenanceExpenses;

        // Calculate profit
        $totalProfit = $totalEarnings - $totalExpenses;

        // Get detailed earnings list
        $earningsList = $filteredPayments->map(function($payment) {
            $vehicle = $payment->booking->vehicle ?? null;
            return [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking->id ?? $payment->booking->bookingID ?? null,
                'customer_name' => $payment->booking->user->name ?? 'Unknown',
                'vehicle' => $vehicle ? (($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '')) : 'N/A',
                'payment_type' => $payment->payment_type,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
            ];
        })->sortByDesc('payment_date');

        // Get detailed expenses list (only maintenance)
        $expensesList = MaintenanceRecord::whereBetween('service_date', [$dateFrom, $dateTo])
            ->get()
            ->map(function($expense) {
                return [
                    'type' => 'Maintenance',
                    'description' => $expense->description ?? $expense->service_type,
                    'amount' => $expense->cost,
                    'date' => $expense->service_date,
                ];
            })
            ->sortByDesc('date');

        // Get filter options
        $cars = Car::orderBy('vehicle_brand')->orderBy('vehicle_model')->get();
        $motorcycles = Motorcycle::orderBy('vehicle_brand')->orderBy('vehicle_model')->get();
        $allBrands = $cars->pluck('vehicle_brand')->merge($motorcycles->pluck('vehicle_brand'))->unique()->sort();

        return view('admin.finance.index', [
            'periodType' => $periodType,
            'selectedDate' => $selectedDate,
            'weekFrom' => $weekFrom,
            'weekTo' => $weekTo,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'vehicleType' => $vehicleType,
            'vehicleBrand' => $vehicleBrand,
            'vehicleModel' => $vehicleModel,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'depositEarnings' => $depositEarnings,
            'balanceEarnings' => $balanceEarnings,
            'fullPaymentEarnings' => $fullPaymentEarnings,
            'totalEarnings' => $totalEarnings,
            'leasingExpenses' => 0,
            'maintenanceExpenses' => $maintenanceExpenses ?? 0,
            'totalExpenses' => $totalExpenses,
            'totalProfit' => $totalProfit,
            'earningsList' => $earningsList,
            'expensesList' => $expensesList,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'allBrands' => $allBrands,
        ]);
    }

    public function exportPDF(Request $request): Response
    {
        // Get same filters as index
        $periodType = $request->get('period_type', 'monthly');
        $selectedDate = $request->get('selected_date', date('Y-m-d'));
        $weekFrom = $request->get('week_from');
        $weekTo = $request->get('week_to');
        $selectedMonth = $request->get('selected_month', date('Y-m'));
        $selectedYear = $request->get('selected_year', date('Y'));
        $vehicleType = $request->get('vehicle_type', 'all');
        $vehicleBrand = $request->get('vehicle_brand');
        $vehicleModel = $request->get('vehicle_model');

        // Calculate date range
        $dateFrom = null;
        $dateTo = null;

        switch ($periodType) {
            case 'daily':
                $dateFrom = $selectedDate;
                $dateTo = $selectedDate;
                break;
            case 'weekly':
                $dateFrom = $weekFrom ?? Carbon::now()->startOfWeek()->format('Y-m-d');
                $dateTo = $weekTo ?? Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $monthParts = explode('-', $selectedMonth);
                $dateFrom = Carbon::create($monthParts[0], $monthParts[1], 1)->startOfMonth()->format('Y-m-d');
                $dateTo = Carbon::create($monthParts[0], $monthParts[1], 1)->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $dateFrom = Carbon::create($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
                $dateTo = Carbon::create($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');
                break;
        }

        // Get earnings
        $earningsQuery = Payment::where('payment_status', 'Verified')
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        $allPayments = $earningsQuery->with('booking')->get();

        $filteredPayments = $allPayments->filter(function($payment) use ($vehicleType, $vehicleBrand, $vehicleModel) {
            $vehicle = $payment->booking->vehicle ?? null;
            if (!$vehicle) return false;
            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) return false;
                if ($vehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) return false;
            }
            if ($vehicleBrand && stripos($vehicle->vehicle_brand ?? '', $vehicleBrand) === false) return false;
            if ($vehicleModel && stripos($vehicle->vehicle_model ?? '', $vehicleModel) === false) return false;
            return true;
        });

        $depositEarnings = $filteredPayments->where('payment_type', 'Deposit')->sum('amount');
        $balanceEarnings = $filteredPayments->where('payment_type', 'Balance')->sum('amount');
        $fullPaymentEarnings = $filteredPayments->where('payment_type', 'Full Payment')->sum('amount');
        $totalEarnings = $depositEarnings + $balanceEarnings + $fullPaymentEarnings;

        $maintenanceExpenses = MaintenanceRecord::whereBetween('service_date', [$dateFrom, $dateTo])->sum('cost');
        $totalExpenses = $maintenanceExpenses;
        $totalProfit = $totalEarnings - $totalExpenses;

        $earningsList = $filteredPayments->map(function($payment) {
            $vehicle = $payment->booking->vehicle ?? null;
            return [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking->id ?? $payment->booking->bookingID ?? null,
                'customer_name' => $payment->booking->user->name ?? 'Unknown',
                'vehicle' => $vehicle ? (($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '')) : 'N/A',
                'payment_type' => $payment->payment_type,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
            ];
        })->sortByDesc('payment_date');

        $expensesList = MaintenanceRecord::whereBetween('service_date', [$dateFrom, $dateTo])
            ->get()
            ->map(fn($e) => ['type' => 'Maintenance', 'description' => $e->description ?? $e->service_type, 'amount' => $e->cost, 'date' => $e->service_date])
            ->sortByDesc('date');

        $pdf = DomPDF::loadView('admin.finance.pdf', [
            'periodType' => $periodType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'vehicleType' => $vehicleType,
            'vehicleBrand' => $vehicleBrand,
            'vehicleModel' => $vehicleModel,
            'depositEarnings' => $depositEarnings,
            'balanceEarnings' => $balanceEarnings,
            'fullPaymentEarnings' => $fullPaymentEarnings,
            'totalEarnings' => $totalEarnings,
            'leasingExpenses' => 0,
            'maintenanceExpenses' => $maintenanceExpenses ?? 0,
            'totalExpenses' => $totalExpenses,
            'totalProfit' => $totalProfit,
            'earningsList' => $earningsList,
            'expensesList' => $expensesList,
        ]);

        return $pdf->download('finance-report-' . date('Y-m-d') . '.pdf');
    }
}






