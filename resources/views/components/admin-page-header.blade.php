@props(['title', 'description', 'stats' => [], 'date' => null])

<style>
    .admin-page-header {
        background: linear-gradient(135deg, var(--admin-red, #dc2626) 0%, var(--admin-red-dark, #991b1b) 100%);
        color: #fff;
        border-radius: var(--radius-xl, 1rem);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.15);
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
    }
    
    .admin-page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }
    
    .admin-page-header .description {
        opacity: 0.9;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    
    .admin-page-header .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .admin-page-header .stat-badge:hover {
        background: rgba(255, 255, 255, 0.25);
    }
    
    .admin-page-header .stat-badge i {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    .admin-page-header .stat-badge strong {
        font-weight: 600;
    }
    
    .admin-page-header .stat-value {
        font-weight: 700;
        font-size: 0.9rem;
    }
</style>

<div class="admin-page-header">
    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
        <div>
            <h1>{{ $title }}</h1>
            @if($date)
                <p class="description mb-0">{{ $description }} — {{ $date->format('d M Y') }}</p>
            @else
                <p class="description mb-0">{{ $description }}</p>
            @endif
            
            @if(count($stats) > 0)
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @foreach($stats as $stat)
                        <span class="stat-badge">
                            <i class="bi {{ $stat['icon'] ?? 'bi-info-circle' }}"></i>
                            <span>{{ $stat['label'] }}:</span>
                            <span class="stat-value">{{ $stat['value'] }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if(isset($actions) && $actions->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>

