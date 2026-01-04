<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount('bookings')
            ->with(['bookings' => function($q) {
                $q->orderBy('rental_start_date', 'desc')->limit(1);
            }]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('matric_number', 'like', "%{$search}%")
                  ->orWhere('customerID', 'like', "%{$search}%");
            });
        }

        // Additional filters
        if ($request->filled('customer_id')) {
            $query->where('customerID', $request->customer_id);
        }

        if ($request->filled('customer_name')) {
            $query->where('fullname', 'like', "%{$request->customer_name}%");
        }

        if ($request->filled('vehicle_id')) {
            $query->whereHas('bookings', function($q) use ($request) {
                $q->where('vehicleID', $request->vehicle_id);
            });
        }

        // Note: faculty and college are in studentdetails table, not customer table
        // Filters removed as they don't exist in customer table

        if ($request->filled('booking_count')) {
            $query->having('bookings_count', '>=', $request->booking_count);
        }

        if ($request->filled('booking_date_from')) {
            $query->whereHas('bookings', function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('rental_start_date', '>=', $request->booking_date_from)
                       ->orWhere('start_date', '>=', $request->booking_date_from);
                });
            });
        }

        if ($request->filled('booking_date_to')) {
            $query->whereHas('bookings', function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('rental_start_date', '<=', $request->booking_date_to)
                       ->orWhere('start_date', '<=', $request->booking_date_to);
                });
            });
        }

        if ($request->filled('blacklist_status')) {
            if ($request->blacklist_status === 'blacklisted') {
                $query->where('customer_status', 'blacklist');
            } elseif ($request->blacklist_status === 'active') {
                $query->where('customer_status', 'active');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name_asc');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('fullname', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('fullname', 'desc');
                break;
            case 'latest_booking':
                $query->orderByRaw('(SELECT MAX(COALESCE(rental_start_date, start_date)) FROM booking WHERE booking.user_id = customer.customerID) DESC');
                break;
            case 'highest_rental':
                $query->orderBy('bookings_count', 'desc');
                break;
            default:
                $query->orderBy('fullname', 'asc');
        }

        $customers = $query->paginate(20)->withQueryString();

        // Note: faculty and college are in studentdetails table, not customer table
        $faculties = collect([]);
        $colleges = collect([]);

        // Summary stats for header
        $totalCustomers = Customer::count();
        $totalCustomersToday = Customer::whereDate('dateRegistered', now())->count();
        $customersWithBookings = Customer::has('bookings')->count();

        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.index' : 'admin.customers.index';
        return view($viewName, [
            'customers' => $customers,
            'faculties' => $faculties,
            'colleges' => $colleges,
            'totalCustomers' => $totalCustomers,
            'totalCustomersToday' => $totalCustomersToday,
            'customersWithBookings' => $customersWithBookings,
            'today' => \Carbon\Carbon::today(),
        ]);
    }

    public function create(): View
    {
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.create' : 'admin.customers.create';
        return view($viewName);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matric_number' => 'nullable|string|max:255',
            'fullname' => 'required|string|max:255',
            'ic_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'customer_type' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'customer_license' => 'nullable|string|max:255',
        ]);

        $validated['registration_date'] = $validated['registration_date'] ?? now();

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['bookings', 'documents']);
        
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.show' : 'admin.customers.show';
        return view($viewName, [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): View
    {
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.edit' : 'admin.customers.edit';
        return view($viewName, [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'matric_number' => 'nullable|string|max:255',
            'fullname' => 'required|string|max:255',
            'ic_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'customer_type' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'customer_license' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        // Check if customer has bookings
        if ($customer->bookings()->count() > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with existing bookings.');
        }

        // Mark as deleted instead of actually deleting
        $customer->update([
            'customer_status' => 'deleted',
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer marked as deleted successfully.');
    }

    public function documents(Customer $customer, $documentType): View
    {
        $customer->load('documents');
        
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.documents' : 'admin.customers.documents';
        return view($viewName, [
            'customer' => $customer,
            'documentType' => $documentType,
        ]);
    }

    public function toggleBlacklist(Request $request, Customer $customer): RedirectResponse
    {
        $isBlacklisting = $customer->customer_status !== 'blacklist';
        
        $customer->update([
            'customer_status' => $isBlacklisting ? 'blacklist' : 'active',
        ]);

        return redirect()->back()->with('success', $isBlacklisting ? 'Customer has been blacklisted.' : 'Customer has been removed from blacklist.');
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        $selectedIds = $request->input('selected_customers', []);
        
        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No customers selected.');
        }

        $customers = Customer::whereIn('customerID', $selectedIds);
        
        // Check if any have bookings
        $withBookings = $customers->get()->filter(function($customer) {
            return $customer->bookings()->count() > 0;
        });

        if ($withBookings->count() > 0) {
            return redirect()->back()->with('error', 'Some selected customers have bookings and cannot be deleted.');
        }

        $customers->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', count($selectedIds) . ' customer(s) deleted successfully.');
    }
}






