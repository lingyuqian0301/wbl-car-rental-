@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-blue: #dc2626;
        --primary-dark-blue: #991b1b;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --bg-light: #f8fafc;
    }

    .deposit-container {
        max-width: 800px;
        margin: 3rem auto;
        padding: 0 1.5rem;
    }

    .deposit-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        padding: 3rem;
        text-align: center;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .success-icon svg {
        width: 48px;
        height: 48px;
        color: white;
    }

    .deposit-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }

    .deposit-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
        margin-bottom: 2rem;
    }

    .deposit-amount {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin: 1.5rem 0;
    }

    .deposit-question {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 2rem 0 1.5rem;
    }

    .options-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .option-card {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem 1.5rem;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }

    .option-card:hover {
        border-color: var(--primary-blue);
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.15);
    }

    .option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .option-card input[type="radio"]:checked + .option-content {
        color: var(--primary-blue);
    }

    .option-card input[type="radio"]:checked ~ .check-icon {
        display: block;
    }

    .option-icon {
        width: 60px;
        height: 60px;
        background: var(--bg-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .option-icon svg {
        width: 32px;
        height: 32px;
        color: var(--primary-blue);
    }

    .option-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .option-description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .check-icon {
        display: none;
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 28px;
        height: 28px;
        background: var(--primary-blue);
        border-radius: 50%;
        color: white;
    }

    .check-icon svg {
        width: 20px;
        height: 20px;
    }

    .btn-submit {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 1rem 3rem;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 2rem;
        transition: all 0.3s;
    }

    .btn-submit:hover:not(:disabled) {
        background: var(--primary-dark-blue);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .options-container {
            grid-template-columns: 1fr;
        }
        .deposit-card {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="deposit-container">
    <div class="deposit-card">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="deposit-title">Return Successful!</h1>
        <p class="deposit-subtitle">Your vehicle has been returned successfully</p>

        @if(session('success'))
            <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        <div class="deposit-amount">RM {{ number_format(50, 2) }}</div>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Deposit Amount</p>

        <div class="deposit-question">What would you like to do with your deposit?</div>

        <form method="POST" action="{{ route('return.handleDeposit', $booking->bookingID) }}">
            @csrf

            <div class="options-container">
                <label class="option-card">
                    <input type="radio" name="deposit_action" value="wallet" required>
                    <div class="option-content">
                        <div class="option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="option-title">Add to Wallet</div>
                        <div class="option-description">Keep your deposit in your HASTA wallet for future bookings</div>
                    </div>
                    <div class="check-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </label>

                <label class="option-card">
                    <input type="radio" name="deposit_action" value="refund" required>
                    <div class="option-content">
                        <div class="option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                        </div>
                        <div class="option-title">Request Refund</div>
                        <div class="option-description">Get your deposit refunded to your bank account (3-5 business days)</div>
                    </div>
                    <div class="check-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </label>
            </div>

            @error('deposit_action')
                <div style="color: #dc2626; font-size: 0.9rem; margin-top: 0.5rem;">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn-submit">Continue</button>
        </form>

        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 2rem;">
            Note: This decision is final. If you request a refund, processing may take 3-5 business days.
        </p>
    </div>
</div>
@endsection