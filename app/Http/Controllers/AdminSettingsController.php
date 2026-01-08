<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;
use App\Models\PersonDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'admin');

        if ($activeTab === 'admin') {
            $admins = Admin::with(['user', 'personDetails'])
                ->orderBy('adminID', 'asc')
                ->get();

            $today = Carbon::today();
            $totalAdmins = Admin::count();
            $activeAdmins = Admin::whereHas('user', function($q) {
                $q->where('isActive', true);
            })->count();

            return view('admin.settings.index', [
                'activeTab' => $activeTab,
                'admins' => $admins,
                'today' => $today,
                'totalAdmins' => $totalAdmins,
                'activeAdmins' => $activeAdmins,
            ]);
        } else {
            $filterActive = $request->get('filter_active', 'all');
            $filterType = $request->get('filter_type', 'all');

            $query = Staff::with(['user', 'personDetails', 'staffIt', 'runner']);

            if ($filterActive !== 'all') {
                $isActive = $filterActive === 'active';
                $query->whereHas('user', function($q) use ($isActive) {
                    $q->where('isActive', $isActive);
                });
            }

            if ($filterType !== 'all') {
                if ($filterType === 'staffit') {
                    $query->whereHas('staffIt');
                } elseif ($filterType === 'runner') {
                    $query->whereHas('runner');
                }
            }

            $staffs = $query->orderBy('staffID', 'asc')->get();

            $today = Carbon::today();
            $totalStaffs = Staff::count();
            $activeStaffs = Staff::whereHas('user', function($q) {
                $q->where('isActive', true);
            })->count();

            return view('admin.settings.index', [
                'activeTab' => $activeTab,
                'staffs' => $staffs,
                'filterActive' => $filterActive,
                'filterType' => $filterType,
                'today' => $today,
                'totalStaffs' => $totalStaffs,
                'activeStaffs' => $activeStaffs,
            ]);
        }
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:user,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'DOB' => 'required|date',
            'ic_no' => 'required|string|max:20|unique:admin,ic_no',
        ]);

        DB::beginTransaction();
        try {
            $now = now();
            $user = User::create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'DOB' => $validated['DOB'],
                'age' => Carbon::parse($validated['DOB'])->age,
                'dateRegistered' => $now,
                'lastLogin' => null,
                'isActive' => true,
            ]);

            PersonDetails::firstOrCreate([
                'ic_no' => $validated['ic_no'],
            ], [
                'ic_no' => $validated['ic_no'],
                'fullname' => $validated['name'],
            ]);

            Admin::create([
                'userID' => $user->userID,
                'ic_no' => $validated['ic_no'],
            ]);

            DB::commit();
            return redirect()->route('admin.settings.index', ['tab' => 'admin'])
                ->with('success', 'Admin created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create admin: ' . $e->getMessage());
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:user,username,' . $user->userID . ',userID',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email,' . $user->userID . ',userID',
            'phone' => 'required|string|max:20',
            'DOB' => 'required|date',
            'ic_no' => 'required|string|max:20|unique:admin,ic_no,' . $admin->adminID . ',adminID',
            'isActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'DOB' => $validated['DOB'],
                'age' => Carbon::parse($validated['DOB'])->age,
                'isActive' => $request->has('isActive'),
            ]);

            PersonDetails::updateOrCreate(
                ['ic_no' => $validated['ic_no']],
                ['fullname' => $validated['name']]
            );

            $admin->update([
                'ic_no' => $validated['ic_no'],
            ]);

            DB::commit();
            return redirect()->route('admin.settings.index', ['tab' => 'admin'])
                ->with('success', 'Admin updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update admin: ' . $e->getMessage());
        }
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:user,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'DOB' => 'required|date',
            'ic_no' => 'required|string|max:20|unique:staff,ic_no',
            'staff_type' => 'required|in:staffit,runner',
        ]);

        DB::beginTransaction();
        try {
            $now = now();
            $user = User::create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'DOB' => $validated['DOB'],
                'age' => Carbon::parse($validated['DOB'])->age,
                'dateRegistered' => $now,
                'lastLogin' => null,
                'isActive' => true,
            ]);

            PersonDetails::firstOrCreate([
                'ic_no' => $validated['ic_no'],
            ], [
                'ic_no' => $validated['ic_no'],
                'fullname' => $validated['name'],
            ]);

            $staff = Staff::create([
                'userID' => $user->userID,
                'ic_no' => $validated['ic_no'],
            ]);

            // Create staff type record
            if ($validated['staff_type'] === 'staffit') {
                \App\Models\StaffIT::create(['staffID' => $staff->staffID]);
            } else {
                \App\Models\Runner::create(['staffID' => $staff->staffID]);
            }

            DB::commit();
            return redirect()->route('admin.settings.index', ['tab' => 'staff'])
                ->with('success', 'Staff created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create staff: ' . $e->getMessage());
        }
    }

    public function updateStaff(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $user = $staff->user;

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:user,username,' . $user->userID . ',userID',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email,' . $user->userID . ',userID',
            'phone' => 'required|string|max:20',
            'DOB' => 'required|date',
            'ic_no' => 'required|string|max:20|unique:staff,ic_no,' . $staff->staffID . ',staffID',
            'staff_type' => 'required|in:staffit,runner',
            'isActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'DOB' => $validated['DOB'],
                'age' => Carbon::parse($validated['DOB'])->age,
                'isActive' => $request->has('isActive'),
            ]);

            PersonDetails::updateOrCreate(
                ['ic_no' => $validated['ic_no']],
                ['fullname' => $validated['name']]
            );

            $staff->update([
                'ic_no' => $validated['ic_no'],
            ]);

            // Update staff type
            $existingStaffIT = $staff->staffIt;
            $existingRunner = $staff->runner;

            if ($validated['staff_type'] === 'staffit') {
                if (!$existingStaffIT) {
                    \App\Models\StaffIT::create(['staffID' => $staff->staffID]);
                }
                if ($existingRunner) {
                    $existingRunner->delete();
                }
            } else {
                if (!$existingRunner) {
                    \App\Models\Runner::create(['staffID' => $staff->staffID]);
                }
                if ($existingStaffIT) {
                    $existingStaffIT->delete();
                }
            }

            DB::commit();
            return redirect()->route('admin.settings.index', ['tab' => 'staff'])
                ->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
    }

    public function showStaff(Staff $staff, Request $request): View
    {
        $activeTab = $request->get('tab', 'staff-detail');
        
        $staff->load([
            'user',
            'personDetails',
            'staffIt',
            'runner'
        ]);

        // For tasks handled tab (only for staffit)
        $tasks = collect();
        $totalCommission = 0;
        $taskCount = 0;
        $filterMonth = $request->get('filter_month', date('m'));
        $filterYear = $request->get('filter_year', date('Y'));
        $filterTaskType = $request->get('filter_task_type', '');

        if ($staff->staffIt && $activeTab === 'tasks-handled') {
            // Get tasks from maintenance, fuel, and other sources
            // This is a placeholder - you'll need to create a tasks table or use existing tables
            // For now, we'll use maintenance and fuel records
            $maintenanceTasks = \App\Models\VehicleMaintenance::where('staffID', $staff->user->userID)
                ->whereMonth('service_date', $filterMonth)
                ->whereYear('service_date', $filterYear)
                ->when($filterTaskType === 'maintenance', function($q) {
                    // Already filtered
                })
                ->when($filterTaskType && $filterTaskType !== 'maintenance', function($q) {
                    $q->whereRaw('1 = 0'); // No results
                })
                ->get()
                ->map(function($m) {
                    return [
                        'task_date' => $m->service_date,
                        'task_type' => 'Maintenance',
                        'description' => $m->service_type . ($m->description ? ': ' . $m->description : ''),
                        'commission_amount' => $m->cost * 0.1, // 10% commission example
                    ];
                });

            $fuelTasks = \App\Models\Fuel::where('handled_by', $staff->user->userID)
                ->whereMonth('fuel_date', $filterMonth)
                ->whereYear('fuel_date', $filterYear)
                ->when($filterTaskType === 'fuel', function($q) {
                    // Already filtered
                })
                ->when($filterTaskType && $filterTaskType !== 'fuel', function($q) {
                    $q->whereRaw('1 = 0'); // No results
                })
                ->get()
                ->map(function($f) {
                    return [
                        'task_date' => $f->fuel_date,
                        'task_type' => 'Fuel',
                        'description' => 'Fuel refill',
                        'commission_amount' => $f->cost * 0.05, // 5% commission example
                    ];
                });

            $tasks = $maintenanceTasks->merge($fuelTasks)->sortByDesc('task_date');
            $totalCommission = $tasks->sum('commission_amount');
            $taskCount = $tasks->count();
        }

        // For login logs tab
        $loginLogs = collect();
        $totalOnlineTime = 0;
        
        if ($activeTab === 'login-logs') {
            // This would require a login_logs table
            // For now, we'll use a placeholder structure
            // You'll need to create a migration for login_logs table
            $loginLogs = collect([
                // Placeholder data structure
            ]);
        }

        return view('admin.settings.staff.show', [
            'staff' => $staff,
            'activeTab' => $activeTab,
            'tasks' => $tasks,
            'totalCommission' => $totalCommission,
            'taskCount' => $taskCount,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'filterTaskType' => $filterTaskType,
            'loginLogs' => $loginLogs,
            'totalOnlineTime' => $totalOnlineTime,
        ]);
    }

    public function storeTask(Request $request, Staff $staff)
    {
        if (!$staff->staffIt) {
            return redirect()->back()->with('error', 'Only Staff IT can have tasks.');
        }

        $validated = $request->validate([
            'task_date' => 'required|date',
            'task_type' => 'required|in:maintenance,fuel,reception,other',
            'description' => 'nullable|string',
            'commission_amount' => 'required|numeric|min:0',
        ]);

        try {
            // Check if tasks table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('tasks')) {
                // Create tasks table if it doesn't exist
                \Illuminate\Support\Facades\Schema::create('tasks', function ($table) {
                    $table->id('taskID');
                    $table->unsignedInteger('staffID');
                    $table->unsignedInteger('userID');
                    $table->date('task_date');
                    $table->string('task_type', 50);
                    $table->text('description')->nullable();
                    $table->decimal('commission_amount', 10, 2);
                    $table->timestamps();
                });
            }

            DB::table('tasks')->insert([
                'staffID' => $staff->staffID,
                'userID' => $staff->user->userID,
                'task_date' => $validated['task_date'],
                'task_type' => $validated['task_type'],
                'description' => $validated['description'] ?? null,
                'commission_amount' => $validated['commission_amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Task added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add task: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, Staff $staff)
    {
        $filterMonth = $request->get('month', date('m'));
        $filterYear = $request->get('year', date('Y'));
        $filterTaskType = $request->get('type', '');

        // Get tasks similar to showStaff method
        $tasks = collect();
        
        if ($staff->staffIt) {
            $maintenanceTasks = \App\Models\VehicleMaintenance::where('staffID', $staff->user->userID)
                ->whereMonth('service_date', $filterMonth)
                ->whereYear('service_date', $filterYear)
                ->when($filterTaskType === 'maintenance', function($q) {})
                ->when($filterTaskType && $filterTaskType !== 'maintenance', function($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get()
                ->map(function($m) {
                    return [
                        'task_date' => $m->service_date,
                        'task_type' => 'Maintenance',
                        'description' => $m->service_type . ($m->description ? ': ' . $m->description : ''),
                        'commission_amount' => $m->cost * 0.1,
                    ];
                });

            $fuelTasks = \App\Models\Fuel::where('handled_by', $staff->user->userID)
                ->whereMonth('fuel_date', $filterMonth)
                ->whereYear('fuel_date', $filterYear)
                ->when($filterTaskType === 'fuel', function($q) {})
                ->when($filterTaskType && $filterTaskType !== 'fuel', function($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get()
                ->map(function($f) {
                    return [
                        'task_date' => $f->fuel_date,
                        'task_type' => 'Fuel',
                        'description' => 'Fuel refill',
                        'commission_amount' => $f->cost * 0.05,
                    ];
                });

            $tasks = $maintenanceTasks->merge($fuelTasks)->sortByDesc('task_date');
        }

        // Generate Excel export
        $filename = 'staff_tasks_' . $staff->staffID . '_' . $filterYear . '_' . str_pad($filterMonth, 2, '0', STR_PAD_LEFT) . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($tasks, $staff) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Task Date', 'Task Type', 'Description', 'Commission Amount (RM)']);
            
            foreach ($tasks as $task) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($task['task_date'])->format('Y-m-d'),
                    $task['task_type'],
                    $task['description'],
                    number_format($task['commission_amount'], 2),
                ]);
            }
            
            fputcsv($file, ['', '', 'Total Commission:', number_format($tasks->sum('commission_amount'), 2)]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request, Staff $staff)
    {
        $filterMonth = $request->get('month', date('m'));
        $filterYear = $request->get('year', date('Y'));
        $filterTaskType = $request->get('type', '');

        // Get tasks similar to showStaff method
        $tasks = collect();
        $totalCommission = 0;
        
        if ($staff->staffIt) {
            $maintenanceTasks = \App\Models\VehicleMaintenance::where('staffID', $staff->user->userID)
                ->whereMonth('service_date', $filterMonth)
                ->whereYear('service_date', $filterYear)
                ->when($filterTaskType === 'maintenance', function($q) {})
                ->when($filterTaskType && $filterTaskType !== 'maintenance', function($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get()
                ->map(function($m) {
                    return [
                        'task_date' => $m->service_date,
                        'task_type' => 'Maintenance',
                        'description' => $m->service_type . ($m->description ? ': ' . $m->description : ''),
                        'commission_amount' => $m->cost * 0.1,
                    ];
                });

            $fuelTasks = \App\Models\Fuel::where('handled_by', $staff->user->userID)
                ->whereMonth('fuel_date', $filterMonth)
                ->whereYear('fuel_date', $filterYear)
                ->when($filterTaskType === 'fuel', function($q) {})
                ->when($filterTaskType && $filterTaskType !== 'fuel', function($q) {
                    $q->whereRaw('1 = 0');
                })
                ->get()
                ->map(function($f) {
                    return [
                        'task_date' => $f->fuel_date,
                        'task_type' => 'Fuel',
                        'description' => 'Fuel refill',
                        'commission_amount' => $f->cost * 0.05,
                    ];
                });

            $tasks = $maintenanceTasks->merge($fuelTasks)->sortByDesc('task_date');
            $totalCommission = $tasks->sum('commission_amount');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.settings.staff.export-pdf', [
            'staff' => $staff,
            'tasks' => $tasks,
            'totalCommission' => $totalCommission,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'filterTaskType' => $filterTaskType,
        ]);

        $filename = 'staff_tasks_' . $staff->staffID . '_' . $filterYear . '_' . str_pad($filterMonth, 2, '0', STR_PAD_LEFT) . '.pdf';
        
        return $pdf->download($filename);
    }
}
