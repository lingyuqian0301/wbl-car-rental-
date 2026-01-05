<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;
use App\Models\PersonDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
}
