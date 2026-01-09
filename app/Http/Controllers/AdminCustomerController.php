<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount('bookings')
            ->with([
                'user',
                'local',
                'international',
                'localStudent.studentDetails',
                'internationalStudent.studentDetails',
                'localUtmStaff',
                'internationalUtmStaff',
                'bookings' => function($q) {
                    $q->orderBy('rental_start_date', 'desc')->limit(1);
                }
            ]);

        // Search by ID, name, email, phone, matric no
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customerID', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('localStudent', function($lsQuery) use ($search) {
                      $lsQuery->where('matric_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by faculty (from studentdetails table)
        if ($request->filled('faculty')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('localStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('faculty', $request->faculty);
                })->orWhereHas('internationalStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('faculty', $request->faculty);
                });
            });
        }

        // Filter by college (from studentdetails table)
        if ($request->filled('college')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('localStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('college', $request->college);
                })->orWhereHas('internationalStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('college', $request->college);
                });
            });
        }

        // Filter by booking count
        if ($request->filled('booking_count')) {
            $query->having('bookings_count', '>=', $request->booking_count);
        }

        // Filter by customer status
        if ($request->filled('customer_status')) {
            if ($request->customer_status === 'blacklisted') {
                $query->where('customer_status', 'blacklist');
            } elseif ($request->customer_status === 'active') {
                $query->where('customer_status', 'active');
            } elseif ($request->customer_status === 'deleted') {
                $query->where('customer_status', 'deleted');
            }
        }

        // Filter by customer nation (international/local)
        if ($request->filled('customer_nation')) {
            if ($request->customer_nation === 'local') {
                $query->whereHas('local');
            } elseif ($request->customer_nation === 'international') {
                $query->whereHas('international');
            }
        }

        // Filter by customer type (student/staff)
        if ($request->filled('customer_type')) {
            if ($request->customer_type === 'student') {
                $query->where(function($q) {
                    $q->whereHas('localStudent')
                      ->orWhereHas('internationalStudent');
                });
            } elseif ($request->customer_type === 'staff') {
                $query->where(function($q) {
                    $q->whereHas('localUtmStaff')
                      ->orWhereHas('internationalUtmStaff');
                });
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name_asc');
        switch ($sortBy) {
            case 'name_asc':
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'ASC')
                      ->select('customer.*');
                break;
            case 'name_desc':
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'DESC')
                      ->select('customer.*');
                break;
            case 'latest_booking':
                $query->orderByRaw('(SELECT MAX(COALESCE(rental_start_date, start_date)) FROM booking WHERE booking.customerID = customer.customerID) DESC');
                break;
            case 'highest_rental':
                $query->orderBy('bookings_count', 'desc');
                break;
            default:
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'ASC')
                      ->select('customer.*');
        }

        $customers = $query->paginate(20)->withQueryString();

        // Get unique faculties and colleges from StudentDetails table
        $faculties = \App\Models\StudentDetails::distinct()->pluck('faculty')->filter()->sort()->values();
        $colleges = \App\Models\StudentDetails::distinct()->pluck('college')->filter()->sort()->values();

        // Summary stats for header
        $totalCustomers = Customer::count();
        $totalCustomersToday = Customer::whereHas('user', function($q) {
            $q->whereDate('dateRegistered', now());
        })->count();
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
            'search' => $request->get('search'),
            'sortBy' => $request->get('sort_by', 'name_asc'),
            'faculty' => $request->get('faculty'),
            'college' => $request->get('college'),
            'bookingCount' => $request->get('booking_count'),
            'customerStatus' => $request->get('customer_status'),
            'customerNation' => $request->get('customer_nation'),
            'customerType' => $request->get('customer_type'),
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'nullable|string|max:255',
            'matric_number' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'customer_type' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'customer_license' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create User first
            $user = \App\Models\User::create([
                'username' => $validated['email'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'dateRegistered' => $validated['registration_date'] ?? now(),
                'isActive' => true,
            ]);

            // Create Customer
            $customer = Customer::create([
                'userID' => $user->userID,
                'phone_number' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'customer_license' => $validated['customer_license'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]);

            // Create PersonDetails if IC number provided
            if (!empty($validated['ic_number'])) {
                \App\Models\PersonDetails::firstOrCreate(
                    ['ic_no' => $validated['ic_number']],
                    ['fullname' => $validated['name']]
                );
            }

            DB::commit();

        return redirect()->route('admin.manage.client')
            ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'user',
            'local',
            'international',
            'localStudent.studentDetails',
            'internationalStudent.studentDetails',
            'localUtmStaff.staffDetails',
            'internationalUtmStaff.staffDetails',
            'bookings' => function($q) {
                $q->with(['vehicle', 'payments'])
                  ->orderBy('rental_start_date', 'desc');
            }
        ]);

        // Calculate booking statistics
        $totalBookings = $customer->bookings->count();
        $totalOutstanding = 0;
        $totalWalletAmount = 0;

        foreach ($customer->bookings as $booking) {
            $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
            $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
            $outstanding = max(0, $totalRequired - $totalPaid);
            $totalOutstanding += $outstanding;
            
            // Abstract wallet amount (assuming it's refunded amounts or credits)
            $refundedAmount = $booking->payments->where('payment_status', 'Refunded')->sum('total_amount');
            $totalWalletAmount += abs($refundedAmount);
        }
        
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.customers.show' : 'admin.customers.show';
        return view($viewName, [
            'customer' => $customer,
            'totalBookings' => $totalBookings,
            'totalOutstanding' => $totalOutstanding,
            'totalWalletAmount' => $totalWalletAmount,
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

            return redirect()->route('admin.manage.client')
                ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        // Check if customer has bookings
        if ($customer->bookings()->count() > 0) {
            return redirect()->route('admin.manage.client')
                ->with('error', 'Cannot delete customer with existing bookings.');
        }

        // Mark as deleted instead of actually deleting
        $customer->update([
            'customer_status' => 'deleted',
        ]);

        return redirect()->route('admin.manage.client')
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

        return redirect()->route('admin.manage.client')
            ->with('success', count($selectedIds) . ' customer(s) deleted successfully.');
    }

    public function exportPdf(Request $request)
    {
        // Apply same filters as index
        $query = $this->buildQuery($request);
        $customers = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.customers.export-pdf', [
            'customers' => $customers,
            'filters' => $request->all(),
        ]);

        return $pdf->download('customers-export-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Apply same filters as index
        $query = $this->buildQuery($request);
        $customers = $query->get();

        $data = $customers->map(function($customer) {
            $localStudentDetails = $customer->localStudent->studentDetails ?? null;
            $internationalStudentDetails = $customer->internationalStudent->studentDetails ?? null;
            
            return [
                'Customer ID' => $customer->customerID,
                'Name' => $customer->user->name ?? 'N/A',
                'Email' => $customer->user->email ?? 'N/A',
                'Phone' => $customer->user->phone ?? 'N/A',
                'Matric Number' => $customer->localStudent->matric_number ?? ($customer->internationalStudent->matric_number ?? 'N/A'),
                'Address' => $customer->address ?? 'N/A',
                'State/Country' => $customer->local->stateOfOrigin ?? $customer->international->countryOfOrigin ?? 'N/A',
                'IC/Passport' => $customer->local->ic_no ?? $customer->international->passport_no ?? 'N/A',
                'Emergency Contact' => $customer->emergency_contact ?? 'N/A',
                'License' => $customer->customer_license ?? 'N/A',
                'College' => $localStudentDetails->college ?? ($internationalStudentDetails->college ?? 'N/A'),
                'Faculty' => $localStudentDetails->faculty ?? ($internationalStudentDetails->faculty ?? 'N/A'),
                'Programme' => $localStudentDetails->programme ?? ($internationalStudentDetails->programme ?? 'N/A'),
                'Year of Study' => $localStudentDetails->yearOfStudy ?? ($internationalStudentDetails->yearOfStudy ?? 'N/A'),
                'Booking Count' => $customer->bookings_count ?? 0,
                'Latest Booking' => $customer->bookings->first()?->rental_start_date?->format('Y-m-d') ?? 'N/A',
                'Status' => $customer->customer_status ?? 'active',
                'Is Active' => ($customer->user->isActive ?? false) ? 'Yes' : 'No',
            ];
        });

        $filename = 'customers-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery(Request $request)
    {
        $query = Customer::withCount('bookings')
            ->with([
                'user',
                'local',
                'international',
                'localStudent.studentDetails',
                'internationalStudent.studentDetails',
                'localUtmStaff',
                'internationalUtmStaff',
                'bookings' => function($q) {
                    $q->orderBy('rental_start_date', 'desc')->limit(1);
                }
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customerID', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('localStudent', function($lsQuery) use ($search) {
                      $lsQuery->where('matric_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('faculty')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('localStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('faculty', $request->faculty);
                })->orWhereHas('internationalStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('faculty', $request->faculty);
                });
            });
        }

        if ($request->filled('college')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('localStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('college', $request->college);
                })->orWhereHas('internationalStudent.studentDetails', function($sdQuery) use ($request) {
                    $sdQuery->where('college', $request->college);
                });
            });
        }

        if ($request->filled('booking_count')) {
            $query->having('bookings_count', '>=', $request->booking_count);
        }

        if ($request->filled('customer_status')) {
            if ($request->customer_status === 'blacklisted') {
                $query->where('customer_status', 'blacklist');
            } elseif ($request->customer_status === 'active') {
                $query->where('customer_status', 'active');
            } elseif ($request->customer_status === 'deleted') {
                $query->where('customer_status', 'deleted');
            }
        }

        if ($request->filled('customer_nation')) {
            if ($request->customer_nation === 'local') {
                $query->whereHas('local');
            } elseif ($request->customer_nation === 'international') {
                $query->whereHas('international');
            }
        }

        if ($request->filled('customer_type')) {
            if ($request->customer_type === 'student') {
                $query->where(function($q) {
                    $q->whereHas('localStudent')
                      ->orWhereHas('internationalStudent');
                });
            } elseif ($request->customer_type === 'staff') {
                $query->where(function($q) {
                    $q->whereHas('localUtmStaff')
                      ->orWhereHas('internationalUtmStaff');
                });
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name_asc');
        switch ($sortBy) {
            case 'name_asc':
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'ASC')
                      ->select('customer.*');
                break;
            case 'name_desc':
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'DESC')
                      ->select('customer.*');
                break;
            case 'latest_booking':
                $query->orderByRaw('(SELECT MAX(COALESCE(rental_start_date, start_date)) FROM booking WHERE booking.customerID = customer.customerID) DESC');
                break;
            case 'highest_rental':
                $query->orderBy('bookings_count', 'desc');
                break;
            default:
                $query->leftJoin('user', 'customer.userID', '=', 'user.userID')
                      ->orderBy('user.name', 'ASC')
                      ->select('customer.*');
        }

        return $query;
    }

    public function uploadLicense(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'license_img' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:5120', // 5MB max
        ]);

        try {
            if ($request->hasFile('license_img')) {
                $file = $request->file('license_img');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('customer_documents', $fileName, 'public');

                // Update license image in customer table
                $customer->update([
                    'customer_license_img' => $filePath,
                ]);

                return redirect()->back()->with('success', 'License uploaded successfully.');
            }

            return redirect()->back()->with('error', 'No file uploaded.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload license: ' . $e->getMessage());
        }
    }

    public function uploadIc(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'ic_img' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:5120', // 5MB max
        ]);

        try {
            if ($request->hasFile('ic_img')) {
                $file = $request->file('ic_img');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('customer_documents', $fileName, 'public');

                // Update IC image in local table if customer is local
                if ($customer->local) {
                    $customer->local->update([
                        'ic_img' => $filePath,
                    ]);
                } else {
                    // For international customers, passport image might be stored differently
                    // You may need to adjust this based on your database structure
                    return redirect()->back()->with('error', 'IC/Passport upload is only available for local customers.');
                }

                return redirect()->back()->with('success', ($customer->local ? 'IC' : 'Passport') . ' uploaded successfully.');
            }

            return redirect()->back()->with('error', 'No file uploaded.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload ' . ($customer->local ? 'IC' : 'Passport') . ': ' . $e->getMessage());
        }
    }
}






