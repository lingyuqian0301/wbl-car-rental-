<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'description', 'stats', 'date' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title', 'description', 'stats', 'date' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h1 class="h3 mb-1 fw-bold"><?php echo e($title); ?></h1>
                </div>
            </div>
            <?php if($date): ?>
                <p class="mb-0 mt-3 fw-semibold"><?php echo e($description); ?> for <?php echo e($date->format('d M Y')); ?></p>
            <?php else: ?>
                <p class="mb-0 mt-3 fw-semibold"><?php echo e($description); ?></p>
            <?php endif; ?>
            <?php if(isset($stats) && count($stats) > 0): ?>
                <div class="mt-3 d-flex flex-wrap gap-3">
                    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="bi <?php echo e($stat['icon'] ?? 'bi-info-circle'); ?> me-1"></i>
                                <strong><?php echo e($stat['label']); ?>:</strong> <?php echo e($stat['value']); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php echo e($actions ?? ''); ?>

        </div>
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(120deg, var(--admin-red, #dc2626) 0%, var(--admin-red-dark, #991b1b) 100%);
        color: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 35px rgba(185, 28, 28, 0.25);
        padding: 24px 28px;
    }
</style>







<?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/components/admin-page-header.blade.php ENDPATH**/ ?>