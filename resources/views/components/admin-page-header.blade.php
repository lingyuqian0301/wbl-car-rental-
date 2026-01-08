@props(['title', 'description', 'stats', 'date' => null])

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h1 class="h3 mb-1 fw-bold">{{ $title }}</h1>
                </div>
            </div>
            @if($date)
                <p class="mb-0 mt-3 fw-semibold">{{ $description }} for {{ $date->format('d M Y') }}</p>
            @else
                <p class="mb-0 mt-3 fw-semibold">{{ $description }}</p>
            @endif
            @if(isset($stats) && count($stats) > 0)
                <div class="mt-3 d-flex flex-wrap gap-3">
                    @foreach($stats as $stat)
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="bi {{ $stat['icon'] ?? 'bi-info-circle' }} me-1"></i>
                                <strong>{{ $stat['label'] }}:</strong> {{ $stat['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(120deg, var(--admin-red, #dc2626) 0%, var(--admin-red-dark, #991b1b) 100%);
        color: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 35px rgba(185, 28, 28, 0.25);
        padding: 24px 28px;
    }
</style>




