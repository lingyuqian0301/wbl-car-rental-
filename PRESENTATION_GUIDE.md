# 🎯 Payment & Billing Modules - Presentation Guide

## 🚀 Server Status
✅ Laravel server is running at: **http://localhost:8000**

---

## 📋 Presentation Flow

### **1. Customer Payment Module (UC009)**

**URL:** `http://localhost:8000/payments/create/{booking_id}`

**Steps:**
1. Login as a customer
2. Navigate to payment page (you'll need a booking ID)
3. Show:
   - ✅ Booking summary card
   - ✅ Deposit calculation logic (< 15 days = RM 50, ≥ 15 days = 100%)
   - ✅ Bank transfer details (Maybank)
   - ✅ Payment form with file upload
4. Submit payment → Status: "Pending"

**Key Features to Highlight:**
- Professional Bootstrap 5 UI with Hasta Travel branding
- Automatic deposit calculation based on rental duration
- Secure file upload (JPG, PNG, PDF, max 2MB)
- Form validation

---

### **2. Staff Payment Verification (UC008)**

**URL:** `http://localhost:8000/admin/payments`

**Steps:**
1. Login as staff/admin
2. Go to Admin Payments page
3. Show:
   - ✅ List of pending payments
   - ✅ Payment details with receipt image
   - ✅ Approve button → Updates payment & booking status
   - ✅ Reject button → Requires reason input
4. Demonstrate approval process

**Key Features to Highlight:**
- Real-time payment verification workflow
- Image/PDF receipt viewing
- Status badges (Yellow=Pending, Green=Verified, Red=Rejected)
- Automatic booking status update on approval

---

### **3. Invoice Generation (UC013)**

**URL:** `http://localhost:8000/invoices/generate/{booking_id}`

**Steps:**
1. Ensure booking has verified payment
2. Access invoice generation URL
3. Show:
   - ✅ Professional PDF invoice
   - ✅ Company header with registration number
   - ✅ Payment summary (Deposit, Balance Due)
   - ✅ Payment history

**Key Features to Highlight:**
- Professional invoice design
- Automatic PDF generation
- Only available for verified payments
- Downloadable invoice

---

## 🧪 Quick Test Setup

### Create Test Data via Tinker:

```bash
php artisan tinker
```

Then run:

```php
// 1. Create Vehicle
$vehicle = \App\Models\Vehicle::create([
    'brand' => 'Toyota',
    'model' => 'Vios',
    'registration_number' => 'ABC1234',
    'daily_rate' => 150.00,
    'status' => 'Available',
]);

// 2. Create Booking (replace 1 with your user ID)
$booking = \App\Models\Booking::create([
    'user_id' => 1,
    'vehicle_id' => $vehicle->id,
    'start_date' => now()->addDays(7),
    'end_date' => now()->addDays(14),
    'duration_days' => 7,
    'total_price' => 1050.00,
    'status' => 'Pending',
]);

// 3. Create Payment
$payment = \App\Models\Payment::create([
    'booking_id' => $booking->id,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'status' => 'Pending',
    'payment_date' => now(),
]);
```

---

## 📍 Important URLs

| Feature | URL | Access Level |
|---------|-----|--------------|
| Customer Payment | `/payments/create/{booking_id}` | Customer |
| Admin Payments List | `/admin/payments` | Staff/Admin |
| Payment Details | `/admin/payments/{payment_id}` | Staff/Admin |
| Generate Invoice | `/invoices/generate/{booking_id}` | Customer/Staff |

---

## 🎨 Design Features

- **Color Scheme:** Maroon (#800020) and White
- **Framework:** Bootstrap 5
- **Responsive:** Mobile and Desktop compatible
- **Status Badges:** Color-coded (Yellow/Green/Red)

---

## ✅ Checklist Before Presentation

- [ ] Server running (`php artisan serve`)
- [ ] Database migrated (`php artisan migrate`)
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Test data created (vehicles, bookings, payments)
- [ ] Test user accounts created (customer & staff)
- [ ] Sample receipt image ready for upload

---

## 💡 Presentation Tips

1. **Start with Customer Flow:** Show how customers submit payments
2. **Then Staff Flow:** Show how staff verifies payments
3. **End with Invoice:** Show the final deliverable (PDF invoice)
4. **Highlight Security:** Mention file validation, CSRF protection
5. **Show Business Logic:** Explain deposit calculation rules

---

## 🐛 Troubleshooting

**If routes don't work:**
- Check: `php artisan route:list` to see all routes
- Clear cache: `php artisan route:clear`

**If images don't display:**
- Verify storage link: `php artisan storage:link`
- Check file permissions

**If PDF doesn't generate:**
- Ensure payment is verified
- Check dompdf is installed: `composer show barryvdh/laravel-dompdf`

