@props(['current'])

<div class="booking-stepper">
    @php
        $steps = [
            1 => 'Booking Details',
            2 => 'Confirmation',
            3 => 'Payment',
            4 => 'Completed',
        ];
    @endphp

    @foreach ($steps as $num => $label)
        <div class="step {{ $current >= $num ? 'active' : '' }}">
            <div class="circle">
                {{ $current > $num ? '✓' : $num }}
            </div>
            <span class="label">{{ $label }}</span>
        </div>

        @if (!$loop->last)
            <div class="line {{ $current > $num ? 'active' : '' }}"></div>
        @endif
    @endforeach
</div>
