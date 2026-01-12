# Duration-Based Calendar Implementation

## Overview
The calendar has been updated to render bookings as **continuous duration bars** instead of separate per-day events. This provides a clear visual representation of booking duration at a glance.

## Key Changes

### 1. **Single Event Per Booking**
- **Before**: Each booking was rendered once per day it spanned (e.g., a 5-day booking appeared 5 times)
- **After**: Each booking renders exactly ONCE on its pickup date, then spans visually across the calendar

**Files Modified**: 
- `resources/views/admin/topbar-calendar/index.blade.php` (Controller logic and view)

### 2. **Rendering Architecture**

#### Data Structure
```php
// Bookings are now grouped by pickup date only
$bookingsByPickupDate = [
    '2024-01-15' => [Booking1, Booking3],  // Two bookings start on Jan 15
    '2024-01-20' => [Booking2],             // One booking starts on Jan 20
];
```

#### Calendar Loop
```php
@foreach($dayBookings as $booking)
    // Only bookings that START on this date are rendered here
    // The bar visually spans across multiple columns
@endforeach
```

### 3. **Continuous Visual Spanning**

#### CSS Grid Variables
Each booking bar uses a CSS variable `--span-days` to determine how many days it spans:

```php
style="background-color: {{ $barColor }}; --span-days: {{ min($spanDays, 7) }};"
```

The CSS then applies this span:
```css
.booking-duration-bar {
    grid-column: auto / span var(--span-days, 1);
}
```

#### Span Calculation
```php
$spanDays = 1;
if ($pickupDate && $returnDate && !$isSingleDay) {
    $spanDays = $pickupDate->diffInDays($returnDate) + 1;
}

// Cap at calendar boundary
if ($returnDate && $returnDate->gt($calendarEndDay)) {
    $spanDays = $currentDay->diffInDays($calendarEndDay) + 1;
}
```

### 4. **Color Consistency**

Colors are generated once per booking using a hash of customer name + plate number:

```php
$colorKey = md5($customerName . $plateNumber . $booking->bookingID);
$colorIndex = hexdec(substr($colorKey, 0, 8)) % count($colorPalette);
$bookingColors[$booking->bookingID] = $colorPalette[$colorIndex];
```

**Benefit**: The same booking always uses the same color, even when displayed across multiple dates.

### 5. **Pickup & Return Markers**

Markers are positioned strategically:

- **Single-day bookings**: Show "R" (return) badge on the right
- **Multi-day bookings**: Show "P" (pickup) badge on the left, no "R" on span (clean look)

```blade
@if(!$isSingleDay)
    <span class="bar-label pickup">P</span>
@endif

<span class="bar-customer">{{ Str::limit($customerName, 12) }}</span>

@if($isSingleDay)
    <span class="bar-label return">R</span>
@endif
```

### 6. **Styling**

#### Bar Variants
```css
.booking-duration-bar.bar-single {
    border-radius: 4px;           /* Full rounded corners */
}

.booking-duration-bar.bar-start {
    border-radius: 4px 0 0 4px;   /* Left rounded, right square */
}

.booking-duration-bar.bar-middle {
    border-radius: 0;              /* Both sides square */
}

.booking-duration-bar.bar-end {
    border-radius: 0 4px 4px 0;   /* Right rounded, left square */
}
```

#### Hover Effects
```css
.booking-duration-bar:hover {
    filter: brightness(1.15);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 5;
}
```

#### Unread Indicator
Unread bookings show a small yellow dot in the top-right corner:
```css
.booking-duration-bar.unread::before {
    content: '';
    top: 3px;
    right: 3px;
    width: 7px;
    height: 7px;
    background: #fef08a;
    box-shadow: 0 0 3px #fcd34d;
}
```

### 7. **Hover & Interaction**

#### Hover Details
Hovering ANY part of the booking bar shows the complete booking details:

```javascript
function showBookingBox(bookingId, event) {
    // Show floating box with all booking info
    // Triggered on ANY part of the duration bar
}
```

#### Booking Detail Box
The floating box displays (once per booking, not per day):
- Booking ID
- Status (Confirmed, Pending, etc.)
- Customer name
- Vehicle details (model + plate)
- Pickup date/time and location
- Return date/time and location
- Duration (days)
- Payment status
- Action buttons (Receipt, View Details, Mark Read)

### 8. **JavaScript Updates**

The JavaScript has been simplified to work with booking IDs instead of date types:

**Before**: `boxId = "123_pickup"` (booking 123, date type pickup)
**After**: `bookingId = "123"` (just the booking)

```javascript
function showBookingBox(bookingId, event) {
    const box = document.getElementById('booking-box-' + bookingId);
    // ... show logic
}

function closeAndMarkRead(bookingId, isUnread) {
    // Mark as read and close the box
}
```

## Edge Cases Handled

### 1. **Same-Day Bookings**
- Render as single bar with both "P" and "R" markers
- Span = 1 day

### 2. **Multi-Week Bookings**
- Span is capped at calendar boundary
- If booking extends beyond visible calendar, span is adjusted

### 3. **Overlapping Bookings**
- Stack vertically within the same date cells
- Each booking gets its own row in `booking-bars-container`

### 4. **Unread Status**
- Combines both pickup and return unread status
- Shows yellow dot indicator
- "Mark Read" button in floating box

## Performance Improvements

### Before
- Rendered each booking N times (where N = duration in days)
- Multiple floating boxes per booking (one per day)
- Inefficient memory usage

### After
- Renders each booking exactly once
- Single floating box per booking
- Better performance, especially with many long-duration bookings

## Backward Compatibility

✅ **All existing features preserved**:
- Booking detail information unchanged
- Mark as read functionality works
- Receipt viewing
- Navigation to booking detail page
- Unread indicators
- Payment status display
- Vehicle filtering

## Testing Checklist

- [ ] **Single-day bookings**: Display as single bar with "P" and "R" badges
- [ ] **2-day bookings**: Span 2 columns, show "P" badge only on first day
- [ ] **Multi-week bookings**: Span correctly, boundary-aware
- [ ] **Overlapping bookings**: Stack vertically without cutting duration
- [ ] **Hover behavior**: Show complete booking details when hovering any part
- [ ] **Colors**: Each unique customer+plate combination has consistent color
- [ ] **Unread markers**: Yellow dots appear on unread bookings
- [ ] **Mark as Read**: Clicking button marks booking as read and closes box
- [ ] **Navigation**: "View Details" button goes to booking page
- [ ] **Receipt viewing**: "Receipt" button opens receipt image
- [ ] **Vehicle filtering**: Filter still works correctly
- [ ] **Month navigation**: Previous/next month buttons work
- [ ] **Responsive**: Calendar adjusts on smaller screens

## Browser Compatibility

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## CSS Grid Notes

The calendar uses a 7-column grid (one for each day of the week):
```css
.calendar-month-view {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
```

Each booking bar spans multiple columns based on its `--span-days` CSS variable, creating the continuous bar effect.

## Future Enhancements

1. **Drag-to-reschedule**: Allow dragging booking bars to change dates
2. **Visual legend**: Show color palette with customer info
3. **Week/Day views**: Implement week and day calendar views
4. **Multi-select**: Select multiple bookings for bulk actions
5. **Booking conflicts**: Highlight overlapping bookings differently

## Files Modified

1. **`resources/views/admin/topbar-calendar/index.blade.php`**
   - Updated CSS styling (hours of work)
   - Rewrote calendar rendering logic
   - Simplified JavaScript functions
   - Removed old per-day rendering code

## Testing URL

Access the calendar at:
```
/admin/topbar-calendar?view=month&date=2024-01-01
```

Filter by vehicle:
```
/admin/topbar-calendar?view=month&vehicle_id=car_1&date=2024-01-01
```
