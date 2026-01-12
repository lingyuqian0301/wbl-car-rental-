<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Card - HASTA Travel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html { font-size: 12px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; }
        .loyalty-card {
            background: linear-gradient(135deg, #b45309, #f59e0b, #fbbf24);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(180, 83, 9, 0.3);
        }
        .btn-claim {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-claim:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
            color: white;
        }
        .btn-claim:disabled {
            background: #9ca3af;
            opacity: 0.7;
            cursor: not-allowed;
        }
        .voucher-card {
            border-left: 4px solid #059669;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        }
    </style>
</head>
<body>
    @include('components.header')

    <div class="container py-5">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-4">&larr; Back to Home</a>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Loyalty Card -->
                <div class="card loyalty-card text-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0"> HASTA LOYALTY</h5>
                            <span class="badge bg-white text-warning fw-bold" style="color: #b45309 !important;">Gold Member</span>
                        </div>
                        
                        <div class="text-center my-4">
                            @php 
                                $stamps = $card->total_stamps ?? 0;
                                $percentage = min(($stamps / 5) * 100, 100);
                                $canClaim = $stamps >= 5;
                            @endphp
                            <h2 class="display-2 fw-bold" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $stamps }}</h2>
                            <p class="mb-0 opacity-75">Total Stamps</p>
                        </div>

                        <div class="progress mb-2" style="height: 12px; background: rgba(255,255,255,0.4); border-radius: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%; background: white; border-radius: 10px;"></div>
                        </div>
                        <div class="d-flex justify-content-between small opacity-75">
                            <span>0</span>
                            <span> 5 stamps = 10% discount</span>
                        </div>
                    </div>
                </div>

                <!-- Info Text -->
                <div class="text-center mb-4">
                    <p class="text-muted">Earn 1 stamp for every completed booking.<br>Collect 5 stamps to claim a discount on your next rental.</p>
                </div>

                <!-- Claim Button -->
                <div class="text-center mb-4">
                    @if($canClaim)
                        <a href="{{ route('loyalty.claim') }}" class="btn btn-claim btn-lg" onclick="return confirm('Claim your 10% discount voucher? This will use 5 stamps.')">
                             Claim Discount
                        </a>
                    @else
                        <button class="btn btn-claim btn-lg" disabled>
                             Claim Discount ({{ $stamps }}/5 stamps)
                        </button>
                    @endif
                </div>

                <!-- Active Vouchers -->
                <h5 class="mb-3">🎟 Your Active Vouchers</h5>
                @forelse($vouchers as $voucher)
                    <div class="card voucher-card mb-3 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-success mb-1">
                                    @if($voucher->discount_type == 'PERCENT')
                                        {{ $voucher->discount_amount }}% OFF
                                    @else
                                        RM {{ number_format($voucher->discount_amount, 2) }} OFF
                                    @endif
                                </h6>
                                <small class="text-muted"> Auto-applied on next booking</small>
                            </div>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light text-center border">
                        <div class="text-muted">
                            @if($canClaim)
                                 You have enough stamps! Claim your discount above.
                            @else
                                 No active vouchers. Keep booking to earn stamps!
                            @endif
                        </div>
                    </div>
                @endforelse

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @include('components.footer')
</body>
</html>
