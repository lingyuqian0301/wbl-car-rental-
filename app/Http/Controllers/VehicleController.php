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
$query = Vehicle::with('car')->whereIn('isActive', [1, 'true']);
        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('vehicle_brand', $request->brand);
        }

        // Filter by car type (column is vehicleType not vehicle_type)
        if ($request->filled('vehicleType')) {
            $query->where('vehicleType', $request->vehicleType);
        }

        // 🔥 Date availability logic
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
        //         $request->validate([
//         'brand' => 'required|string',
//         'model' => 'required|string',
//         'type' => 'required|string',
//         'price_per_day' => 'required|numeric',
//         'car_browse_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
//     ]);

        //     Car::create([
//         'brand' => $request->brand,
//         'model' => $request->model,
//         'type' => $request->type,
//         'price_per_day' => $request->price_per_day,

        //         // ✅ THIS IS WHERE IT GOES
//         'car_browse_image' => $request->file('car_browse_image')
//              $request->file('car_browse_image')->store('cars', 'public')
//             : null,
//     ]);

        //     return redirect()->back()->with('success', 'Car added successfully');
//     }

        $cars = $query->get();

        // Get unique vehicle types and brands for filters
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
        $vehicle = Vehicle::findOrFail($id);

        return view('vehicles.show', compact('vehicle'));
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


