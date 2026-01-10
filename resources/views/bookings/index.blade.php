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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($bookings->count() > 0)
                        
                        {{-- Fixed: Added overflow-y-visible and proper spacing --}}
                        <div class="overflow-x-auto overflow-y-visible pb-60 min-h-[500px]">
                            
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rental Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 200px;">Payment Progress</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($bookings as $booking)
                                        @php
                                            // 1. CALCULATE TOTALS
                                            $totalPrice = $booking->rental_amount;
                                            $verifiedPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
                                            $hasPending = $booking->payments->where('payment_status', 'Pending')->count() > 0;

                                            $percentage = $totalPrice > 0 ? ($verifiedPaid / $totalPrice) * 100 : 0;
                                            $percentage = min($percentage, 100);

                                            // 2. PAYMENT LABELS
                                            if ($verifiedPaid >= ($totalPrice - 1)) {
                                                $payStatusLabel = 'Fully Verified';
                                                $barColor = 'bg-green-500';
                                                $textColor = 'text-green-700';
                                            } elseif ($verifiedPaid > 0) {
                                                $payStatusLabel = 'Deposit Verified';
                                                $barColor = 'bg-yellow-400'; 
                                                $textColor = 'text-yellow-700';
                                            } else {
                                                $payStatusLabel = 'Pending Payment';
                                                $barColor = 'bg-gray-300';
                                                $textColor = 'text-gray-500';
                                            }

                                            // 3. BOOKING STATUS LABELS
                                            $displayStatus = $booking->booking_status;
                                            $statusBadge = 'bg-gray-100 text-gray-800';

                                            if ($booking->booking_status == 'Cancelled') {
                                                $displayStatus = 'Cancelled';
                                                $statusBadge = 'bg-red-100 text-red-800';
                                            } elseif ($verifiedPaid >= ($totalPrice - 1)) {
                                                $displayStatus = 'Ready for Pickup';
                                                $statusBadge = 'bg-green-100 text-green-800';
                                            } elseif ($verifiedPaid > 0) {
                                                $displayStatus = 'Reserved'; 
                                                $statusBadge = 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                $displayStatus = 'Pending';
                                                $statusBadge = 'bg-gray-100 text-gray-800';
                                            }
                                        @endphp

                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $bookings->total() - (($bookings->currentPage() - 1) * $bookings->perPage() + $loop->index) }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($booking->vehicle)
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $booking->vehicle->vehicle_brand }} {{ $booking->vehicle->vehicle_model }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $booking->vehicle->plate_number }}</div>
                                                @else
                                                    <span class="text-red-500 font-bold text-sm">Vehicle Not Found</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-400">to {{ \Carbon\Carbon::parse($booking->rental_end_date)->format('M d, Y') }}</div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusBadge }}">
                                                    {{ $displayStatus }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap align-middle">
                                                <div class="w-full max-w-xs">
                                                    <div class="flex justify-between mb-1">
                                                        <span class="text-xs font-bold {{ $textColor }}">{{ $payStatusLabel }}</span>
                                                        <span class="text-xs font-bold text-gray-600">{{ number_format($percentage, 0) }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                        <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 font-medium">
                                                        RM {{ number_format($verifiedPaid, 2) }} <span class="text-gray-400">/</span> RM {{ number_format($totalPrice, 2) }}
                                                    </div>
                                                    @if($hasPending)
                                                        <div class="text-[10px] text-blue-500 italic mt-1">* Verification Pending</div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Fixed: Removed overflow-visible, use static positioning --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="relative z-10">
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
                                                            <x-dropdown-link :href="route('bookings.show', $booking->bookingID)">
                                                                {{ __('View Details') }}
                                                            </x-dropdown-link>

                                                            @if($verifiedPaid < ($totalPrice - 1) && $booking->booking_status != 'Cancelled')
                                                                @if($hasPending)
                                                                    <div class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-400 cursor-not-allowed">
                                                                        {{ __('Verifying Payment...') }}
                                                                    </div>
                                                                @else
                                                                    <x-dropdown-link :href="route('payments.create', ['booking' => $booking->bookingID])">
                                                                        {{ $verifiedPaid > 0 ? __('Pay Balance') : __('Pay Deposit') }}
                                                                    </x-dropdown-link>
                                                                @endif
                                                            @endif

                                                            @if($verifiedPaid >= ($totalPrice - 1))
                                                                <x-dropdown-link :href="route('booking.invoice', $booking->bookingID)">
                                                                    {{ __('Download Invoice') }}
                                                                </x-dropdown-link>
                                                            @endif

                                                            @if($verifiedPaid == 0 && $booking->booking_status != 'Confirmed' && $booking->booking_status != 'Cancelled')
                                                                <form method="POST" action="{{ route('bookings.cancel', $booking->bookingID) }}">
                                                                    @csrf
                                                                    <x-dropdown-link href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to cancel?')) { this.closest('form').submit(); }">
                                                                        {{ __('Cancel Booking') }}
                                                                    </x-dropdown-link>
                                                                </form>
                                                            @endif
                                                        </x-slot>
                                                    </x-dropdown>
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
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                            <p class="mt-1 text-sm text-gray-500">Book a car to get started!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add custom styles to handle dropdown visibility --}}
    <style>
        /* Ensure dropdowns are always visible */
        .relative {
            position: relative;
        }
        
        /* Prevent table from clipping dropdowns */
        table {
            position: relative;
        }
        
        tbody tr {
            position: relative;
        }
        
        /* Make sure dropdown menus appear above other content */
        [role="menu"] {
            z-index: 50 !important;
        }
    </style>
</x-app-layout>