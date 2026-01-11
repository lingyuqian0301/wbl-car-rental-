<?php $__env->startSection('title', 'Vehicle Maintenance'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .maintenance-img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    .maintenance-img:hover {
        opacity: 0.8;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="<?php echo e(route('admin.vehicles.show', $vehicle->vehicleID)); ?>?tab=car-info" class="nav-link">
                <i class="bi bi-info-circle"></i> Car Info
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="<?php echo e(route('admin.vehicles.show', $vehicle->vehicleID)); ?>?tab=owner-info" class="nav-link">
                <i class="bi bi-person"></i> Owner Info
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                <i class="bi bi-tools"></i> Maintenance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="<?php echo e(route('admin.vehicles.fuel', $vehicle->vehicleID)); ?>" class="nav-link">
                <i class="bi bi-fuel-pump"></i> Fuel
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="<?php echo e(route('admin.vehicles.show', $vehicle->vehicleID)); ?>?tab=booking-history" class="nav-link">
                <i class="bi bi-clock-history"></i> Booking History
            </a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1"><?php echo e($vehicle->vehicle_brand ?? 'N/A'); ?> <?php echo e($vehicle->vehicle_model ?? 'N/A'); ?></h1>
            <div class="text-muted small">
                Plate Number: <?php echo e($vehicle->plate_number ?? 'N/A'); ?> · Vehicle ID: #<?php echo e($vehicle->vehicleID); ?>

            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.vehicles.show', $vehicle->vehicleID)); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vehicle
            </a>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                <i class="bi bi-plus-circle"></i> Add Maintenance
            </button>
        </div>
    </div>

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

    <!-- Maintenance List -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-tools"></i> Maintenance List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Service Type</th>
                            <th>Mileage</th>
                            <th>Cost</th>
                            <th>Commission</th>
                            <th>Next Due</th>
                            <th>Maintenance Image</th>
                            <th>Block Dates</th>
                            <th>Accompany Vehicle</th>
                            <th>Staff Handled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $vehicle->maintenances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($maintenance->service_date ? \Carbon\Carbon::parse($maintenance->service_date)->format('d M Y') : 'N/A'); ?></td>
                                <td><?php echo e($maintenance->service_type ?? 'N/A'); ?></td>
                                <td><?php echo e($maintenance->mileage ?? 'N/A'); ?></td>
                                <td>RM <?php echo e(number_format($maintenance->cost ?? 0, 2)); ?></td>
                                <td>RM <?php echo e(number_format($maintenance->commission_amount ?? 0, 2)); ?></td>
                                <td>
                                    <?php if($maintenance->next_due_date): ?>
                                        <?php
                                            $nextDue = \Carbon\Carbon::parse($maintenance->next_due_date);
                                            $isDue = $nextDue->isPast();
                                        ?>
                                        <span class="<?php echo e($isDue ? 'text-danger fw-bold' : ''); ?>">
                                            <?php echo e($nextDue->format('d M Y')); ?>

                                        </span>
                                        <?php if($isDue): ?>
                                            <span class="badge bg-danger">Due</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($maintenance->maintenance_img): ?>
                                        <img src="<?php echo e(asset('storage/' . $maintenance->maintenance_img)); ?>" 
                                             alt="Maintenance Image" 
                                             class="maintenance-img"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#viewMaintenanceImgModal<?php echo e($maintenance->maintenanceID); ?>">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($maintenance->block_start_date && $maintenance->block_end_date): ?>
                                        <?php echo e(\Carbon\Carbon::parse($maintenance->block_start_date)->format('d M Y')); ?> - 
                                        <?php echo e(\Carbon\Carbon::parse($maintenance->block_end_date)->format('d M Y')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($maintenance->accompanyVehicle): ?>
                                        <?php echo e($maintenance->accompanyVehicle->plate_number ?? 'N/A'); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($maintenance->staffID): ?>
                                        <?php
                                            $staffUser = \App\Models\User::find($maintenance->staffID);
                                        ?>
                                        <?php echo e($staffUser->name ?? 'N/A'); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?php echo e(route('admin.vehicles.maintenance.destroy', $maintenance->maintenanceID)); ?>" 
                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- View Maintenance Image Modal -->
                            <?php if($maintenance->maintenance_img): ?>
                            <div class="modal fade" id="viewMaintenanceImgModal<?php echo e($maintenance->maintenanceID); ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Maintenance Image</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center" style="min-height: 400px;">
                                            <img src="<?php echo e(asset('storage/' . $maintenance->maintenance_img)); ?>" 
                                                 alt="Maintenance Image" 
                                                 class="img-fluid" 
                                                 style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                 onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                        </div>
                                        <div class="modal-footer">
                                            <a href="<?php echo e(asset('storage/' . $maintenance->maintenance_img)); ?>" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="bi bi-download"></i> Open in New Tab
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No maintenance records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Maintenance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('admin.vehicles.maintenance.store', $vehicle->vehicleID)); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Service Date <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" value="<?php echo e(old('service_date', date('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <input type="text" name="service_type" class="form-control" value="<?php echo e(old('service_type')); ?>" required>
                            <small class="text-muted">e.g., Oil Change, Tire Replacement, Battery, General Service</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo e(old('description')); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mileage</label>
                            <input type="number" name="mileage" class="form-control" value="<?php echo e(old('mileage')); ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" step="0.01" class="form-control" value="<?php echo e(old('cost')); ?>" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Commission Amount (RM)</label>
                            <input type="number" name="commission_amount" step="0.01" class="form-control" value="<?php echo e(old('commission_amount', 0)); ?>" min="0">
                            <small class="text-muted">Commission for staff who served this maintenance</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control" value="<?php echo e(old('next_due_date')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Center</label>
                            <input type="text" name="service_center" class="form-control" value="<?php echo e(old('service_center')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Image</label>
                            <input type="file" name="maintenance_img" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block Start Date</label>
                            <input type="date" name="block_start_date" id="block_start_date" class="form-control" value="<?php echo e(old('block_start_date')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block End Date</label>
                            <input type="date" name="block_end_date" id="block_end_date" class="form-control" value="<?php echo e(old('block_end_date')); ?>">
                            <small class="text-muted">Vehicle will be unavailable between these dates</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accompany Vehicle</label>
                            <select name="accompany_vehicleID" id="accompany_vehicleID" class="form-select">
                                <option value="">Select Vehicle</option>
                            </select>
                            <small class="text-muted">Vehicle will be unavailable at start and end dates only</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Staff Handled</label>
                            <select name="staffID" class="form-select">
                                <option value="">Select Staff</option>
                                <?php $__currentLoopData = $staffUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($staff->userID); ?>" <?php echo e(old('staffID') == $staff->userID ? 'selected' : ''); ?>>
                                        <?php echo e($staff->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Update accompany vehicle dropdown based on block dates
    document.getElementById('block_start_date').addEventListener('change', updateAccompanyVehicles);
    document.getElementById('block_end_date').addEventListener('change', updateAccompanyVehicles);

    function updateAccompanyVehicles() {
        const startDate = document.getElementById('block_start_date').value;
        const endDate = document.getElementById('block_end_date').value;
        const dropdown = document.getElementById('accompany_vehicleID');
        
        if (!startDate || !endDate) {
            dropdown.innerHTML = '<option value="">Select Vehicle</option>';
            return;
        }

        // Fetch available vehicles for the date range
        fetch(`<?php echo e(route('admin.vehicles.available-vehicles')); ?>?start_date=${startDate}&end_date=${endDate}&exclude_vehicle=<?php echo e($vehicle->vehicleID); ?>`)
            .then(response => response.json())
            .then(data => {
                dropdown.innerHTML = '<option value="">Select Vehicle</option>';
                if (data.vehicles) {
                    data.vehicles.forEach(vehicle => {
                        const option = document.createElement('option');
                        option.value = vehicle.vehicleID;
                        option.textContent = vehicle.display || vehicle.plate_number;
                        dropdown.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching vehicles:', error);
            });
    }

    // Validate block end date is after start date
    document.getElementById('block_end_date').addEventListener('change', function() {
        const startDate = document.getElementById('block_start_date').value;
        const endDate = this.value;
        
        if (startDate && endDate && endDate < startDate) {
            alert('Block end date must be after or equal to block start date');
            this.value = '';
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/vehicles/maintenance.blade.php ENDPATH**/ ?>