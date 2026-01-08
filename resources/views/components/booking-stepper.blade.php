{{-- Booking Stepper Component --}}
{{-- Usage: <x-booking-stepper /> --}}

@php
    // Define all booking steps in order
    $allSteps = [
        [
            'number' => 1,
            'label' => 'Select Vehicle',
            'routes' => ['vehicles.show'],
        ],
        [
            'number' => 2,
            'label' => 'Booking Details',
            'routes' => ['booking.confirm'],
        ],
        [
            'number' => 3,
            'label' => 'Payment',
            'routes' => ['payments.create'],
        ],
        [
            'number' => 4,
            'label' => 'Agreement',
            'routes' => ['agreement.show'],
        ],
        [
            'number' => 5,
            'label' => 'Pickup',
            'routes' => ['pickup.show'],
        ],
        [
            'number' => 6,
            'label' => 'Return',
            'routes' => ['return.show'],
        ],
    ];

    // Determine the current step based on the current route
    $currentStepNumber = 1;
    foreach ($allSteps as $step) {
        if (request()->routeIs($step['routes'])) {
            $currentStepNumber = $step['number'];
            break;
        }
    }
@endphp

<nav class="booking-stepper" aria-label="Booking Progress">
    <div class="booking-stepper-wrapper">
        <ol class="booking-stepper-list">
            @foreach ($allSteps as $step)
                @php
                    $isCompleted = $step['number'] < $currentStepNumber;
                    $isCurrent = $step['number'] === $currentStepNumber;
                    $stepStatus = $isCompleted ? 'completed' : ($isCurrent ? 'active' : 'upcoming');
                @endphp

                <li class="booking-stepper-step booking-stepper-step--{{ $stepStatus }}">
                    <span class="booking-stepper-label">{{ $step['label'] }}</span>
                    
                    <div class="booking-stepper-circle" aria-current="{{ $isCurrent ? 'step' : 'false' }}">
                        @if ($isCompleted)
                            <span class="booking-stepper-icon">✓</span>
                        @else
                            <span class="booking-stepper-number">{{ $step['number'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</nav>

<style>
   /* Booking Stepper Container */
.booking-stepper {
    width: 100%;
    padding: 2rem 1.5rem;
    background: transparent;
    display: flex;
    justify-content: center;
}

.booking-stepper-wrapper {
    width: 100%;
    max-width: 1200px;
}

/* Stepper List */
.booking-stepper-list {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    list-style: none;
    padding: 0;
    margin: 0;
    width: 100%;
}

/* Individual Step */
.booking-stepper-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    text-align: center;
}

/* Label */
.booking-stepper-label {
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 0.6rem;
    white-space: nowrap;
}

/* Circle */
.booking-stepper-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    z-index: 2;
    background: #fff;
}

/* Connector line */
.booking-stepper-step::after {
    content: '';
    
    position: absolute;
    top: calc(44px / 2 + 2.2rem);
    left: 50%;
    width: 100%;
    height: 3px;
    background: #d1d5db;
    transform: translateY(-50%);
    z-index: 1;
}


.booking-stepper-step:last-child::after {
    display: none;
}

/* Completed */
.booking-stepper-step--completed .booking-stepper-circle {
    background: linear-gradient(135deg, #059669, #047857);
    border-color: #059669;
    color: #fff;
}

.booking-stepper-step--completed::after {
    background: #059669;
}

/* Active */
.booking-stepper-step--active .booking-stepper-circle {
    background: linear-gradient(135deg, #dc2626, #991b1b);
    border-color: #dc2626;
    color: #fff;
}

.booking-stepper-step--active .booking-stepper-label {
    color: #dc2626;
    font-weight: 700;
}

/* Upcoming */
.booking-stepper-step--upcoming .booking-stepper-circle {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #9ca3af;
}

.booking-stepper-step--upcoming .booking-stepper-label {
    color: #9ca3af;
}

/* Mobile */
@media (max-width: 768px) {
    .booking-stepper-label {
        font-size: 0.7rem;
    }

    .booking-stepper-circle {
        width: 36px;
        height: 36px;
    }

    .booking-stepper-step::after {
        top: 50px;
        height: 2px;
    }
}

</style>

