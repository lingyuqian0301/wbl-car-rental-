@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-orange: #dc2626;
        --primary-dark-orange: #991b1b;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    .agreement-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .agreement-header {
        margin-bottom: 2rem;
    }

    .agreement-header h1 {
        font-size: 2rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .agreement-header p {
        color: var(--text-secondary);
        font-size: 1.05rem;
    }

    .agreement-preview-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        border: 2px solid var(--border-color);
    }

    .agreement-preview-frame {
        width: 100%;
        height: 600px;
        border: none;
        display: block;
    }

    .agreement-actions {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        border: 2px solid var(--border-color);
    }

    .agreement-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1.2rem;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .agreement-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 0.3rem;
        cursor: pointer;
        accent-color: var(--primary-orange);
        flex-shrink: 0;
    }

    .agreement-checkbox label {
        cursor: pointer;
        flex: 1;
        margin: 0;
    }

    .agreement-checkbox-text {
        color: var(--text-primary);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .agreement-error {
        background: #fee;
        border: 2px solid var(--primary-orange);
        color: var(--primary-orange);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .agreement-error.show {
        display: block;
    }

    .agreement-success {
        background: #dcfce7;
        border: 2px solid #22c55e;
        color: #166534;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .agreement-success.show {
        display: block;
    }

    .agreement-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
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

    @media (max-width: 768px) {
        .agreement-container {
            padding: 0 1rem;
        }

        .agreement-preview-frame {
            height: 400px;
        }

        .agreement-buttons {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<x-booking-stepper /> {{-- Auto-detects Agreement step --}}

<div class="agreement-container">
    <div class="agreement-header">
        <h1>Rental Agreement</h1>
        <p>Please review the rental agreement and accept the terms to proceed with your booking</p>
    </div>

    @if ($errors->any())
        <div class="agreement-error show">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="agreement-success" id="successMessage">
        <strong>Success!</strong> Agreement downloaded successfully. You may proceed to vehicle pickup.
    </div>

    <div class="agreement-preview-wrapper">
        <iframe class="agreement-preview-frame" src="{{ route('agreement.preview', $booking) }}" title="Rental Agreement"></iframe>
    </div>

    <div class="agreement-actions">
        <form method="POST" action="{{ route('agreement.download', $booking) }}" id="agreementForm">
            @csrf

            <div class="agreement-checkbox">
                <input type="checkbox" id="agreeCheckbox" name="agree" value="on">
                <label for="agreeCheckbox">
                    <div class="agreement-checkbox-text">
                        I have read and agree to the <strong>Rental Agreement Terms & Conditions</strong>. I understand my responsibilities as a renter and agree to comply with all the terms outlined in this agreement.
                    </div>
                </label>
            </div>

            <div class="agreement-buttons">
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back to My Bookings</a>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" id="downloadBtn" disabled>
                        Download Agreement
                    </button>
                    <a href="{{ route('pickup.show', $booking) }}" class="btn btn-primary" id="nextBtn" style="cursor: not-allowed; opacity: 0.6;" disabled>Next: Pickup</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const downloadBtn = document.getElementById('downloadBtn');
    const nextBtn = document.getElementById('nextBtn');
    const agreementForm = document.getElementById('agreementForm');
    const successMessage = document.getElementById('successMessage');
    let hasDownloaded = false;

    // Update button states
    function updateButtonStates() {
        const isChecked = agreeCheckbox.checked;
        const canProceed = isChecked && hasDownloaded;
        
        downloadBtn.disabled = !isChecked;
        
        if (canProceed) {
            nextBtn.disabled = false;
            nextBtn.style.cursor = 'pointer';
            nextBtn.style.opacity = '1';
        } else {
            nextBtn.disabled = true;
            nextBtn.style.cursor = 'not-allowed';
            nextBtn.style.opacity = '0.6';
        }
    }

    agreeCheckbox.addEventListener('change', function() {
        updateButtonStates();
    });

    // Handle form submission
    agreementForm.addEventListener('submit', function(e) {
        e.preventDefault();

        downloadBtn.disabled = true;
        downloadBtn.textContent = 'Downloading...';

        // Show success message after 3 seconds
        setTimeout(function() {
            // Mark as downloaded
            hasDownloaded = true;
            
            // Reset button
            downloadBtn.textContent = 'Download Agreement';
            updateButtonStates();

            // Show success message
            successMessage.classList.add('show');

            // Auto-hide success message after 5 seconds
            setTimeout(function() {
                successMessage.classList.remove('show');
            }, 5000);
        }, 3000);

        // Submit the form after displaying success message
        setTimeout(function() {
            agreementForm.submit();
        }, 3000);
    });

    // Prevent navigation if conditions not met
    nextBtn.addEventListener('click', function(e) {
        if (!agreeCheckbox.checked || !hasDownloaded) {
            e.preventDefault();
        }
    });
</script>

@endsection

