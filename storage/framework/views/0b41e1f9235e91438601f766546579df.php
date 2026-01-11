<?php $__env->startSection('title', 'Customer Management'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .customer-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .customer-info-text div {
        margin-bottom: 2px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <!-- Header -->
    <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Customer Management','description' => 'Manage all customer information','stats' => [
            ['label' => 'Total Customers', 'value' => $totalCustomers, 'icon' => 'bi-people'],
            ['label' => 'With Bookings', 'value' => $customersWithBookings, 'icon' => 'bi-calendar-check'],
            ['label' => 'New Today', 'value' => $totalCustomersToday, 'icon' => 'bi-person-plus']
        ],'date' => $today]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Customer Management','description' => 'Manage all customer information','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Total Customers', 'value' => $totalCustomers, 'icon' => 'bi-people'],
            ['label' => 'With Bookings', 'value' => $customersWithBookings, 'icon' => 'bi-calendar-check'],
            ['label' => 'New Today', 'value' => $totalCustomersToday, 'icon' => 'bi-person-plus']
        ]),'date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $attributes = $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $component = $__componentOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Action Buttons - Right Top Corner -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.customers.create')); ?>" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo e(route('admin.customers.export-pdf', request()->query())); ?>">
                        <i class="bi bi-file-pdf me-2"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('admin.customers.export-excel', request()->query())); ?>">
                        <i class="bi bi-file-excel me-2"></i> Export Excel
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-light text-danger" onclick="removeSelected()">
                <i class="bi bi-trash me-1"></i> Remove
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.manage.client')); ?>" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="ID, Name, Email, Phone, Matric No">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="name_asc" <?php echo e($sortBy === 'name_asc' ? 'selected' : ''); ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo e($sortBy === 'name_desc' ? 'selected' : ''); ?>>Name (Z-A)</option>
                        <option value="latest_booking" <?php echo e($sortBy === 'latest_booking' ? 'selected' : ''); ?>>Latest Booking</option>
                        <option value="highest_rental" <?php echo e($sortBy === 'highest_rental' ? 'selected' : ''); ?>>Most Rental Time</option>
                    </select>
                </div>
                
                <!-- Faculty Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Faculty</label>
                    <select name="faculty" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($faculty); ?>" <?php echo e($faculty === request('faculty') ? 'selected' : ''); ?>>
                                <?php echo e($faculty); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- College Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">College</label>
                    <select name="college" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $colleges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $college): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($college); ?>" <?php echo e($college === request('college') ? 'selected' : ''); ?>>
                                <?php echo e($college); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Booking Count Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Booking Count</label>
                    <input type="number" name="booking_count" value="<?php echo e($bookingCount); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="Min bookings">
                </div>
                
                <!-- Customer Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Status</label>
                    <select name="customer_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" <?php echo e($customerStatus === 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="blacklisted" <?php echo e($customerStatus === 'blacklisted' ? 'selected' : ''); ?>>Blacklisted</option>
                        <option value="deleted" <?php echo e($customerStatus === 'deleted' ? 'selected' : ''); ?>>Deleted</option>
                    </select>
                </div>
                
                <!-- Customer Nation Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Nation</label>
                    <select name="customer_nation" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="local" <?php echo e($customerNation === 'local' ? 'selected' : ''); ?>>Local</option>
                        <option value="international" <?php echo e($customerNation === 'international' ? 'selected' : ''); ?>>International</option>
                    </select>
                </div>
                
                <!-- Customer Type Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Type</label>
                    <select name="customer_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="student" <?php echo e($customerType === 'student' ? 'selected' : ''); ?>>Student</option>
                        <option value="staff" <?php echo e($customerType === 'staff' ? 'selected' : ''); ?>>Staff</option>
                    </select>
                </div>
                
                <!-- Filter Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
                
                <!-- Clear Button -->
                <?php if($search || $faculty || $college || $bookingCount || $customerStatus || $customerNation || $customerType): ?>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?php echo e(route('admin.manage.client')); ?>" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customers</h5>
            <span class="badge bg-light text-dark"><?php echo e($customers->total()); ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Date Registered</th>
                            <th>Status</th>
                            <th>No of Booking Time</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $user = $customer->user;
                                
                                // Get customer details
                                $username = $user->username ?? 'N/A';
                                $dob = $user->DOB ?? null;
                                $dobYear = $dob ? \Carbon\Carbon::parse($dob)->format('Y') : 'N/A';
                                $address = $customer->address ?? 'N/A';
                                $stateCountry = $customer->local->stateOfOrigin ?? $customer->international->countryOfOrigin ?? 'N/A';
                                $license = $customer->customer_license ?? 'N/A';
                                $icPassport = $customer->local->ic_no ?? $customer->international->passport_no ?? 'N/A';
                                $emergencyContact = $customer->emergency_contact ?? 'N/A';
                                
                                // Student/Staff details
                                // Get student details from LocalStudent/InternationalStudent -> StudentDetails relationship
                                $localStudentDetails = $customer->localStudent->studentDetails ?? null;
                                $internationalStudentDetails = $customer->internationalStudent->studentDetails ?? null;
                                $college = $localStudentDetails->college ?? ($internationalStudentDetails->college ?? 'N/A');
                                $faculty = $localStudentDetails->faculty ?? ($internationalStudentDetails->faculty ?? 'N/A');
                                $matricStaffNo = $customer->localStudent->matric_number ?? ($customer->internationalStudent->matric_number ?? ($customer->localUtmStaff->staffID ?? ($customer->internationalUtmStaff->staffID ?? 'N/A')));
                                $programme = $localStudentDetails->programme ?? ($internationalStudentDetails->programme ?? 'N/A');
                                $yearOfStudy = $localStudentDetails->yearOfStudy ?? ($internationalStudentDetails->yearOfStudy ?? 'N/A');
                                
                                $dateRegistered = $user->dateRegistered ?? null;
                            ?>
                            <tr class="<?php echo e($customer->customer_status === 'blacklist' ? 'table-danger' : ($customer->customer_status === 'deleted' ? 'table-secondary' : '')); ?>">
                                <td>
                                    <input type="checkbox" class="customer-checkbox" value="<?php echo e($customer->customerID); ?>">
                                </td>
                                <td>
                                    <strong>#<?php echo e($customer->customerID); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo e($user->name ?? 'Unknown'); ?></strong>
                                    <div class="customer-info-text">
                                        <div><strong>Username:</strong> <?php echo e($username); ?></div>
                                        <?php if($dob): ?>
                                        <div><strong>DOB:</strong> <?php echo e(\Carbon\Carbon::parse($dob)->format('d M Y')); ?> (<?php echo e($dobYear); ?>)</div>
                                        <?php endif; ?>
                                        <div><strong>Address:</strong> <?php echo e($address); ?></div>
                                        <div><strong><?php echo e($customer->local ? 'State of Origin' : 'Country of Origin'); ?>:</strong> <?php echo e($stateCountry); ?></div>
                                        <div><strong>Customer License:</strong> <?php echo e($license); ?></div>
                                        <div><strong>Emergency Contact:</strong> <?php echo e($emergencyContact); ?></div>
                                        <div><strong><?php echo e($customer->local ? 'IC No' : 'Passport No'); ?>:</strong> <?php echo e($icPassport); ?></div>
                                        <?php if($college !== 'N/A'): ?>
                                        <div><strong>College:</strong> <?php echo e($college); ?></div>
                                        <?php endif; ?>
                                        <?php if($faculty !== 'N/A'): ?>
                                        <div><strong>Faculty:</strong> <?php echo e($faculty); ?></div>
                                        <?php endif; ?>
                                        <?php if($matricStaffNo !== 'N/A'): ?>
                                        <div><strong><?php echo e(($customer->localStudent || $customer->internationalStudent) ? 'Matric No' : 'Staff No'); ?>:</strong> <?php echo e($matricStaffNo); ?></div>
                                        <?php endif; ?>
                                        <?php if($programme !== 'N/A'): ?>
                                        <div><strong>Programme:</strong> <?php echo e($programme); ?></div>
                                        <?php endif; ?>
                                        <?php if($yearOfStudy !== 'N/A'): ?>
                                        <div><strong>Year of Study:</strong> <?php echo e($yearOfStudy); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($dateRegistered): ?>
                                        <?php echo e(\Carbon\Carbon::parse($dateRegistered)->format('d M Y')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo e(($user->isActive ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                                        <?php echo e(($user->isActive ?? false) ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo e($customer->bookings_count ?? 0); ?></strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('admin.customers.show', $customer)); ?>" 
                                           class="btn btn-outline-primary" title="View Customer">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="<?php echo e(route('admin.customers.edit', $customer)); ?>" 
                                           class="btn btn-outline-secondary" title="Edit Customer">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No customers found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($customers->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($customers->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.customer-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    function removeSelected() {
        const selected = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one customer.');
            return;
        }
        if (confirm(`Are you sure you want to delete ${selected.length} customer(s)?\n\nNote: Customers with existing bookings cannot be deleted.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route('admin.customers.delete-selected')); ?>';
            form.innerHTML = `
                <?php echo csrf_field(); ?>
                ${selected.map(id => `<input type="hidden" name="selected_customers[]" value="${id}">`).join('')}
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/customers/index.blade.php ENDPATH**/ ?>