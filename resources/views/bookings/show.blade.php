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
            {{-- ALERTS --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- LEFT COLUMN: BOOKING INFO & ACTIONS --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-fit">
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
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->vehicle->plate_number ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('F d, Y h:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('F d, Y h:i A') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">RM {{ number_format($booking->rental_amount ?? 0, 2) }}</dd>
                            </div>
                        </dl>

                        {{-- ACTION BUTTONS (EXTEND / CANCEL) --}}
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            @php
                                $pickup = \Carbon\Carbon::parse($booking->rental_start_date);
                                $hoursLeft = now()->diffInHours($pickup, false);
                                
                                // Late if positive (future) but less than 12h
                                $isLate = $hoursLeft < 12 && $hoursLeft > 0;
                                
                                // Can act if not already Cancelled or Completed
                                $canAction = !in_array($booking->booking_status, ['Cancelled', 'Completed']);
                            @endphp

                            @if($canAction)
                                <h4 class="text-sm font-medium text-gray-900 mb-4">Manage Booking</h4>

                                {{-- Late Cancellation Warning --}}
                                @if($isLate)
                                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-bold text-red-800">Urgent: Late Cancellation Policy</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <p>You are cancelling within <strong>12 hours</strong> of pickup.</p>
                                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                                        <li><strong>Option 1 (Recommended):</strong> <span class="font-semibold">Reschedule</span> to keep your deposit safe.</li>
                                                        <li><strong>Option 2:</strong> Cancel now and your deposit will be <span class="font-bold underline">FORFEITED (Burned)</span>.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-4">
                                    {{-- Extend Button --}}
                                    <a href="{{ route('bookings.extend', $booking->bookingID) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Reschedule Dates
                                    </a>

                                    {{-- Cancel Button --}}
                                    <form action="{{ route('bookings.cancel', $booking->bookingID) }}" method="POST" class="inline-block"
                                          onsubmit="return confirm('{{ $isLate ? 'FINAL WARNING: Because it is less than 12 hours before pickup, your payment will be FORFEITED (Not Refunded). Are you sure you want to proceed?' : 'Are you sure you want to cancel? Refund will be processed to your wallet.' }}');">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                            Cancel Booking
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                </div>
            </div>

                {{-- RIGHT COLUMN: PAYMENT INFO --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-fit">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Payment Information</h3>

                        @php
                            $verifiedPayment = $booking->payments->where('payment_status', 'Verified')->first();
                            $pendingPayment  = $booking->payments->where('payment_status', 'Pending')->first();
                            $rejectedPayment = $booking->payments->where('payment_status', 'Rejected')->first();
                            $hasVerifiedPayment = $verifiedPayment ? true : false;
                        @endphp

                        {{-- Current Status Block --}}
                        @if($verifiedPayment)
                            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <span class="text-sm font-semibold text-green-800">Payment Verified</span>
                                </div>
                                <!-- <dl class="text-sm">
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <dt class="text-gray-600">Amount:</dt>
                                        <dd class="font-medium">RM {{ number_format($verifiedPayment->total_amount, 2) }}</dd>
                                        <dt class="text-gray-600">Date:</dt>
                                        <dd class="font-medium">{{ \Carbon\Carbon::parse($verifiedPayment->payment_date)->format('M d, Y') }}</dd>
                                    </div>
                                </dl> -->
                            </div>
                        @elseif($pendingPayment)
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <span class="text-sm font-semibold text-yellow-800">Payment Pending Verification</span>
                                <p class="text-sm text-yellow-700 mt-1">Your payment is being reviewed.</p>
                            </div>
                        @elseif($rejectedPayment)
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <span class="text-sm font-semibold text-red-800">Payment Rejected</span>
                                <p class="text-sm text-red-700 mt-1">Please submit a new payment.</p>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-700">No payment has been submitted.</p>
                            </div>
                        @endif

                        {{-- Action Buttons for Payment --}}
                        <div class="mt-6 space-y-3">
                            @if(!$verifiedPayment && !$pendingPayment)
                                <a href="{{ route('payments.create', $booking->bookingID) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ $rejectedPayment ? 'Resubmit Payment' : 'Submit Payment' }}
                                </a>
                            @endif

                            @if($hasVerifiedPayment)
                                <a href="{{ route('booking.invoice', $booking->bookingID) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Download Invoice
                                </a>
                            @endif
                        </div>

                        {{-- ========================================== --}}
                        {{-- NEW SECTION: TRANSACTION HISTORY TIMELINE --}}
                        {{-- ========================================== --}}
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Transaction History</h4>
                            
                            @if($booking->payments->count() > 0)
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        @foreach($booking->payments->sortByDesc('created_at') as $payment)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if(!$loop->last)
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white 
                                                                {{ $payment->payment_status == 'Verified' ? 'bg-green-500' : 
                                                                   ($payment->payment_status == 'Rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                                                
                                                                {{-- Icons based on status --}}
                                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                                    @if($payment->payment_status == 'Verified')
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    @elseif($payment->payment_status == 'Rejected')
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                    @else
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                                    @endif
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                            <div>
                                                                <p class="text-sm text-gray-500">
                                                                    RM <span class="font-medium text-gray-900">{{ number_format($payment->total_amount, 2) }}</span>
                                                                </p>
                                                            </div>
                                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                                <time datetime="{{ $payment->created_at }}">{{ \Carbon\Carbon::parse($payment->	latest_Update_Date_Time)->format('d M, H:i') }}</time>
                                                                <p class="mt-1 text-xs font-semibold
                                                                    {{ $payment->payment_status == 'Verified' ? 'text-green-600' : 
                                                                       ($payment->payment_status == 'Rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                                                    {{ $payment->payment_status }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic">No transaction history found.</p>
                            @endif
                        </div>
                        {{-- END HISTORY --}}

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>