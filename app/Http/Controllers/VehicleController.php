<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
// use App\Models\OwnerCar;
// use App\Models\VehicleDocument;
use Illuminate\Http\Request;

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
            // Return empty if no filters
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

        return view('welcome', compact('cars', 'vehicleTypes', 'brands'));
    }


    public function show($id)
    {
        $vehicle = Vehicle::with(['car', 'motorcycle'])->findOrFail($id);

        // Calculate blocked dates (active bookings)
        $bookings = \App\Models\Booking::where('vehicleID', $vehicle->vehicleID)
            ->where('booking_status', '!=', 'Cancelled')
            ->select('rental_start_date', 'rental_end_date')
            ->get();

        $blockedDates = [];
        foreach ($bookings as $booking) {
            $start = \Carbon\Carbon::parse($booking->rental_start_date);
            $end = \Carbon\Carbon::parse($booking->rental_end_date);
            while ($start->lte($end)) {
                $blockedDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        return view('vehicles.show', compact('vehicle', 'blockedDates'));
    }
    // public function store(Request $request)
//     {

    //         $car = Car::create([
//             'ownerID' => auth()->user()->ownerID,
//             'model' => $request->model,
//             'image' => $request->file('car_image')->store('cars', 'public'),
//             'approval_status' => 'pending'
//         ]);

    //         VehicleDocument::create([
//             'carID' => $car->carID,
//             'roadtax_image' => $request->file('roadtax')->store('documents', 'public'),
//             'insurance_image' => $request->file('insurance')->store('documents', 'public'),
//             'verification_status' => 'pending'
//         ]);

    //         return redirect()->back()->with('success', 'Car submitted for verification');

    //         // 1️⃣ Create owner (staff enters owner info)
//     $owner = OwnerCar::create([
//         'owner_name' => $request->owner_name,
//         'owner_phone' => $request->owner_phone,
//     ]);

    //     // 2️⃣ Create car (linked to owner)
//     $car = Car::create([
//         'ownerID' => $owner->ownerID,
//         'model' => $request->model,
//         'plate_number' => $request->plate_number,
//         'image' => $request->file('car_image')->store('cars', 'public'),
//         'approval_status' => 'pending'
//     ]);

    //     // 3️⃣ Create vehicle documents
//     VehicleDocument::create([
//         'carID' => $car->vehicleID,
//         'roadtax_image' => $request->file('roadtax')->store('documents', 'public'),
//         'insurance_image' => $request->file('insurance')->store('documents', 'public'),
//         'verification_status' => 'pending'
//     ]);

    //     return redirect()->route('home')->with('success', 'Car submitted for admin verification');

    //     }
// public function bookings()
// {
//     return $this->hasMany(Booking::class, 'car_id');
// }
}


