<?php $__env->startSection('title', 'Staff Details'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .grouping-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .grouping-box-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--hasta-red-dark);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--hasta-rose);
    }
    .document-cell {
        min-height: 250px;
        transition: transform 0.2s;
        border: 1px solid #e5e7eb;
    }
    .document-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1"><?php echo e($staff->user->name ?? 'N/A'); ?></h1>
            <div class="text-muted small">
                Staff ID: #<?php echo e($staff->staffID); ?> · Status:
                <span class="badge <?php echo e(($staff->user->isActive ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                    <?php echo e(($staff->user->isActive ?? false) ? 'Active' : 'Inactive'); ?>

                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.settings.index', ['tab' => 'staff'])); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
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

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($activeTab ?? 'staff-detail') === 'staff-detail' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#staff-detail" type="button" role="tab">
                <i class="bi bi-person-circle"></i> Staff Detail
            </button>
        </li>
        <?php if($staff->runner): ?>
            <!-- Runner Task List Tab -->
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e(($activeTab ?? '') === 'task-list' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#task-list" type="button" role="tab">
                    <i class="bi bi-truck"></i> Task List
                </button>
            </li>
        <?php else: ?>
            <!-- StaffIT Tasks Handled Tab -->
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e(($activeTab ?? '') === 'tasks-handled' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#tasks-handled" type="button" role="tab">
                    <i class="bi bi-list-task"></i> Tasks Handled
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e(($activeTab ?? '') === 'commission' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#commission" type="button" role="tab">
                    <i class="bi bi-cash-coin"></i> Commission
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <!-- Runner Task List Tab -->
        <?php if($staff->runner): ?>
        <div class="tab-pane fade <?php echo e(($activeTab ?? '') === 'task-list' ? 'show active' : ''); ?>" id="task-list" role="tabpanel">
            <!-- Header Box -->
            <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Runner Task List','description' => 'Tasks assigned to '.e($staff->user->name ?? 'Runner').'','stats' => [
                    ['label' => 'Total Tasks', 'value' => $runnerTaskCount ?? 0, 'icon' => 'bi-list-check'],
                    ['label' => 'Total Commission', 'value' => 'RM ' . number_format($runnerTotalCommission ?? 0, 2), 'icon' => 'bi-cash-coin']
                ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Runner Task List','description' => 'Tasks assigned to '.e($staff->user->name ?? 'Runner').'','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    ['label' => 'Total Tasks', 'value' => $runnerTaskCount ?? 0, 'icon' => 'bi-list-check'],
                    ['label' => 'Total Commission', 'value' => 'RM ' . number_format($runnerTotalCommission ?? 0, 2), 'icon' => 'bi-cash-coin']
                ])]); ?>
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

            <div class="card mt-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Task List</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="<?php echo e(route('admin.settings.staff.show', ['staff' => $staff->staffID])); ?>" class="row g-3 mb-3">
                        <input type="hidden" name="tab" value="task-list">
                        <div class="col-md-3">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo e($i); ?>" <?php echo e(($filterMonth ?? date('m')) == $i ? 'selected' : ''); ?>><?php echo e(date('F', mktime(0, 0, 0, $i, 1))); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="<?php echo e($filterYear ?? date('Y')); ?>" class="form-control form-control-sm" min="2020" max="<?php echo e(date('Y') + 1); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">Filter</button>
                        </div>
                    </form>

                    <!-- Tasks Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Booking ID</th>
                                    <th>Task Type</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $runnerTasks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($task['num']); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('admin.bookings.reservations.show', ['booking' => $task['booking_id']])); ?>" class="text-decoration-none fw-bold text-primary">
                                                #<?php echo e($task['booking_id']); ?>

                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo e($task['task_type'] === 'Pickup' ? 'bg-success' : 'bg-info'); ?>">
                                                <?php echo e($task['task_type']); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php echo e(\Carbon\Carbon::parse($task['task_date'])->format('d M Y')); ?><br>
                                            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($task['task_date'])->format('H:i')); ?></small>
                                        </td>
                                        <td><?php echo e($task['location'] ?? 'N/A'); ?></td>
                                        <td class="fw-semibold">RM <?php echo e(number_format($task['commission_amount'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No tasks found for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if(($runnerTasks ?? collect())->count() > 0): ?>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="fw-semibold text-end">Total Tasks: <?php echo e($runnerTaskCount ?? 0); ?></td>
                                        <td class="fw-bold text-danger">RM <?php echo e(number_format($runnerTotalCommission ?? 0, 2)); ?></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Staff Detail Tab -->
        <div class="tab-pane fade <?php echo e(($activeTab ?? 'staff-detail') === 'staff-detail' ? 'show active' : ''); ?>" id="staff-detail" role="tabpanel">
            <!-- Staff Info Card -->
            <div class="card mb-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Staff Info</h5>
                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editStaffModal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Staff ID:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->staffID ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Username:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->username ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Email:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->email ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Phone:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->phone ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Name:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->name ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Last Login:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->lastLogin ? \Carbon\Carbon::parse($staff->user->lastLogin)->format('d M Y H:i') : 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Date Registered:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->dateRegistered ? \Carbon\Carbon::parse($staff->user->dateRegistered)->format('d M Y') : 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Date of Birth:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->user->DOB ? \Carbon\Carbon::parse($staff->user->DOB)->format('d M Y') : 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Age:</dt>
                            <dd class="d-inline ms-2">
                                <?php if($staff->user->DOB): ?>
                                    <?php echo e(\Carbon\Carbon::parse($staff->user->DOB)->age); ?> years
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Status:</dt>
                            <dd class="d-inline ms-2">
                                <span class="badge <?php echo e(($staff->user->isActive ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                                    <?php echo e(($staff->user->isActive ?? false) ? 'Active' : 'Inactive'); ?>

                                </span>
                            </dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">IC No:</dt>
                            <dd class="d-inline ms-2"><?php echo e($staff->ic_no ?? 'N/A'); ?></dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Staff Type:</dt>
                            <dd class="d-inline ms-2">
                                <span class="badge bg-info">
                                    <?php echo e($staff->staffIt ? 'Staff IT' : ($staff->runner ? 'Runner' : 'N/A')); ?>

                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Documentation Card -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- IC Image -->
                        <div class="col-md-6">
                            <div class="card document-cell h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                    <h6 class="fw-semibold">IC Document</h6>
                                    <?php
                                        $icImg = $staff->ic_img ?? null;
                                    ?>
                                    <?php if($icImg): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo e(getFileUrl($icImg)); ?>" 
                                                 alt="IC" 
                                                 class="img-fluid mb-2" 
                                                 style="max-height: 150px; border-radius: 6px;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p class="text-muted small" style="display:none;">Image not found</p>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewIcModal">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadIcModal">
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <p class="small text-muted mb-2">No IC image uploaded</p>
                                        <button type="button" 
                                                class="btn btn-sm" 
                                                style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#uploadIcModal">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View IC Modal -->
        <?php if($staff->ic_img ?? false): ?>
        <div class="modal fade" id="viewIcModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">IC Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center" style="min-height: 400px;">
                        <img src="<?php echo e(getFileUrl($staff->ic_img)); ?>" 
                             alt="IC Document" 
                             class="img-fluid" 
                             style="max-height: 70vh; width: auto; border-radius: 6px;"
                             onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo e(getFileUrl($staff->ic_img)); ?>" 
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

        <!-- Upload IC Modal -->
        <div class="modal fade" id="uploadIcModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload IC</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.settings.staff.upload-ic', $staff->staffID)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">IC Image <span class="text-danger">*</span></label>
                                <input type="file" name="ic_img" class="form-control" accept="image/*,.pdf" required>
                                <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Upload IC</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Staff Modal -->
        <div class="modal fade" id="editStaffModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Staff</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.settings.staff.update', $staff->staffID)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" value="<?php echo e($staff->user->username ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($staff->user->name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo e($staff->user->email ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo e($staff->user->phone ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="DOB" class="form-control" value="<?php echo e($staff->user->DOB ? \Carbon\Carbon::parse($staff->user->DOB)->format('Y-m-d') : ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IC No <span class="text-danger">*</span></label>
                                    <input type="text" name="ic_no" class="form-control" value="<?php echo e($staff->ic_no ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                                    <select name="staff_type" class="form-select" required>
                                        <option value="staffit" <?php echo e($staff->staffIt ? 'selected' : ''); ?>>Staff IT</option>
                                        <option value="runner" <?php echo e($staff->runner ? 'selected' : ''); ?>>Runner</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" name="isActive" class="form-check-input" id="editIsActive" <?php echo e(($staff->user->isActive ?? false) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="editIsActive">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Update Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tasks Handled Tab (StaffIT Only) -->
        <?php if($staff->staffIt): ?>
        <div class="tab-pane fade <?php echo e(($activeTab ?? '') === 'tasks-handled' ? 'show active' : ''); ?>" id="tasks-handled" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-task"></i> Tasks Handled</h5>
                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('admin.settings.staff.export-excel', $staff->staffID)); ?>?month=<?php echo e($filterMonth ?? date('m')); ?>&year=<?php echo e($filterYear ?? date('Y')); ?>&type=<?php echo e($filterTaskType ?? ''); ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <a href="<?php echo e(route('admin.settings.staff.export-pdf', $staff->staffID)); ?>?month=<?php echo e($filterMonth ?? date('m')); ?>&year=<?php echo e($filterYear ?? date('Y')); ?>&type=<?php echo e($filterTaskType ?? ''); ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="<?php echo e(route('admin.settings.staff.show', ['staff' => $staff->staffID])); ?>" class="row g-3 mb-3">
                        <input type="hidden" name="tab" value="tasks-handled">
                        <div class="col-md-3">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo e($i); ?>" <?php echo e(($filterMonth ?? date('m')) == $i ? 'selected' : ''); ?>><?php echo e(date('F', mktime(0, 0, 0, $i, 1))); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="<?php echo e($filterYear ?? date('Y')); ?>" class="form-control form-control-sm" min="2020" max="<?php echo e(date('Y') + 1); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Task Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="maintenance" <?php echo e(($filterTaskType ?? '') === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                                <option value="fuel" <?php echo e(($filterTaskType ?? '') === 'fuel' ? 'selected' : ''); ?>>Fuel</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">Filter</button>
                        </div>
                    </form>

                    <!-- Tasks Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Task Date</th>
                                    <th>Task Type</th>
                                    <th>Description</th>
                                    <th>Commission Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tasks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(\Carbon\Carbon::parse($task['task_date'])->format('d M Y')); ?></td>
                                        <td><span class="badge bg-info"><?php echo e($task['task_type']); ?></span></td>
                                        <td><?php echo e($task['description']); ?></td>
                                        <td class="fw-semibold">RM <?php echo e(number_format($task['commission_amount'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No tasks found for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if(($tasks ?? collect())->count() > 0): ?>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="fw-semibold text-end">Total Commission:</td>
                                        <td class="fw-bold text-danger">RM <?php echo e(number_format($totalCommission ?? 0, 2)); ?></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Commission Tab (StaffIT Only) -->
        <?php if($staff->staffIt): ?>
        <div class="tab-pane fade <?php echo e(($activeTab ?? '') === 'commission' ? 'show active' : ''); ?>" id="commission" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Commission Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Tasks</h6>
                                    <h3 class="fw-bold mb-0"><?php echo e($taskCount ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Commission</h6>
                                    <h3 class="fw-bold text-danger mb-0">RM <?php echo e(number_format($totalCommission ?? 0, 2)); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Task Count</th>
                                    <th>Total Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $monthlyStats = ($tasks ?? collect())->groupBy(function($task) {
                                        return \Carbon\Carbon::parse($task['task_date'])->format('Y-m');
                                    })->map(function($monthTasks, $yearMonth) {
                                        return [
                                            'month' => \Carbon\Carbon::parse($yearMonth . '-01')->format('F'),
                                            'year' => \Carbon\Carbon::parse($yearMonth . '-01')->format('Y'),
                                            'count' => $monthTasks->count(),
                                            'total' => $monthTasks->sum('commission_amount'),
                                        ];
                                    });
                                ?>
                                <?php $__empty_1 = true; $__currentLoopData = $monthlyStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($stat['month']); ?></td>
                                        <td><?php echo e($stat['year']); ?></td>
                                        <td><?php echo e($stat['count']); ?></td>
                                        <td class="fw-semibold">RM <?php echo e(number_format($stat['total'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No commission data available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>






<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/settings/staff/show.blade.php ENDPATH**/ ?>