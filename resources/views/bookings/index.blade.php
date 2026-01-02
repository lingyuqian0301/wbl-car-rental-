<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Bookings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($bookings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rental Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ ($bookings->currentPage() - 1) * $bookings->perPage() + $loop->iteration }}
                                            </td>
                                         <td class="px-6 py-4 whitespace-nowrap">
                                        @if($booking->vehicle)
                                            <div class="text-sm font-medium text-gray-900">
                                             {{ $booking->vehicle->brand ?? $booking->vehicle->vehicle_brand ?? 'Car' }}
                                             {{ $booking->vehicle->model ?? $booking->vehicle->vehicle_model ?? '' }}
                                            </div>

                                                    <div class="text-sm text-gray-500">
                                                        {{ $booking->vehicle->registration_number ?? $booking->vehicle->plate_number ?? '' }}
                                                    </div>
                                                @else
                                                    <span class="text-red-500 font-bold text-sm">Vehicle Not Found</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>{{ $booking->start_date->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-400">to {{ $booking->end_date->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-400">{{ $booking->duration_days }} days</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">RM {{ number_format($booking->total_price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($booking->status == 'Pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($booking->status == 'Confirmed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                                @elseif($booking->status == 'Cancelled')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $verifiedPayment = $booking->payments->where('status', 'Verified')->first();
                                                    $pendingPayment = $booking->payments->where('status', 'Pending')->first();
                                                    $rejectedPayment = $booking->payments->where('status', 'Rejected')->first();
                                                @endphp
                                                @if($verifiedPayment)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                                @elseif($pendingPayment)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($rejectedPayment)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Not Paid</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex items-center space-x-4">
        <a href="{{ route('bookings.show', $booking->bookingID) }}" class="text-indigo-600 hover:text-indigo-900">
            View
        </a>

        @if(!$verifiedPayment && !$pendingPayment)
            <a href="{{ route('payments.create', $booking->bookingID) }}" class="text-indigo-600 hover:text-indigo-900">
                {{ $rejectedPayment ? 'Resubmit' : 'Pay Now' }}
            </a>
        @endif

        @if($verifiedPayment)
            <a href="{{ route('booking.invoice', $booking->bookingID) }}" class="text-green-600 hover:text-green-900">
                📄 Invoice
            </a>
        @endif
    </div>
</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $bookings->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new booking.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

