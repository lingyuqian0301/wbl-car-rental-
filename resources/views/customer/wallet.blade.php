<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - HASTA Travel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html { font-size: 12px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; }
        
        /* Custom Red Theme Styles */
        .text-hasta-red { color: #dc2626; }
        .bg-hasta-red { background-color: #dc2626; color: white; }
        .btn-hasta-red { background-color: #dc2626; border-color: #dc2626; color: white; }
        .btn-hasta-red:hover { background-color: #b91c1c; border-color: #b91c1c; color: white; }
        
        .card { 
            border-radius: 16px; 
            border: none; 
            border-top: 5px solid #dc2626; /* Red top border */
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

        <div class="row justify-content-center">
            <div class="col-md-6">

                {{-- WALLET CARD --}}
                <div class="card shadow-lg">
                    <div class="card-body text-center p-5">
                        
                        <div class="mb-4">
                            {{-- Ikon Wallet Merah --}}
                            <i class="bi bi-wallet2 text-hasta-red" style="font-size: 3rem;"></i>
                        </div>

                        <h6 class="text-muted text-uppercase fw-bold ls-1">Available Deposit Balance</h6>

                        {{-- Balance Display --}}
                        {{-- Nota: Duit masuk (Credit) elok kekal Hijau (Success) supaya nampak positif, 
                             tapi jika kosong kita guna kelabu. --}}
                        <h1 class="display-3 fw-bold my-2 {{ $balance > 0 ? 'text-success' : 'text-secondary' }}">
                            RM {{ number_format($balance, 2) }}
                        </h1>
                        <p class="text-muted small">Refunded deposits are stored here safely.</p>

                        <hr class="my-4 w-50 mx-auto">

                        <div class="mt-3">
                            @if($balance > 0)
                                <div class="alert alert-success d-inline-flex align-items-center px-4 py-3 border-0 bg-opacity-10 bg-success text-success">
                                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> 
                                    <div class="text-start lh-sm">
                                        <strong>Ready to use!</strong><br>
                                        <small>You can use this balance for your next booking.</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border d-inline-flex align-items-center px-4 py-3">
                                    <i class="bi bi-info-circle me-2 fs-5 text-muted"></i>
                                    <div class="text-start lh-sm text-muted">
                                        <strong>Empty Wallet</strong><br>
                                        <small>Cancel a booking >12h before pickup to get a refund here.</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-grid gap-2 mt-5">
                            {{-- Butang Merah --}}
                            <a href="{{ route('home') }}" class="btn btn-hasta-red btn-lg rounded-pill shadow-sm">
                                Book a Vehicle Now
                            </a>
                            
                            {{-- Butang Outline Merah --}}
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-danger rounded-pill">
                                View My Bookings
                            </a>
                        </div>

                    </div>
                </div>
                {{-- END WALLET CARD --}}

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @include('components.footer')
</body>
</html>