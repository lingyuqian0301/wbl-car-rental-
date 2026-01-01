<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Card - HASTA Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('components.header')

    <div class="container py-5">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-3">&larr; Back to Dashboard</a>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-white mb-4 shadow" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); border:none; border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">HASTA LOYALTY</h5>
                            <span class="badge bg-warning text-dark">Gold Member</span>
                        </div>
                        
                        <div class="text-center my-4">
                            <h2 class="display-3 fw-bold">{{ $card->total_stamps ?? 0 }}</h2>
                            <p class="mb-0">Total Stamps</p>
                        </div>

                        @php 
                            $stamps = $card->total_stamps ?? 0;
                            $percentage = min(($stamps / 48) * 100, 100);
                        @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small">
                            <span>0</span>
                            <span>Target: 48 Stamps for Free Day</span>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Your Rewards</h5>
                @forelse($vouchers as $voucher)
                    <div class="card mb-3 shadow-sm border-success">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-success mb-1">🎟 {{ $voucher->discount_type }}</h6>
                                <small class="text-muted">Active Reward</small>
                            </div>
                            <button class="btn btn-sm btn-outline-success">Redeem</button>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-secondary text-center">
                        You haven't earned any vouchers yet. Keep booking!
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</body>
</html>