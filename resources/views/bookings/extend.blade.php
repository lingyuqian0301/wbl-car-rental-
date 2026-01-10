@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Reschedule Booking #{{ $booking->bookingID }}</h2>
        </div>
        
        <div class="p-6">
            {{-- Info Alert --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            By rescheduling, your existing deposit of <strong>RM {{ number_format($booking->payments->sum('total_amount'), 2) }}</strong> will be carried over to the new dates.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Vehicle Info --}}
            <div class="mb-6 p-4 bg-gray-50 rounded border">
                <h3 class="font-bold text-gray-700">Vehicle Details</h3>
                <p class="text-gray-600">{{ $booking->vehicle->vehicle_brand }} {{ $booking->vehicle->vehicle_model }} ({{ $booking->vehicle->plate_number }})</p>
            </div>

            {{-- Form --}}
            <form action="{{ route('bookings.process_extend', $booking->bookingID) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">New Start Date</label>
                        <input type="datetime-local" name="start_date" required
                               class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">New End Date</label>
                        <input type="datetime-local" name="end_date" required
                               class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('Y-m-d\TH:i') }}">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('bookings.show', $booking->bookingID) }}" class="text-gray-600 hover:text-gray-800 font-medium">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition duration-200">
                        Confirm New Dates
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection