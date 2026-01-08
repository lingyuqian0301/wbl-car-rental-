<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                {{ __('My Bookings') }}
            </h2>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($bookings->count() > 0)
                        <div class="overflow-x-auto pb-20"> {{-- Added pb-20 to allow space for dropdown --}}
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
                                            {{ $bookings->total() - (($bookings->currentPage() - 1) * $bookings->perPage() + $loop->index) }}
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
                                                <div>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('M d, Y') : 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">to {{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('M d, Y') : 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $booking->duration }} days</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">RM {{ number_format($booking->rental_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($booking->booking_status == 'Pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($booking->booking_status == 'Confirmed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                                @elseif($booking->booking_status == 'Cancelled')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $verifiedPayment = $booking->payments->where('payment_status', 'Verified')->first();
                                                    $pendingPayment = $booking->payments->where('payment_status', 'Pending')->first();
                                                    $rejectedPayment = $booking->payments->where('payment_status', 'Rejected')->first();
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium overflow-visible">
                                                <x-dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                            <div>Actions</div>
                                                            <div class="ml-1">
                                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    </x-slot>

                                                    <x-slot name="content">
                                                        {{-- View Details --}}
                                                        <x-dropdown-link :href="route('bookings.show', $booking->bookingID)">
                                                            {{ __('View Details') }}
                                                        </x-dropdown-link>

                                                        {{-- Payment Actions --}}
                                                        @if(!$verifiedPayment && !$pendingPayment && $booking->booking_status != 'Cancelled')
                                                            <x-dropdown-link :href="route('payments.create', $booking->bookingID)">
                                                                {{ $rejectedPayment ? __('Resubmit Payment') : __('Pay Now') }}
                                                            </x-dropdown-link>
                                                        @endif

                                                        {{-- Invoice --}}
                                                        @if($verifiedPayment)
                                                            <x-dropdown-link :href="route('booking.invoice', $booking->bookingID)">
                                                                {{ __('Download Invoice') }}
                                                            </x-dropdown-link>
                                                        @endif
                                                        
                                                        {{-- Extend Booking (Only if Active/Confirmed/Pending and not Cancelled) --}}
                                                        @if($booking->booking_status != 'Confirmed' && !$verifiedPayment && !in_array($booking->booking_status, ['Cancelled', 'Completed']))
                                                            <x-dropdown-link :href="route('bookings.extend.form', $booking->bookingID)">
                                                                {{ __('Extend Booking') }}
                                                            </x-dropdown-link>
                                                        @endif

                                                        {{-- Cancel Booking (Only if not already cancelled or completed) --}}
                                                            @if($booking->booking_status != 'Confirmed' && !$verifiedPayment && !in_array($booking->booking_status, ['Cancelled', 'Completed']))
                                                                <form method="POST" action="{{ route('bookings.cancel', $booking->bookingID) }}">
                                                                    @csrf
                                                                    <x-dropdown-link :href="route('bookings.cancel', $booking->bookingID)"
                                                                            onclick="event.preventDefault(); if(confirm('Are you sure you want to cancel this booking?')) { this.closest('form').submit(); }">
                                                                        {{ __('Cancel Booking') }}
                                                                    </x-dropdown-link>
                                                                </form>
                                                            @endif
                                                    </x-slot>
                                                </x-dropdown>
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