<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reschedule Booking') }} #{{ $booking->bookingID }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    {{-- INFO ALERT --}}
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Rescheduling allows you to change your dates while <strong>keeping your current deposit/payment</strong> safe.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- VEHICLE INFO --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <div class="mt-1 p-3 bg-gray-100 rounded-md text-gray-900">
                            {{ $booking->vehicle->vehicle_brand }} {{ $booking->vehicle->vehicle_model }} ({{ $booking->vehicle->plate_number }})
                        </div>
                    </div>

                    {{-- FORM --}}
                    <form action="{{ route('bookings.process_extend', $booking->bookingID) }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Start Date</label>
                                <input type="datetime-local" name="start_date" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('Y-m-d\TH:i') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">New End Date</label>
                                <input type="datetime-local" name="end_date" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('bookings.show', $booking->bookingID) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Confirm New Dates
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>