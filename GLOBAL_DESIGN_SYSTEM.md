# Global Design System - HASTA Travel

## Overview

All pages now inherit the **homepage's visual scale** automatically through `app.blade.php`. No page-specific sizing CSS needed.

---

## 🎯 Global Rules (Auto-Applied to All Pages)

### **Typography**
```css
html { font-size: 12px; }
body { line-height: 1.6; }
font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

**Result:** All text on all pages is compact and consistent.

### **Colors (CSS Variables)**
```css
:root {
    --primary-orange: #dc2626;
    --primary-dark-orange: #991b1b;
    --success-green: #059669;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --bg-light: #f8fafc;
    --error-red: #dc2626;
}
```

**Usage:** `color: var(--primary-orange);` in any page

### **Spacing**
```css
section { padding: 2rem 2rem 0.5rem 2rem; }
.container { max-width: 1280px; margin: 0 auto; padding: 2rem; }
```

**Result:** Consistent margins and padding across all pages.

### **Layout**
```css
body { display: flex; flex-direction: column; min-height: 100vh; }
main { flex: 1; }
footer { flex-shrink: 0; }
```

**Result:** Footer always sticks to bottom, main content grows.

---

## 📐 Using the Global System

### **Option 1: Use `.container` Class**

For standard content pages (Wallet, Loyalty, My Bookings, etc.):

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Page Title</h1>
    <p>Content here inherits global font-size: 12px, line-height: 1.6</p>
</div>
@endsection
```

**What `.container` provides:**
- Max-width: 1280px (centered)
- Padding: 2rem (auto-responsive on mobile)
- Full-width on mobile devices
- Automatically scales with font-size: 12px

### **Option 2: Use `<section>` Tags**

For multi-section layouts:

```blade
@extends('layouts.app')

@section('content')
<section>
    <h2>Section 1</h2>
    <p>Content here...</p>
</section>

<section>
    <h2>Section 2</h2>
    <p>More content...</p>
</section>
@endsection
```

**What `<section>` provides:**
- Padding: 2rem 2rem 0.5rem 2rem (auto-responsive)
- Consistent vertical spacing
- flex-shrink: 0 (maintains size)

### **Option 3: Custom Layout**

If you need custom styling for a specific page:

```blade
@extends('layouts.app')

@section('content')
<style>
    /* Page-specific styles only - inherits font-size: 12px automatically */
    .my-custom-card {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
    }

    .my-custom-card h3 {
        font-size: 1.25rem;  /* 15px because html { font-size: 12px } */
        margin-bottom: 1rem;
    }
</style>

<div class="container">
    <div class="my-custom-card">
        <!-- Content inherits global sizing -->
    </div>
</div>
@endsection
```

---

## 🔄 Font-Size Calculation (Important!)

**All font sizes scale with `html { font-size: 12px }`**

| Size in REM | Actual Size | Use Case |
|---|---|---|
| 0.85rem | 10.2px | Small text, labels |
| 0.9rem | 10.8px | Meta info, captions |
| 1rem | 12px | Body text (default) |
| 1.1rem | 13.2px | Subheadings |
| 1.25rem | 15px | Section titles |
| 1.5rem | 18px | Card titles |
| 1.875rem | 22.5px | Page titles |
| 2rem | 24px | Hero section title |
| 2.5rem | 30px | Large hero title |

**Example in page-specific CSS:**
```css
/* This will be 15px because html font-size is 12px */
.card-title { font-size: 1.25rem; }

/* This will be 10.8px */
.label { font-size: 0.9rem; }
```

---

## 📱 Responsive Behavior

### **On Mobile (≤768px)**

All these rules automatically apply:

```css
@media (max-width: 768px) {
    html { font-size: 12px; }           /* Stays compact */
    section { padding: 1.5rem 1rem; }  /* Reduced padding */
    .container { padding: 1.5rem; }    /* Reduced padding */
}
```

**Result:** Mobile pages look the same as desktop - compact, consistent, readable.

---

## ✅ Best Practices

### ✅ DO:

1. **Use `.container` for standard content:**
   ```blade
   <div class="container">
       <h1>Page Title</h1>
       <p>Content here</p>
   </div>
   ```

2. **Use `<section>` for multi-part layouts:**
   ```blade
   <section>
       <h2>Part 1</h2>
   </section>
   <section>
       <h2>Part 2</h2>
   </section>
   ```

3. **Reference CSS variables for colors:**
   ```css
   background-color: var(--primary-orange);
   color: var(--text-secondary);
   ```

4. **Use rem units for consistency:**
   ```css
   font-size: 1.25rem;  /* Scales with base 12px */
   padding: 1.5rem;     /* Scales with base 12px */
   ```

5. **Keep page styles minimal:**
   ```blade
   <style>
       /* Only page-specific styles here */
       .my-component { /* custom styles */ }
   </style>
   ```

---

### ❌ DON'T:

1. ❌ **Override global font-size:**
   ```css
   /* BAD - breaks consistency */
   html { font-size: 14px; }
   body { font-size: 16px; }
   ```

2. ❌ **Use px units everywhere:**
   ```css
   /* BAD - doesn't scale with base font-size */
   padding: 20px;
   font-size: 16px;
   ```

3. ❌ **Add inline styles for layout:**
   ```blade
   <!-- BAD - use .container class instead -->
   <div style="max-width: 1280px; margin: 0 auto; padding: 2rem;">
   ```

4. ❌ **Duplicate container styles in each page:**
   ```blade
   <!-- BAD - already in app.blade.php -->
   <style>
       .container { max-width: 1280px; ... }
   </style>
   ```

5. ❌ **Create page-specific color schemes:**
   ```css
   /* BAD - use CSS variables */
   background: #dc2626;  /* Use var(--primary-orange) instead */
   ```

---

## 📋 Page Structure Template

### **Simple Content Page**
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Page Title</h1>
    <p>Standard content with global sizing...</p>
</div>
@endsection
```

### **Multi-Section Page**
```blade
@extends('layouts.app')

@section('content')
<style>
    /* Page-specific styles only */
    .special-card { background: white; padding: 1rem; }
</style>

<section>
    <div class="container">
        <h2>Section 1</h2>
    </div>
</section>

<section style="background: #f0f0f0;">
    <div class="container">
        <h2>Section 2</h2>
    </div>
</section>
@endsection
```

### **Page with Header**
```blade
@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1>Page Title</h1>
    </div>
</div>

<div class="container">
    <p>Content here...</p>
</div>
@endsection
```

---

## 🎨 Global CSS Variables

Available everywhere (every page inherits them):

```css
/* Primary Colors */
--primary-orange: #dc2626
--primary-dark-orange: #991b1b

/* Semantic Colors */
--success-green: #059669
--error-red: #dc2626

/* Text Colors */
--text-primary: #1e293b
--text-secondary: #64748b

/* Borders & Backgrounds */
--border-color: #e2e8f0
--bg-light: #f8fafc
```

**Usage in any page:**
```css
button {
    background-color: var(--primary-orange);
    color: white;
}

.error { color: var(--error-red); }
.success { color: var(--success-green); }
```

---

## 🔍 Debugging Sizing Issues

If a page looks too large or too small:

1. **Check html font-size:**
   ```css
   html { font-size: 12px; }  /* Should always be 12px globally */
   ```

2. **Use dev tools to inspect:**
   - Open browser DevTools (F12)
   - Select element
   - Check "Computed" font-size
   - Should be 12px × multiplier (e.g., 1.25rem = 15px)

3. **Verify inheritance chain:**
   - Element inherits from parent
   - Parent inherits from body
   - Body inherits from html (font-size: 12px)

---

## 📦 Summary

| Component | Location | Scope |
|---|---|---|
| **Global Sizing** | `app.blade.php` | All pages |
| **Layout/Flexbox** | `app.blade.php` | All pages |
| **CSS Variables** | `app.blade.php` | All pages |
| **Responsive** | `app.blade.php` | All pages |
| **Page-specific** | Individual `.blade.php` | Single page |
| **Hero/Cards** | `welcome.blade.php` | Homepage only |

---

## ✨ Result

- ✅ All pages use **12px base font-size**
- ✅ All pages use **1.6 line-height**
- ✅ All pages use **consistent spacing**
- ✅ All pages use **same max-width (1280px)**
- ✅ All pages inherit **CSS variables**
- ✅ Footer always at **bottom**
- ✅ No duplication across pages
- ✅ Easy to maintain globally
- ✅ Mobile-responsive everywhere

