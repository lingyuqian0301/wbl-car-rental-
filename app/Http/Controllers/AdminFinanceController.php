<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\OwnerCar;
use App\Models\Booking;
use App\Models\Fuel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminFinanceController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'expenses-profit');

        if ($activeTab === 'expenses-profit') {
            return $this->expensesProfitTab($request);
        } elseif ($activeTab === 'monthly-income') {
            return $this->monthlyIncomeTab($request);
        } elseif ($activeTab === 'weekly-income') {
            return $this->weeklyIncomeTab($request);
        } else {
            return $this->dailyIncomeTab($request);
        }
    }

    private function expensesProfitTab(Request $request): View
    {
        // Get filters
        $vehicleType = $request->get('vehicle_type', 'all');
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        
        // Date range for filtering
        $dateFrom = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $dateTo = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth();
        
        // Get all vehicles with their owner, earnings, expenses, and profit
        $query = Vehicle::with(['owner', 'bookings.payments', 'maintenances', 'fuels', 'car', 'motorcycle']);
        
        // Filter by vehicle type
        if ($vehicleType === 'car') {
            $query->whereHas('car');
        } elseif ($vehicleType === 'motor') {
            $query->whereHas('motorcycle');
        }
        
        $vehicles = $query->get()
            ->map(function ($vehicle) use ($dateFrom, $dateTo) {
                // Get owner leasing price
                $leasingPrice = $vehicle->owner->leasing_price ?? 0;

                // Get total earnings from verified payments for this vehicle (rental price only, not deposit)
                $earnings = $vehicle->bookings()
                    ->whereBetween('rental_start_date', [$dateFrom, $dateTo])
                    ->sum('rental_amount');

                // Get total maintenance expenses for this vehicle in the period
                $maintenanceExpenses = $vehicle->maintenances()
                    ->whereBetween('service_date', [$dateFrom, $dateTo])
                    ->sum('cost');

                // Get total staff commission for this vehicle:
                // 1. Booking commission: 10% of rental_price for bookings served by staff
                $bookingCommission = $vehicle->bookings()
                    ->whereBetween('rental_start_date', [$dateFrom, $dateTo])
                    ->whereNotNull('staff_served')
                    ->get()
                    ->sum(function($booking) {
                        return ($booking->rental_amount ?? 0) * 0.10; // 10% commission
                    });

                // 2. Maintenance commission: commission_amount from maintenance records
                $maintenanceCommission = $vehicle->maintenances()
                    ->whereBetween('service_date', [$dateFrom, $dateTo])
                    ->whereNotNull('staffID')
                    ->sum('commission_amount');

                // 3. Fuel commission: RM2 per fuel record served by staff
                $fuelCommission = $vehicle->fuels()
                    ->whereBetween('fuel_date', [$dateFrom, $dateTo])
                    ->whereNotNull('handled_by')
                    ->count() * 2; // RM2 per fuel record

                // 4. Runner commission: RM2 per pickup/return at non-HASTA HQ Office locations
                $runnerCommission = 0;
                $runnerBookings = $vehicle->bookings()
                    ->whereBetween('rental_start_date', [$dateFrom, $dateTo])
                    ->whereNotNull('staff_served')
                    ->where(function($q) {
                        $q->where(function($subQ) {
                            $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                                 ->whereNotNull('pickup_point');
                        })->orWhere(function($subQ) {
                            $subQ->where('return_point', '!=', 'HASTA HQ Office')
                                 ->whereNotNull('return_point');
                        });
                    })
                    ->get();

                foreach ($runnerBookings as $runnerBooking) {
                    // RM2 for pickup if not at HASTA HQ Office
                    if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                        $runnerCommission += 2;
                    }
                    // RM2 for return if not at HASTA HQ Office
                    if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                        $runnerCommission += 2;
                    }
                }

                $totalStaffExpenses = $bookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;

                // Total expenses = maintenance expenses + staff commission
                $totalExpenses = $maintenanceExpenses + $totalStaffExpenses;

                // Calculate profit: earnings - expenses - leasing price
                $profit = $earnings - $totalExpenses - $leasingPrice;

                return [
                    'vehicleID' => $vehicle->vehicleID,
                    'vehicle' => ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? ''),
                    'plate_number' => $vehicle->plate_number ?? 'N/A',
                    'leasing_price' => $leasingPrice,
                    'ownerID' => $vehicle->ownerID,
                    'expenses' => $maintenanceExpenses,
                    'staff_expenses' => $totalStaffExpenses,
                    'profit' => $profit,
                ];
            })
            ->sortBy('vehicleID');

        // Calculate totals
        $totalVehicles = $vehicles->count();
        $totalProfit = $vehicles->sum('profit');

        return view('admin.finance.index', [
            'activeTab' => 'expenses-profit',
            'vehicles' => $vehicles,
            'vehicleType' => $vehicleType,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'totalVehicles' => $totalVehicles,
            'totalProfit' => $totalProfit,
        ]);
    }

    public function updateLeasingPrice(Request $request, OwnerCar $owner)
    {
        $request->validate([
            'leasing_price' => 'required|numeric|min:0',
        ]);

        $owner->update([
            'leasing_price' => $request->leasing_price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leasing price updated successfully.',
        ]);
    }

    private function monthlyIncomeTab(Request $request): View
    {
        $selectedYear = $request->get('year', date('Y'));

        $months = [];
        $yearTotalRentals = 0;
        $yearTotalExpenses = 0;
        $yearTotalEarnings = 0;
        $yearTotalProfit = 0;

        for ($month = 1; $month <= 12; $month++) {
            $dateFrom = Carbon::create($selectedYear, $month, 1)->startOfMonth();
            $dateTo = Carbon::create($selectedYear, $month, 1)->endOfMonth();

            // Get bookings (rentals) in this month
            $bookings = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])->get();
            $totalRentals = $bookings->count();

            // Total earning = rental_price (not deposit) + fine_amount
            $totalEarnings = $bookings->sum('rental_amount') + $bookings->sum('deposit_fine_amount');

            // Get maintenance expenses in this month
            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dateFrom, $dateTo])
                ->sum('cost');

            // Calculate staff commission:
            // 1. Booking commission: 10% of rental_price for bookings served by staff
            $bookingCommission = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])
                ->whereNotNull('staff_served')
                ->sum(DB::raw('rental_amount * 0.10'));

            // 2. Maintenance commission
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dateFrom, $dateTo])
                ->whereNotNull('staffID')
                ->sum('commission_amount');

            // 3. Fuel commission: RM2 per fuel record
            $fuelCommission = Fuel::whereBetween('fuel_date', [$dateFrom, $dateTo])
                ->whereNotNull('handled_by')
                ->count() * 2;

            // 4. Runner commission: RM2 per pickup/return at non-HASTA HQ Office locations
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();

            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $bookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;

            // Total expenses = staff commission + maintenance cost
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

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
            $yearTotalEarnings += $totalEarnings;
            $yearTotalProfit += $profit;
        }

        return view('admin.finance.index', [
            'activeTab' => 'monthly-income',
            'selectedYear' => $selectedYear,
            'months' => $months,
            'yearTotalRentals' => $yearTotalRentals,
            'yearTotalExpenses' => $yearTotalExpenses,
            'yearTotalEarnings' => $yearTotalEarnings,
            'yearTotalProfit' => $yearTotalProfit,
        ]);
    }

    private function weeklyIncomeTab(Request $request): View
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $dateFrom = Carbon::parse($startDate);
        $dateTo = $dateFrom->copy()->addDays(6)->endOfDay();

        $days = [];
        $currentDate = $dateFrom->copy();
        $weekTotalEarnings = 0;
        $weekTotalExpenses = 0;
        $weekTotalProfit = 0;

        while ($currentDate->lte($dateTo)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            // Get bookings on this day
            $bookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])->get();

            // Total earning = rental_price (not deposit) + fine_amount
            $totalEarnings = $bookings->sum('rental_amount') + $bookings->sum('deposit_fine_amount');

            // Get maintenance expenses on this day
            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])
                ->sum('cost');

            // Calculate staff commission for this day:
            // 1. Booking commission: 10% of rental_price
            $bookingCommission = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->sum(DB::raw('rental_amount * 0.10'));

            // 2. Maintenance commission
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])
                ->whereNotNull('staffID')
                ->sum('commission_amount');

            // 3. Fuel commission: RM2 per fuel record
            $fuelCommission = Fuel::whereBetween('fuel_date', [$dayStart, $dayEnd])
                ->whereNotNull('handled_by')
                ->count() * 2;

            // 4. Runner commission: RM2 per pickup/return at non-HASTA HQ Office locations
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();

            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $bookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;

            // Total expenses = staff commission + maintenance cost
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

            // Profit = earnings - expenses
            $profit = $totalEarnings - $totalExpenses;

            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dateFormatted' => $currentDate->format('d M Y'),
                'totalEarnings' => $totalEarnings,
                'totalExpenses' => $totalExpenses,
                'profit' => $profit,
            ];

            $weekTotalEarnings += $totalEarnings;
            $weekTotalExpenses += $totalExpenses;
            $weekTotalProfit += $profit;

            $currentDate->addDay();
        }

        return view('admin.finance.index', [
            'activeTab' => 'weekly-income',
            'startDate' => $startDate,
            'days' => $days,
            'weekTotalEarnings' => $weekTotalEarnings,
            'weekTotalExpenses' => $weekTotalExpenses,
            'weekTotalProfit' => $weekTotalProfit,
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

            // Get bookings on this day
            $bookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])->get();

            // Total earning = rental_price (not deposit) + fine_amount
            $totalEarnings = $bookings->sum('rental_amount') + $bookings->sum('deposit_fine_amount');

            // Get maintenance expenses on this day
            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])
                ->sum('cost');

            // Calculate staff commission for this day:
            // 1. Booking commission: 10% of rental_price
            $bookingCommission = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->sum(DB::raw('rental_amount * 0.10'));

            // 2. Maintenance commission
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])
                ->whereNotNull('staffID')
                ->sum('commission_amount');

            // 3. Fuel commission: RM2 per fuel record
            $fuelCommission = Fuel::whereBetween('fuel_date', [$dayStart, $dayEnd])
                ->whereNotNull('handled_by')
                ->count() * 2;

            // 4. Runner commission: RM2 per pickup/return at non-HASTA HQ Office locations
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();

            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $bookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;

            // Total expenses = staff commission + maintenance cost
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

            // Profit = earnings - expenses
            $profit = $totalEarnings - $totalExpenses;

            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dateFormatted' => $currentDate->format('d M Y'),
                'totalEarnings' => $totalEarnings,
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

    public function exportPdf(Request $request)
    {
        $activeTab = $request->get('tab', 'expenses-profit');

        if ($activeTab === 'expenses-profit') {
            return $this->exportExpensesProfitPdf($request);
        } elseif ($activeTab === 'monthly-income') {
            return $this->exportMonthlyIncomePdf($request);
        } elseif ($activeTab === 'weekly-income') {
            return $this->exportWeeklyIncomePdf($request);
        } else {
            return $this->exportDailyIncomePdf($request);
        }
    }

    private function exportExpensesProfitPdf(Request $request)
    {
        $vehicleType = $request->get('vehicle_type', 'all');
        $selectedMonth = $request->get('month');
        $selectedYear = $request->get('year', date('Y'));
        
        $query = Vehicle::with(['owner', 'bookings.payments', 'maintenances', 'fuels', 'car', 'motorcycle']);
        
        if ($vehicleType === 'car') {
            $query->whereHas('car');
        } elseif ($vehicleType === 'motor') {
            $query->whereHas('motorcycle');
        }
        
        $vehicles = $query->get()
            ->map(function ($vehicle) use ($selectedMonth, $selectedYear) {
                $leasingPrice = $vehicle->owner->leasing_price ?? 0;
                
                $bookingsQuery = $vehicle->bookings()->where('booking_status', '!=', 'Cancelled');
                if ($selectedMonth) {
                    $bookingsQuery->whereMonth('rental_start_date', $selectedMonth);
                }
                if ($selectedYear) {
                    $bookingsQuery->whereYear('rental_start_date', $selectedYear);
                }
                $bookings = $bookingsQuery->get();
                
                $earnings = $bookings->flatMap(function ($booking) {
                    return $booking->payments()->where('payment_status', 'Verified')->get();
                })->sum('total_amount');
                
                $staffBookingCommission = $bookings->sum(function($booking) {
                    return ($booking->rental_amount ?? 0) * 0.10;
                });
                
                $maintenanceQuery = $vehicle->maintenances();
                if ($selectedMonth) {
                    $maintenanceQuery->whereMonth('service_date', $selectedMonth);
                }
                if ($selectedYear) {
                    $maintenanceQuery->whereYear('service_date', $selectedYear);
                }
                $maintenanceCommission = $maintenanceQuery->whereNotNull('staffID')->sum('commission_amount');
                $maintenanceCost = $maintenanceQuery->sum('cost');
                
                $fuelQuery = $vehicle->fuels();
                if ($selectedMonth) {
                    $fuelQuery->whereMonth('fuel_date', $selectedMonth);
                }
                if ($selectedYear) {
                    $fuelQuery->whereYear('fuel_date', $selectedYear);
                }
                $fuelCommission = $fuelQuery->whereNotNull('handled_by')->count() * 2;
                
                // Runner commission for this vehicle
                $runnerCommission = 0;
                $runnerBookingsQuery = $vehicle->bookings()
                    ->whereNotNull('staff_served')
                    ->where(function($q) {
                        $q->where(function($subQ) {
                            $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                                 ->whereNotNull('pickup_point');
                        })->orWhere(function($subQ) {
                            $subQ->where('return_point', '!=', 'HASTA HQ Office')
                                 ->whereNotNull('return_point');
                        });
                    });
                if ($selectedMonth) {
                    $runnerBookingsQuery->whereMonth('rental_start_date', $selectedMonth);
                }
                if ($selectedYear) {
                    $runnerBookingsQuery->whereYear('rental_start_date', $selectedYear);
                }
                $runnerBookings = $runnerBookingsQuery->get();
                foreach ($runnerBookings as $runnerBooking) {
                    if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                        $runnerCommission += 2;
                    }
                    if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                        $runnerCommission += 2;
                    }
                }
                
                $totalStaffCommission = $staffBookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;
                $totalExpenses = $maintenanceCost + $totalStaffCommission;
                $profit = $earnings - $totalExpenses - $leasingPrice;

                return [
                    'vehicleID' => $vehicle->vehicleID,
                    'vehicle' => ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? ''),
                    'plate_number' => $vehicle->plate_number ?? 'N/A',
                    'leasing_price' => $leasingPrice,
                    'maintenance_expenses' => $maintenanceCost,
                    'staff_expenses' => $totalStaffCommission,
                    'profit' => $profit,
                ];
            })
            ->sortBy('vehicleID');

        $totalVehicles = $vehicles->count();
        $totalProfit = $vehicles->sum('profit');

        $pdf = Pdf::loadView('admin.finance.pdf.expenses-profit', [
            'vehicles' => $vehicles,
            'vehicleType' => $vehicleType,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'totalVehicles' => $totalVehicles,
            'totalProfit' => $totalProfit,
        ]);

        $filename = 'expenses_profit_' . $selectedYear . ($selectedMonth ? '_' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) : '') . '.pdf';
        return $pdf->download($filename);
    }

    private function exportMonthlyIncomePdf(Request $request)
    {
        $selectedYear = $request->get('year', date('Y'));
        $months = [];
        $yearTotalRentals = 0;
        $yearTotalExpenses = 0;
        $yearTotalEarnings = 0;
        $yearTotalProfit = 0;

        for ($month = 1; $month <= 12; $month++) {
            $dateFrom = Carbon::create($selectedYear, $month, 1)->startOfMonth();
            $dateTo = Carbon::create($selectedYear, $month, 1)->endOfMonth();

            $bookings = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])
                ->where('booking_status', '!=', 'Cancelled')
                ->get();

            $totalRentalsCount = $bookings->count();
            $totalEarnings = $bookings->sum('rental_amount');
            $totalEarnings += $bookings->sum('deposit_fine_amount');

            $staffBookingCommission = $bookings->sum(function($booking) {
                return ($booking->rental_amount ?? 0) * 0.10;
            });

            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dateFrom, $dateTo])->sum('cost');
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dateFrom, $dateTo])->whereNotNull('staffID')->sum('commission_amount');

            $fuelCommission = Fuel::whereBetween('fuel_date', [$dateFrom, $dateTo])->whereNotNull('handled_by')->count() * 2;

            // Runner commission
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dateFrom, $dateTo])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();
            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $staffBookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

            $profit = $totalEarnings - $totalExpenses;

            $months[] = [
                'month' => $month,
                'monthName' => $dateFrom->format('F'),
                'totalRentals' => $totalRentalsCount,
                'totalExpenses' => $totalExpenses,
                'totalEarnings' => $totalEarnings,
                'profit' => $profit,
            ];

            $yearTotalRentals += $totalRentalsCount;
            $yearTotalExpenses += $totalExpenses;
            $yearTotalEarnings += $totalEarnings;
            $yearTotalProfit += $profit;
        }

        $pdf = Pdf::loadView('admin.finance.pdf.monthly-income', [
            'selectedYear' => $selectedYear,
            'months' => $months,
            'yearTotalRentals' => $yearTotalRentals,
            'yearTotalExpenses' => $yearTotalExpenses,
            'yearTotalEarnings' => $yearTotalEarnings,
            'yearTotalProfit' => $yearTotalProfit,
        ]);

        $filename = 'monthly_income_' . $selectedYear . '.pdf';
        return $pdf->download($filename);
    }

    private function exportWeeklyIncomePdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $startOfWeek = Carbon::parse($startDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6);

        $week = [];
        $currentDate = $startOfWeek->copy();

        $weekTotalRentals = 0;
        $weekTotalExpenses = 0;
        $weekTotalEarnings = 0;
        $weekTotalProfit = 0;

        while ($currentDate->lte($endOfWeek)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            $bookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->get();

            $totalRentalsCount = $bookings->count();
            $totalEarnings = $bookings->sum('rental_amount');
            $totalEarnings += $bookings->sum('deposit_fine_amount');

            $staffBookingCommission = $bookings->sum(function($booking) {
                return ($booking->rental_amount ?? 0) * 0.10;
            });

            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])->sum('cost');
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])->whereNotNull('staffID')->sum('commission_amount');

            $fuelCommission = Fuel::whereBetween('fuel_date', [$dayStart, $dayEnd])->whereNotNull('handled_by')->count() * 2;

            // Runner commission
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();
            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $staffBookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

            $profit = $totalEarnings - $totalExpenses;

            $week[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dateFormatted' => $currentDate->format('d M Y'),
                'totalRentals' => $totalRentalsCount,
                'totalEarnings' => $totalEarnings,
                'totalExpenses' => $totalExpenses,
                'profit' => $profit,
            ];

            $weekTotalRentals += $totalRentalsCount;
            $weekTotalExpenses += $totalExpenses;
            $weekTotalEarnings += $totalEarnings;
            $weekTotalProfit += $profit;

            $currentDate->addDay();
        }

        $pdf = Pdf::loadView('admin.finance.pdf.weekly-income', [
            'startDate' => $startOfWeek->format('Y-m-d'),
            'endDate' => $endOfWeek->format('Y-m-d'),
            'week' => $week,
            'weekTotalRentals' => $weekTotalRentals,
            'weekTotalExpenses' => $weekTotalExpenses,
            'weekTotalEarnings' => $weekTotalEarnings,
            'weekTotalProfit' => $weekTotalProfit,
        ]);

        $filename = 'weekly_income_' . $startDate . '.pdf';
        return $pdf->download($filename);
    }

    private function exportDailyIncomePdf(Request $request)
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

            $bookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->get();

            $totalEarnings = $bookings->sum('rental_amount');
            $totalEarnings += $bookings->sum('deposit_fine_amount');

            $staffBookingCommission = $bookings->sum(function($booking) {
                return ($booking->rental_amount ?? 0) * 0.10;
            });

            $maintenanceExpenses = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])->sum('cost');
            $maintenanceCommission = VehicleMaintenance::whereBetween('service_date', [$dayStart, $dayEnd])->whereNotNull('staffID')->sum('commission_amount');

            $fuelCommission = Fuel::whereBetween('fuel_date', [$dayStart, $dayEnd])->whereNotNull('handled_by')->count() * 2;

            // Runner commission
            $runnerCommission = 0;
            $runnerBookings = Booking::whereBetween('rental_start_date', [$dayStart, $dayEnd])
                ->whereNotNull('staff_served')
                ->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('pickup_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('pickup_point');
                    })->orWhere(function($subQ) {
                        $subQ->where('return_point', '!=', 'HASTA HQ Office')
                             ->whereNotNull('return_point');
                    });
                })
                ->get();
            foreach ($runnerBookings as $runnerBooking) {
                if ($runnerBooking->pickup_point && $runnerBooking->pickup_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
                if ($runnerBooking->return_point && $runnerBooking->return_point !== 'HASTA HQ Office') {
                    $runnerCommission += 2;
                }
            }

            $totalStaffCommission = $staffBookingCommission + $maintenanceCommission + $fuelCommission + $runnerCommission;
            $totalExpenses = $maintenanceExpenses + $totalStaffCommission;

            $profit = $totalEarnings - $totalExpenses;

            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dateFormatted' => $currentDate->format('d M Y'),
                'totalEarnings' => $totalEarnings,
                'totalExpenses' => $totalExpenses,
                'profit' => $profit,
            ];

            $currentDate->addDay();
        }

        $pdf = Pdf::loadView('admin.finance.pdf.daily-income', [
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'days' => $days,
        ]);

        $filename = 'daily_income_' . $selectedYear . '_' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export finance as Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $activeTab = $request->get('tab', 'expenses-profit');
        $data = [];

        if ($activeTab === 'expenses-profit') {
            $vehicleType = $request->get('vehicle_type', 'all');
            $selectedMonth = $request->get('month');
            $selectedYear = $request->get('year', date('Y'));
            
            $dateFrom = Carbon::create($selectedYear, $selectedMonth ?? date('m'), 1)->startOfMonth();
            $dateTo = Carbon::create($selectedYear, $selectedMonth ?? date('m'), 1)->endOfMonth();
            
            $query = Vehicle::with(['owner', 'bookings.payments', 'maintenances', 'fuels']);
            
            if ($vehicleType === 'car') {
                $query->whereHas('car');
            } elseif ($vehicleType === 'motor') {
                $query->whereHas('motorcycle');
            }
            
            $vehicles = $query->get();
            
            foreach ($vehicles as $vehicle) {
                $leasingPrice = $vehicle->owner->leasing_price ?? 0;
                $earnings = $vehicle->bookings()
                    ->whereBetween('rental_start_date', [$dateFrom, $dateTo])
                    ->sum('rental_amount');
                $maintenanceExpenses = $vehicle->maintenances()
                    ->whereBetween('service_date', [$dateFrom, $dateTo])
                    ->sum('cost');
                
                $data[] = [
                    'Vehicle Plate' => $vehicle->plate_number ?? 'N/A',
                    'Owner' => $vehicle->owner->name ?? 'N/A',
                    'Leasing Price' => number_format($leasingPrice, 2),
                    'Earnings' => number_format($earnings, 2),
                    'Maintenance Expenses' => number_format($maintenanceExpenses, 2),
                    'Profit' => number_format($earnings - $leasingPrice - $maintenanceExpenses, 2),
                ];
            }
        } else {
            // For income tabs, export simplified data
            $data[] = [
                'Note' => 'Excel export for ' . $activeTab . ' tab is available in PDF format. Please use PDF export for detailed reports.',
            ];
        }

        $filename = 'finance-export-' . $activeTab . '-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
