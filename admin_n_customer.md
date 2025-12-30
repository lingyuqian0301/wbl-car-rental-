# 👥 User Role System Guide

## ✅ **Role System Implemented!**

The application now has a **role-based access control system** with two roles:
- **Customer** - Default role for all new registrations
- **Admin** - For staff who can verify payments

---

## 🔐 **How Registration Works**

### **Default Behavior:**
- ✅ **All new registrations are automatically set as "Customer"**
- ✅ Customers can:
  - View their bookings
  - Submit payments
  - Download invoices (after payment verification)

### **Admin Access:**
- 🔒 **Admin accounts must be created manually** (for security)
- ✅ Admins can:
  - Access Payment Verification page
  - Approve/Reject payments
  - View all pending payments

---

## 🛠️ **How to Create an Admin User**

### **Option 1: Using Tinker (Recommended)**

```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::where('email', 'admin@hasta.com')->first();
$user->update(['role' => 'admin']);
```

Or create a new admin:
```php
$admin = \App\Models\User::create([
    'name' => 'Admin Name',
    'email' => 'admin@hasta.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
```

### **Option 2: Using Database Directly**

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `db_laracrud`
3. Go to `users` table
4. Find the user you want to make admin
5. Edit the `role` field
6. Change from `customer` to `admin`
7. Save

### **Option 3: Using SQL**

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@hasta.com';
```

---

## 🔒 **Security Features**

### **Admin Routes Protection:**
- ✅ All `/admin/*` routes are protected by `admin` middleware
- ✅ Non-admin users get **403 Forbidden** error if they try to access
- ✅ Navigation links only show to admins

### **Middleware:**
- `EnsureUserIsAdmin` middleware checks if user is admin
- Automatically redirects/denies access if not admin

---

## 📋 **Role Values**

The `role` field in the `users` table accepts:
- `customer` - Default for all registrations
- `admin` - For staff members

---

## 🎯 **What Each Role Can Do**

### **Customer Role:**
- ✅ Register new account (automatically gets customer role)
- ✅ View own bookings
- ✅ Submit payments
- ✅ View payment status
- ✅ Download invoices (after verification)
- ❌ Cannot access admin pages
- ❌ Cannot verify payments

### **Admin Role:**
- ✅ Everything customers can do
- ✅ Access Payment Verification page
- ✅ View all pending payments
- ✅ Approve payments
- ✅ Reject payments with reason
- ✅ See "Payment Verification" in navigation

---

## 🧪 **Testing the Role System**

### **Test 1: Customer Registration**
1. Go to `/register`
2. Register a new account
3. ✅ Should automatically be set as "customer"
4. ✅ Should NOT see "Payment Verification" in navigation
5. ❌ Should get 403 error if trying to access `/admin/payments`

### **Test 2: Admin Access**
1. Create an admin user (using methods above)
2. Login as admin
3. ✅ Should see "Payment Verification" in navigation
4. ✅ Should be able to access `/admin/payments`
5. ✅ Should be able to verify payments

### **Test 3: Role Switching**
1. Login as customer
2. Use tinker to change role to admin:
   ```php
   $user = \App\Models\User::find(1);
   $user->update(['role' => 'admin']);
   ```
3. Refresh page
4. ✅ Should now see admin navigation
5. ✅ Should be able to access admin pages

---

## 📝 **Database Schema**

### **Users Table:**
```sql
role ENUM('customer', 'admin') DEFAULT 'customer'
```

### **Migration:**
- Migration file: `2025_12_07_115851_add_role_to_users_table.php`
- Adds `role` field with default value `'customer'`

---

## 🔄 **Updating Existing Users**

If you have existing users without roles:

```bash
php artisan tinker
```

```php
// Set all existing users to customer (if null)
\App\Models\User::whereNull('role')->update(['role' => 'customer']);

// Or set specific user to admin
\App\Models\User::where('email', 'admin@hasta.com')->update(['role' => 'admin']);
```

---

## 💡 **Best Practices**

1. **Limit Admin Accounts:**
   - Only create admin accounts for trusted staff
   - Don't allow self-registration as admin

2. **Regular Audits:**
   - Periodically check who has admin access
   - Remove admin access when staff leave

3. **Strong Passwords:**
   - Ensure admin accounts have strong passwords
   - Consider 2FA for admin accounts (future enhancement)

---

## 🚀 **Quick Commands**

### **Check User Role:**
```php
$user = \App\Models\User::find(1);
$user->role; // 'customer' or 'admin'
$user->isAdmin(); // true or false
$user->isCustomer(); // true or false
```

### **List All Admins:**
```php
\App\Models\User::where('role', 'admin')->get();
```

### **List All Customers:**
```php
\App\Models\User::where('role', 'customer')->get();
```

---

## ✅ **Summary**

- ✅ **Registration:** All new users are automatically "customer"
- ✅ **Admin Creation:** Must be done manually (for security)
- ✅ **Access Control:** Admin routes protected by middleware
- ✅ **Navigation:** Admin links only show to admins
- ✅ **Security:** Non-admins get 403 error on admin routes

The role system is now fully functional! 🎉

