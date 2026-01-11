<?php $__env->startSection('title', 'Motorcycles'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.35rem 0.6rem;
        font-size: 1rem;
        font-weight: 600;
    }
    .status-btn {
        min-width: 100px;
        font-weight: 500;
    }
    .status-available {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }
    .status-available:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
    }
    .status-rented {
        background-color: #ffc107;
        color: #000;
        border-color: #ffc107;
    }
    .status-rented:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #000;
    }
    .status-maintenance {
        background-color: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }
    .status-maintenance:hover {
        background-color: #138496;
        border-color: #117a8b;
        color: white;
    }
    .status-unavailable {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    .status-unavailable:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: white;
    }
    .status-unknown {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    .status-unknown:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: white;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Motorcycles Management','description' => 'Manage all motorcycle vehicles','stats' => [
            ['label' => 'Total Motorcycles', 'value' => $totalMotorcycles, 'icon' => 'bi-bicycle'],
            ['label' => 'Available', 'value' => $totalAvailable, 'icon' => 'bi-check-circle'],
            ['label' => 'Rented', 'value' => $totalRented, 'icon' => 'bi-calendar-check']
        ],'date' => $today]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Motorcycles Management','description' => 'Manage all motorcycle vehicles','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Total Motorcycles', 'value' => $totalMotorcycles, 'icon' => 'bi-bicycle'],
            ['label' => 'Available', 'value' => $totalAvailable, 'icon' => 'bi-check-circle'],
            ['label' => 'Rented', 'value' => $totalRented, 'icon' => 'bi-calendar-check']
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
            <a href="<?php echo e(route('admin.vehicles.motorcycles.create')); ?>" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-plus-circle me-1"></i> Create New
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo e(route('admin.vehicles.motorcycles.export-pdf', request()->query())); ?>">
                        <i class="bi bi-file-pdf me-2"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('admin.vehicles.motorcycles.export-excel', request()->query())); ?>">
                        <i class="bi bi-file-excel me-2"></i> Export Excel
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-light text-danger" onclick="deleteSelected()">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <!-- Search, Sort and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.vehicles.motorcycles')); ?>" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="Brand, Model, Plate Number">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="vehicle_id_asc" <?php echo e($sortBy === 'vehicle_id_asc' ? 'selected' : ''); ?>>Asc Vehicle ID</option>
                        <option value="highest_rented" <?php echo e($sortBy === 'highest_rented' ? 'selected' : ''); ?>>Highest Rented</option>
                        <option value="highest_rental_price" <?php echo e($sortBy === 'highest_rental_price' ? 'selected' : ''); ?>>Highest Rental Price</option>
                        <option value="plate_no_asc" <?php echo e($sortBy === 'plate_no_asc' ? 'selected' : ''); ?>>Asc Plate No</option>
                    </select>
                </div>
                
                <!-- Filters -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="filter_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php echo e($filterStatus === $status ? 'selected' : ''); ?>><?php echo e(ucfirst($status)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Is Active</label>
                    <select name="filter_isactive" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" <?php echo e(($filterIsActive ?? '') == '1' ? 'selected' : ''); ?>>Active</option>
                        <option value="0" <?php echo e(($filterIsActive ?? '') == '0' ? 'selected' : ''); ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="<?php echo e(route('admin.vehicles.motorcycles')); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Motorcycles List -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Motorcycles</h5>
            <span class="badge bg-light text-dark"><?php echo e($motorcycles->total()); ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Vehicle ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Plate Number</th>
                        <th>Motor Type</th>
                        <th>Rental Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $motorcycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorcycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="motorcycle-checkbox" value="<?php echo e($motorcycle->vehicleID); ?>">
                            </td>
                            <td><strong>#<?php echo e($motorcycle->vehicleID); ?></strong></td>
                            <td><?php echo e($motorcycle->vehicle_brand ?? 'N/A'); ?></td>
                            <td><?php echo e($motorcycle->vehicle_model ?? 'N/A'); ?></td>
                            <td><?php echo e($motorcycle->plate_number ?? 'N/A'); ?></td>
                            <td><?php echo e($motorcycle->motor_type ?? 'N/A'); ?></td>
                            <td>RM <?php echo e(number_format($motorcycle->rental_price ?? 0, 2)); ?></td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm status-btn status-<?php echo e($motorcycle->availability_status ?? 'unknown'); ?>"
                                        data-vehicle-id="<?php echo e($motorcycle->vehicleID); ?>"
                                        data-current-status="<?php echo e($motorcycle->availability_status ?? 'unknown'); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#statusChangeModal"
                                        onclick="openStatusModal(this)">
                                    <?php echo e(ucfirst($motorcycle->availability_status ?? 'Unknown')); ?>

                                </button>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo e(route('admin.vehicles.show', $motorcycle->vehicleID)); ?>" 
                                       class="btn btn-sm btn-outline-info" title="View Motorcycle Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?php echo e(route('admin.vehicles.motorcycles.edit', $motorcycle->vehicleID)); ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Motorcycle">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteMotorcycle(<?php echo e($motorcycle->vehicleID); ?>, '<?php echo e($motorcycle->plate_number); ?>')" 
                                            class="btn btn-sm btn-outline-danger" title="Delete Motorcycle">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No motorcycles found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($motorcycles->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($motorcycles->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Update Confirmation Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Current Status: <strong id="currentStatusText"></strong></p>
                <label class="form-label">Select New Status:</label>
                <select class="form-select" id="newStatusSelect">
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmStatusChange">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Select All Checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.motorcycle-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Status Change with Confirmation
    let pendingStatusChange = null;

    function openStatusModal(button) {
        let vehicleId = button.dataset.vehicleId;
        let currentStatus = button.dataset.currentStatus;
        
        document.getElementById('currentStatusText').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
        document.getElementById('newStatusSelect').value = currentStatus;
        
        pendingStatusChange = {
            button: button,
            vehicleId: vehicleId,
            oldStatus: currentStatus
        };
    }

    document.getElementById('confirmStatusChange')?.addEventListener('click', function() {
        if (!pendingStatusChange) return;
        
        let newStatus = document.getElementById('newStatusSelect').value;
        
        if (pendingStatusChange.oldStatus === newStatus) {
            bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
            return;
        }
        
        fetch(`/admin/vehicles/${pendingStatusChange.vehicleId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({
                availability_status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button
                let btn = pendingStatusChange.button;
                btn.dataset.currentStatus = newStatus;
                btn.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                btn.className = `btn btn-sm status-btn status-${newStatus}`;
                
                bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
            } else {
                alert('Failed to update status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status.');
        });
        
        pendingStatusChange = null;
    });

    // Delete Selected
    function deleteSelected() {
        let selected = Array.from(document.querySelectorAll('.motorcycle-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one motorcycle to delete.');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selected.length} motorcycle(s)?\n\nNote: Motorcycles with existing bookings cannot be deleted.`)) {
            return;
        }
        
        // Delete each selected motorcycle
        let deletePromises = selected.map(vehicleId => {
            return fetch(`<?php echo e(url('/admin/vehicles/motorcycles')); ?>/${vehicleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to delete motorcycle');
                    });
                }
                return response.json();
            });
        });
        
        Promise.all(deletePromises)
            .then(() => {
                alert('Selected motorcycles deleted successfully.');
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                location.reload();
            });
    }

    // Delete Single Motorcycle
    function deleteMotorcycle(vehicleId, plateNumber) {
        if (!confirm(`Are you sure you want to delete motorcycle with plate number "${plateNumber}"?\n\nNote: Motorcycles with existing bookings cannot be deleted.`)) {
            return;
        }
        
        fetch(`<?php echo e(url('/admin/vehicles/motorcycles')); ?>/${vehicleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to delete motorcycle');
                });
            }
            return response.json();
        })
        .then(data => {
            alert('Motorcycle deleted successfully.');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/vehicles/motorcycles.blade.php ENDPATH**/ ?>