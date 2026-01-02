<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{-- FIX 1: Changed id to bookingID --}}
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
                                {{-- FIX 2: Changed id to bookingID --}}
                                <dd class="mt-1 text-sm text-gray-900">#{{ $booking->bookingID }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if($booking->status == 'Pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @elseif($booking->status == 'Confirmed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                    @elseif($booking->status == 'Cancelled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->vehicle->full_model ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registration</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->vehicle->registration_number ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->start_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->end_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->duration_days }} days</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Daily Rate</dt>
                                <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($booking->vehicle->daily_rate ?? 0, 2) }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">RM {{ number_format($booking->total_price, 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                        
                        @php
                            $verifiedPayment = $booking->payments->where('status', 'Verified')->first();
                            $pendingPayment = $booking->payments->where('status', 'Pending')->first();
                            $rejectedPayment = $booking->payments->where('status', 'Rejected')->first();
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
                                        <dd class="font-medium">RM {{ number_format($verifiedPayment->amount, 2) }}</dd>
                                        <dt class="text-gray-600">Type:</dt>
                                        <dd class="font-medium">{{ $verifiedPayment->payment_type }}</dd>
                                        <dt class="text-gray-600">Method:</dt>
                                        <dd class="font-medium">{{ $verifiedPayment->payment_method }}</dd>
                                        <dt class="text-gray-600">Date:</dt>
                                        <dd class="font-medium">{{ $verifiedPayment->payment_date->format('M d, Y') }}</dd>
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
                                <p class="text-sm text-red-700 mb-2"><strong>Reason:</strong> {{ $rejectedPayment->rejected_reason }}</p>
                                <p class="text-sm text-red-700">Please submit a new payment.</p>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-700 mb-4">No payment has been submitted for this booking.</p>
                            </div>
                        @endif

                        @if($booking->payments->count() > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Payment History</h4>
                                <div class="space-y-2">
                                    @foreach($booking->payments as $payment)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <div>
                                                <span class="text-sm font-medium">{{ $payment->payment_type }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $payment->payment_date->format('M d, Y') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium">RM {{ number_format($payment->amount, 2) }}</span>
                                                @if($payment->status == 'Pending')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($payment->status == 'Verified')
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
                                {{-- FIX 3: Changed id to bookingID --}}
                                <a href="{{ route('payments.create', $booking->bookingID) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if($rejectedPayment)
                                        Resubmit Payment
                                    @else
                                        Submit Payment
                                    @endif
                                </a>
                            @endif
                            
                            @if($rejectedPayment && !$pendingPayment)
                                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-700">
                                        <strong>Note:</strong> Your previous payment was rejected. Please review the reason above and submit a new payment with the correct information.
                                    </p>
                                </div>
                            @endif

                            @if($hasVerifiedPayment)
                                {{-- FIX 4: Changed id to bookingID --}}
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