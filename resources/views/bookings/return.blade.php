@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-orange: #dc2626;
        --primary-dark-orange: #991b1b;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --success-green: #22c55e;
    }

    .return-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .return-header {
        margin-bottom: 2rem;
    }

    .return-header h1 {
        font-size: 2rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .return-header p {
        color: var(--text-secondary);
        font-size: 1.05rem;
    }

    .return-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        border: 2px solid var(--border-color);
        margin-bottom: 2rem;
    }

    .return-section-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: var(--text-primary);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-weight: bold;
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        color: var(--text-primary);
        font-size: 1.1rem;
        font-weight: 500;
    }

    .confirmation-section {
        background: #f9fafb;
        border-left: 4px solid var(--primary-orange);
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
    }

    .confirmation-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .confirmation-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 0.3rem;
        cursor: pointer;
        accent-color: var(--primary-orange);
        flex-shrink: 0;
    }

    .confirmation-checkbox label {
        cursor: pointer;
        flex: 1;
        margin: 0;
    }

    .confirmation-text {
        color: var(--text-primary);
        font-size: 1rem;
        line-height: 1.6;
    }

    .success-message {
        background: #dcfce7;
        border: 2px solid var(--success-green);
        color: #166534;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .success-message.show {
        display: block;
    }

    .error-message {
        background: #fee;
        border: 2px solid var(--primary-orange);
        color: var(--primary-orange);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .error-message.show {
        display: block;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        border: none;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .completion-badge {
        display: inline-block;
        background: var(--success-green);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .return-container {
            padding: 0 1rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<x-booking-stepper /> {{-- Auto-detects Return step --}}

<div class="return-container">
    <div class="return-header">
        <h1>Vehicle Return</h1>
        <p>Confirm the return of your rental vehicle</p>
    </div>

    <div class="completion-badge">
        ✓ Final Step - Rental Completion
    </div>

    <div class="success-message" id="successMessage">
        <strong>Success!</strong> Vehicle return confirmed. Your rental is complete!
    </div>

    <div class="return-card">
        <!-- Booking Details -->
        <div class="return-section-title">Booking Details</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Booking ID</div>
                <div class="info-value">{{ $booking->bookingID }}</div>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="return-section-title" style="margin-top: 2rem;">Vehicle Details</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Vehicle</div>
                <div class="info-value">{{ $vehicle->vehicle_brand ?? 'N/A' }} {{ $vehicle->vehicle_model ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Plate Number</div>
                <div class="info-value">{{ $vehicle->plate_number ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Confirmation Section -->
        <div class="confirmation-section">
            <form method="POST" action="{{ route('return.confirm', $booking) }}" id="returnForm">
                @csrf

                <div class="confirmation-checkbox">
                    <input type="checkbox" id="confirmReturn" name="confirm_return" value="on">
                    <label for="confirmReturn">
                        <div class="confirmation-text">
                            I confirm the vehicle has been returned in good condition and understand any damages will be assessed by HASTA Travel & Tours staff.
                        </div>
                    </label>
                </div>

                @if ($errors->any())
                    <div class="error-message show">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="action-buttons">
                    <a href="{{ route('pickup.show', $booking) }}" class="btn btn-secondary">Back to Pickup</a>
                    <button type="submit" class="btn btn-primary" id="completeBtn" disabled>
                        Complete Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const confirmReturnCheckbox = document.getElementById('confirmReturn');
    const completeBtn = document.getElementById('completeBtn');
    const returnForm = document.getElementById('returnForm');
    const successMessage = document.getElementById('successMessage');

    confirmReturnCheckbox.addEventListener('change', function() {
        completeBtn.disabled = !this.checked;
    });

    returnForm.addEventListener('submit', function(e) {
        e.preventDefault();

        completeBtn.disabled = true;
        completeBtn.textContent = 'Completing...';

        // Show success message
        successMessage.classList.add('show');

        // Submit form after delay
        setTimeout(function() {
            returnForm.submit();
        }, 1500);
    });
</script>

@endsection
