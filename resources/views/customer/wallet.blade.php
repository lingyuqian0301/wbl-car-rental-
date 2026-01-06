<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - HASTA Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        .card { border-radius: 12px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">HASTA Travel</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">
                    Hello, {{ Auth::user()->name ?? 'Guest' }}
                </span>
                <a class="nav-link text-danger" href="{{ route('bookings.index') }}">My Bookings</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card shadow-sm border-0">
                    <div class="card-body text-center p-5">
                        <h5 class="text-muted text-uppercase mb-3 fw-bold">Outstanding Balance</h5>

                        <h1 class="display-3 fw-bold my-3 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                            RM {{ number_format($outstanding, 2) }}
                        </h1>

                        <div class="mt-4">
                            @if($outstanding > 0)
                                <div class="alert alert-warning d-inline-block">
                                    <i class="bi bi-exclamation-circle"></i> 
                                    You have pending payments. Please verify your bookings.
                                </div>
                            @else
                                <div class="alert alert-success d-inline-block">
                                    <i class="bi bi-check-circle"></i>
                                    You are all clear! No outstanding debts.
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary">
                                Go to My Bookings
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>