<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Booking Details') }} #{{ $booking->bookingID }}
            </h2>
            <a href="{{ route('bookings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Back to Bookings</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Booking Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Booking ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $booking->bookingID }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    {{-- FIX: Updated to 'booking_status' --}}
                                    @if($booking->booking_status == 'Pending' || $booking->booking_status == 'Pending Payment Verification')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @elseif($booking->booking_status == 'Confirmed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                    @elseif($booking->booking_status == 'Cancelled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $booking->booking_status }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->vehicle->vehicle_brand ?? '' }} {{ $booking->vehicle->vehicle_model ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Plate Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->vehicle->vehicle_number ?? $booking->vehicle->plate_number ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->start_date)->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->end_date)->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1 }} days
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Daily Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($booking->vehicle->price_per_day ?? 0, 2) }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">RM {{ number_format($booking->total_amount ?? 0, 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Payment Information</h3>

                        @php
                            // FIX: Using 'payment_status' to match new DB
                            $verifiedPayment = $booking->payments->where('payment_status', 'Verified')->first();
                            $pendingPayment  = $booking->payments->where('payment_status', 'Pending')->first();
                            $rejectedPayment = $booking->payments->where('payment_status', 'Rejected')->first();
                            $hasVerifiedPayment = $verifiedPayment ? true : false;
                        @endphp

                        @if($verifiedPayment)
                            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-green-800">Payment Verified</span>
                                </div>
                                <dl class="text-sm">
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <dt class="text-gray-600">Amount:</dt>
                                        {{-- FIX: Using 'total_amount' --}}
                                        <dd class="font-medium">RM {{ number_format($verifiedPayment->total_amount, 2) }}</dd>

                                        <dt class="text-gray-600">Date:</dt>
                                        <dd class="font-medium">{{ \Carbon\Carbon::parse($verifiedPayment->payment_date)->format('M d, Y') }}</dd>

                                        {{-- REMOVED: payment_type & payment_method (Deleted from DB) --}}
                                    </div>
                                </dl>
                            </div>
                        @elseif($pendingPayment)
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <svg class="h-5 w-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-yellow-800">Payment Pending Verification</span>
                                </div>
                                <p class="text-sm text-yellow-700">Your payment is being reviewed by our staff.</p>
                            </div>
                        @elseif($rejectedPayment)
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <svg class="h-5 w-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-red-800">Payment Rejected</span>
                                </div>
                                {{-- REMOVED: rejected_reason (Deleted from DB) --}}
                                <p class="text-sm text-red-700">Please submit a new payment.</p>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-700 mb-4">No payment has been submitted for this booking.</p>
                            </div>
                        @endif

                        {{-- Payment History List --}}
                        @if($booking->payments->count() > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Payment History</h4>
                                <div class="space-y-2">
                                    @foreach($booking->payments as $payment)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <div>
                                                {{-- Removed payment_type (Deleted from DB) --}}
                                                <span class="text-sm font-medium">Payment</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                {{-- FIX: Using 'total_amount' --}}
                                                <span class="text-sm font-medium">RM {{ number_format($payment->total_amount, 2) }}</span>

                                                {{-- FIX: Using 'payment_status' --}}
                                                @if($payment->payment_status == 'Pending')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                             @elseif($payment->payment_status == 'Verified' || $payment->payment_status == 'Full')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                            @else
                                                 <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                            @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 space-y-3">
                            @if(!$verifiedPayment && !$pendingPayment)
                                <a href="{{ route('payments.create', $booking->bookingID) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if($rejectedPayment)
                                        Resubmit Payment
                                    @else
                                        Submit Payment
                                    @endif
                                </a>
                            @endif

                            @if($hasVerifiedPayment)
                                <a href="{{ route('booking.invoice', $booking->bookingID) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Download Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
