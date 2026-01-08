# Booking Stepper Visual Guide

## Component Behavior by Page

### Page: Vehicle Selection (`/vehicles/{id}`)
**Route Name**: `vehicles.show`
```
[1] ← ACTIVE      [2]            [3]            [4]            [5]            [6]
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
              ─────────────────────────────────────────────────────────────────
  Detected as: Step 1 (Active)
  Visual State: Red circle with number "1", bold label
```

### Page: Booking Confirmation (`/booking/confirm`)
**Route Name**: `booking.confirm`
```
[✓]            [2] ← ACTIVE     [3]            [4]            [5]            [6]
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
  ✓ ─────────── 
  Detected as: Step 2 (Active)
  Visual State: Step 1 shows green ✓, Step 2 red circle
```

### Page: Payment (`/payments/create/{booking}`)
**Route Name**: `payments.create`
```
[✓]            [✓]            [3] ← ACTIVE   [4]            [5]            [6]
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
  ✓ ─────────── ✓ ─────────────
  Detected as: Step 3 (Active)
  Visual State: Steps 1-2 show green ✓, Step 3 red circle
```

### Page: Agreement (`/agreement/{booking}`)
**Route Name**: `agreement.show`
```
[✓]            [✓]            [✓]            [4] ← ACTIVE   [5]            [6]
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
  ✓ ─────────── ✓ ─────────────── ✓ ─────────────
  Detected as: Step 4 (Active)
  Visual State: Steps 1-3 show green ✓, Step 4 red circle
```

### Page: Pickup (Future - `pickup.show`)
```
[✓]            [✓]            [✓]            [✓]            [5] ← ACTIVE   [6]
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
  ✓ ─────────── ✓ ─────────────── ✓ ─────────────── ✓ ─────────────
  Detected as: Step 5 (Active)
  Visual State: Steps 1-4 show green ✓, Step 5 red circle
```

### Page: Return (Future - `return.show`)
```
[✓]            [✓]            [✓]            [✓]            [✓]            [6] ← ACTIVE
Select Vehicle   Booking       Payment       Agreement      Pickup        Return
  ✓ ─────────── ✓ ─────────────── ✓ ─────────────── ✓ ─────────────── ✓ ─────────────
  Detected as: Step 6 (Active)
  Visual State: Steps 1-5 show green ✓, Step 6 red circle
```

## Visual Appearance

### Step Circle States

#### Active (Current Step)
```
┌─────────┐
│    2    │  ← Bright red/orange (#dc2626)
│         │  ← 40px diameter circle
└─────────┘  ← Bold white text
    ↓
Animated pulse effect (2s cycle)
```

#### Completed (Past Steps)
```
┌─────────┐
│    ✓    │  ← Green (#059669)
│         │  ← 40px diameter circle
└─────────┘  ← Bold white text
    ↓
Shows checkmark
```

#### Upcoming (Future Steps)
```
┌─────────┐
│    5    │  ← Light gray (#e5e7eb)
│         │  ← 40px diameter circle
└─────────┘  ← Gray text (#6b7280)
    ↓
Muted appearance
```

## Color Scheme

```
CSS Variables Used:
─────────────────
Active Step:      #dc2626 (red) → linear-gradient(135deg, #dc2626 0%, #991b1b 100%)
Completed Step:   #059669 (green) → linear-gradient(135deg, #059669 0%, #047857 100%)
Upcoming Step:    #e5e7eb (light gray)
Text Primary:     #1e293b (dark)
Text Secondary:   #9ca3af (gray)
Border:           #e2e8f0
```

## Component Structure

```html
<nav class="booking-stepper" aria-label="Booking Progress">
  <ol class="booking-stepper-list">
    <!-- For each step: -->
    <li class="booking-stepper-step booking-stepper-step--active" data-step="2">
      <div class="booking-stepper-circle">
        <span class="booking-stepper-number">2</span>
      </div>
      <span class="booking-stepper-label">Booking Details</span>
      <div class="booking-stepper-line">
        <!-- Line to next step -->
      </div>
    </li>
  </ol>
</nav>
```

## Responsive Behavior

### Desktop (1200px+)
- Full 6-step display
- All labels visible
- Step circles: 40px
- Lines connecting steps visible

### Tablet (600px - 900px)
- Full 6-step display (may wrap)
- Smaller text (0.8rem instead of 0.9rem)
- Step circles: 36px
- Adjusted padding

### Mobile (< 600px)
- Full 6-step display (stacked/wrapped)
- Very small labels (0.65rem)
- Text truncation with ellipsis
- Step circles: 32px
- Lines hidden (vertical space)
- Minimal padding

## Animation Effects

### Pulse Animation (Active Step Only)
```css
@keyframes pulse-active {
  0%, 100% {
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
  }
  50% {
    box-shadow: 0 4px 20px rgba(220, 38, 38, 0.5);
  }
}
```

Runs infinitely on the active step's circle, drawing user attention to current progress.

## Accessibility Features

1. **Semantic HTML**: Uses `<nav>` and `<ol>` for proper structure
2. **ARIA Labels**: `aria-label="Booking Progress"` on nav
3. **ARIA Current**: `aria-current="step"` on active circle
4. **Screen Readers**: Navigate list items naturally
5. **Keyboard**: Full keyboard navigation support
6. **High Contrast**: Colors meet WCAG AA standards
7. **Text Labels**: Not just icons (accessible to screen readers)

## JavaScript Logic (Blade/PHP)

```php
// Route Detection
@php
    $currentStepNumber = 1; // Default
    foreach ($allSteps as $step) {
        if (request()->routeIs($step['routes'])) {
            $currentStepNumber = $step['number'];
            break;
        }
    }
@endphp

// For each step, determine its visual state:
@foreach ($allSteps as $step)
    @php
        $isCompleted = $step['number'] < $currentStepNumber;
        $isCurrent = $step['number'] === $currentStepNumber;
        $isFuture = $step['number'] > $currentStepNumber;
        $stepStatus = $isCompleted ? 'completed' : ($isCurrent ? 'active' : 'upcoming');
    @endphp
@endforeach
```

## Usage In Blade Templates

### Simple Usage (Auto-Detection)
```blade
{{-- Automatically detects current step from route --}}
<x-booking-stepper />
```

### In Layout or Component
```blade
@extends('layouts.app')

@section('content')
    {{-- Stepper auto-displays with correct step --}}
    <x-booking-stepper />
    
    {{-- Your page content below --}}
@endsection
```

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Full support (responsive)
- Print: Stepper hidden (print-friendly)

## Performance Considerations

- **Lightweight**: No JavaScript required (pure Blade + CSS)
- **Route Detection**: O(n) where n = number of steps (6) - negligible
- **CSS**: Minimal and optimized
- **No AJAX calls**: All processing server-side
- **Caching friendly**: No dynamic elements that prevent caching
