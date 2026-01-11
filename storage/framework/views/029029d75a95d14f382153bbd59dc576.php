

<?php $__env->startSection('title', 'Task Calendar'); ?>
<?php $__env->startSection('page-title', 'Task Calendar'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .calendar-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .calendar-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .unread-badge {
            background: #dc2626;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .calendar-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .calendar-grid {
            padding: 20px;
        }

        .calendar-month-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e0e0e0;
        }

        .calendar-day-header {
            background: var(--admin-red);
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day-cell {
            background: white;
            min-height: 120px;
            padding: 8px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .calendar-day-cell:hover {
            background: #f8f9fa;
            z-index: 10;
        }

        .date-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 4px;
            display: inline-block;
        }

        .date-label.pickup-label {
            background: #3b82f6;
            color: white;
        }

        .date-label.return-label {
            background: #8b5cf6;
            color: white;
        }

        .calendar-day-cell.other-month {
            background: #f5f5f5;
            color: #999;
        }

        .calendar-day-cell.today {
            border: 2px solid var(--admin-red);
        }

        .calendar-day-number {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .task-item {
            padding: 5px;
            margin: 3px 0;
            border-radius: 4px;
            font-size: 0.75rem;
            cursor: pointer;
            position: relative;
        }

        /* Pickup Task - Blue */
        .task-item.pickup {
            background: #3b82f6;
            color: white;
            border-left: 3px solid #1d4ed8;
        }

        /* Return Task - Purple */
        .task-item.return {
            background: #8b5cf6;
            color: white;
            border-left: 3px solid #6d28d9;
        }

        /* Done Task - Faded */
        .task-item.done {
            opacity: 0.6;
        }

        .task-floating-box {
            position: fixed;
            background: white;
            border: 2px solid var(--admin-red);
            border-radius: 8px;
            padding: 15px;
            min-width: 350px;
            max-width: 400px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            cursor: default;
        }

        .task-item:hover .task-floating-box:not(.sticky),
        .task-floating-box.sticky {
            display: block;
        }

        .task-floating-box.sticky {
            cursor: default;
        }

        .floating-box-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 2px solid var(--admin-red);
        }

        .floating-box-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            line-height: 1;
        }

        .floating-box-close:hover {
            color: var(--admin-red);
        }

        .task-detail-row {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .task-detail-row:last-child {
            border-bottom: none;
        }

        .task-detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
        }

        .task-detail-value {
            color: #333;
            font-size: 0.9rem;
            text-align: right;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <h4 class="mb-0">
                <i class="bi bi-calendar-event"></i> Task Calendar
                <?php
                    $totalTasks = 0;
                    foreach($tasksByDate as $tasks) {
                        $totalTasks += count($tasks);
                    }
                ?>
                <?php if($totalTasks > 0): ?>
                    <span class="unread-badge">
                        <i class="bi bi-list-task"></i> <?php echo e($totalTasks); ?> Tasks
                    </span>
                <?php endif; ?>
            </h4>
            <div class="calendar-controls">
                <div class="btn-group" role="group">
                    <a href="<?php echo e(route('runner.calendar', ['view' => 'month', 'date' => $currentDate])); ?>" 
                       class="btn btn-sm <?php echo e($currentView === 'month' ? 'btn-danger' : 'btn-outline-danger'); ?>">
                        Month
                    </a>
                    <a href="<?php echo e(route('runner.calendar', ['view' => 'week', 'date' => $currentDate])); ?>" 
                       class="btn btn-sm <?php echo e($currentView === 'week' ? 'btn-danger' : 'btn-outline-danger'); ?>">
                        Week
                    </a>
                    <a href="<?php echo e(route('runner.calendar', ['view' => 'day', 'date' => $currentDate])); ?>" 
                       class="btn btn-sm <?php echo e($currentView === 'day' ? 'btn-danger' : 'btn-outline-danger'); ?>">
                        Day
                    </a>
                </div>
                <div class="btn-group ms-2" role="group">
                    <a href="<?php echo e(route('runner.calendar', ['view' => $currentView, 'date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m-d')])); ?>" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('datePicker').showPicker()">
                        <?php echo e(\Carbon\Carbon::parse($currentDate)->format('M Y')); ?>

                    </button>
                    <input type="month" id="datePicker" value="<?php echo e(\Carbon\Carbon::parse($currentDate)->format('Y-m')); ?>" 
                           style="display: none;" 
                           onchange="window.location.href='<?php echo e(route('runner.calendar', ['view' => $currentView])); ?>&date=' + this.value + '-01'">
                    <a href="<?php echo e(route('runner.calendar', ['view' => $currentView, 'date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m-d')])); ?>" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="calendar-grid">
            <?php if($currentView === 'month'): ?>
                <div class="calendar-month-view">
                    <!-- Day Headers -->
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>

                    <?php
                        $startOfMonth = \Carbon\Carbon::parse($currentDate)->startOfMonth();
                        $endOfMonth = \Carbon\Carbon::parse($currentDate)->endOfMonth();
                        $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        $currentDay = $startOfCalendar->copy();
                    ?>

                    <?php while($currentDay->lte($endOfCalendar)): ?>
                        <?php
                            $dateKey = $currentDay->format('Y-m-d');
                            $isToday = $currentDay->isToday();
                            $isOtherMonth = !$currentDay->isSameMonth($startOfMonth);
                            $dayTasks = $tasksByDate[$dateKey] ?? [];
                            $cellClass = 'calendar-day-cell';
                            if ($isOtherMonth) $cellClass .= ' other-month';
                            if ($isToday) $cellClass .= ' today';
                        ?>
                        <div class="<?php echo e($cellClass); ?>" data-date="<?php echo e($dateKey); ?>" 
                             onmouseleave="handleCellMouseLeave('<?php echo e($dateKey); ?>')">
                            <div class="calendar-day-number"><?php echo e($currentDay->format('j')); ?></div>
                            <?php $__currentLoopData = $dayTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $booking = $task['booking'];
                                    $taskType = $task['type'];
                                    $taskId = $booking->bookingID . '_' . $taskType . '_' . $dateKey;
                                    $isDone = $task['is_done'];
                                ?>
                                <div class="task-item <?php echo e($taskType); ?> <?php echo e($isDone ? 'done' : ''); ?>"
                                     data-task-id="<?php echo e($taskId); ?>"
                                     onmouseenter="showTaskBox('<?php echo e($taskId); ?>', event)"
                                     onmouseleave="hideTaskBox('<?php echo e($taskId); ?>')"
                                     onclick="event.stopPropagation(); toggleTaskBox('<?php echo e($taskId); ?>')">
                                    <div>
                                        <span class="date-label <?php echo e($taskType); ?>-label">
                                            <?php echo e($taskType === 'pickup' ? 'Pickup' : 'Return'); ?>

                                        </span>
                                    </div>
                                    <div>
                                        <strong><?php echo e($booking->customer->user->name ?? 'N/A'); ?></strong>
                                    </div>
                                    <div class="small"><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></div>

                                    <!-- Floating Task Details Box -->
                                    <div class="task-floating-box" 
                                         id="task-box-<?php echo e($taskId); ?>" 
                                         data-task-id="<?php echo e($taskId); ?>"
                                         onmouseenter="keepTaskBoxOpen('<?php echo e($taskId); ?>')"
                                         onmouseleave="hideTaskBox('<?php echo e($taskId); ?>')">
                                        <div class="floating-box-header">
                                            <div>
                                                <strong style="color: var(--admin-red); font-size: 1.1rem;">
                                                    <?php echo e($taskType === 'pickup' ? 'Pickup Task' : 'Return Task'); ?>

                                                </strong>
                                                <div style="font-size: 0.85rem; color: #666; margin-top: 3px;">
                                                    <?php echo e($task['date']->format('d M Y, H:i')); ?>

                                                </div>
                                            </div>
                                            <button type="button" class="floating-box-close" onclick="event.stopPropagation(); closeTaskBox('<?php echo e($taskId); ?>')" title="Close">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Booking ID:</span>
                                            <span class="task-detail-value"><strong>#<?php echo e($booking->bookingID); ?></strong></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Task Type:</span>
                                            <span class="task-detail-value">
                                                <span class="badge <?php echo e($taskType === 'pickup' ? 'bg-primary' : ''); ?>" style="<?php echo e($taskType === 'return' ? 'background: #8b5cf6;' : ''); ?>">
                                                    <?php echo e(ucfirst($taskType)); ?>

                                                </span>
                                            </span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Customer Name:</span>
                                            <span class="task-detail-value"><?php echo e($booking->customer->user->name ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Phone:</span>
                                            <span class="task-detail-value"><?php echo e($booking->customer->user->phone ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Plate Number:</span>
                                            <span class="task-detail-value"><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Location:</span>
                                            <span class="task-detail-value"><?php echo e($task['location']); ?></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Date & Time:</span>
                                            <span class="task-detail-value"><?php echo e($task['date']->format('d M Y, H:i')); ?></span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Status:</span>
                                            <span class="task-detail-value">
                                                <?php if($isDone): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Upcoming</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="task-detail-row">
                                            <span class="task-detail-label">Commission:</span>
                                            <span class="task-detail-value"><strong style="color: var(--admin-red);">RM 2.00</strong></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $currentDay->addDay(); ?>
                    <?php endwhile; ?>
                </div>
            <?php elseif($currentView === 'week'): ?>
                <div class="alert alert-info">
                    Week view will be implemented here.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Day view will be implemented here.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let stickyBoxes = {};
        let hoveredBoxes = {};
        let hideTimeouts = {};

        function showTaskBox(taskId, event) {
            const box = document.getElementById('task-box-' + taskId);
            if (!box) return;
            
            // Clear any pending hide timeout
            if (hideTimeouts[taskId]) {
                clearTimeout(hideTimeouts[taskId]);
                delete hideTimeouts[taskId];
            }
            
            if (!stickyBoxes[taskId]) {
                box.style.display = 'block';
                hoveredBoxes[taskId] = true;
                // Position the box after it's displayed
                requestAnimationFrame(() => {
                    positionTaskBox(box, event);
                });
            }
        }

        function hideTaskBox(taskId) {
            hoveredBoxes[taskId] = false;
            
            // Set a small delay before hiding to allow moving to the box
            if (hideTimeouts[taskId]) {
                clearTimeout(hideTimeouts[taskId]);
            }
            
            hideTimeouts[taskId] = setTimeout(() => {
                const box = document.getElementById('task-box-' + taskId);
                
                // Check if mouse is still over box
                const isMouseOverBox = box && (box.matches(':hover') || box.querySelector(':hover'));
                
                if (box && !stickyBoxes[taskId] && !hoveredBoxes[taskId] && !isMouseOverBox) {
                    box.style.display = 'none';
                }
                delete hideTimeouts[taskId];
            }, 200);
        }

        function keepTaskBoxOpen(taskId) {
            hoveredBoxes[taskId] = true;
            if (hideTimeouts[taskId]) {
                clearTimeout(hideTimeouts[taskId]);
                delete hideTimeouts[taskId];
            }
        }

        function positionTaskBox(box, event) {
            if (!box) return;
            
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const margin = 15;
            
            // Get the task item element
            const taskItem = box.closest('.task-item');
            if (!taskItem) return;
            
            const itemRect = taskItem.getBoundingClientRect();
            
            // Reset position and styles first
            box.style.top = 'auto';
            box.style.left = 'auto';
            box.style.right = 'auto';
            box.style.bottom = 'auto';
            box.style.maxHeight = '';
            box.style.overflowY = '';
            
            // Get measurements after display
            const boxRect = box.getBoundingClientRect();
            const boxWidth = boxRect.width;
            const boxHeight = boxRect.height;
            
            // Calculate initial position (below the item)
            let top = itemRect.bottom + 5;
            let left = itemRect.left;
            
            // Check if box goes off the right edge of viewport
            if (left + boxWidth > viewportWidth - margin) {
                left = viewportWidth - boxWidth - margin;
            }
            
            // Check if box goes off the left edge of viewport
            if (left < margin) {
                left = margin;
            }
            
            // Check if box goes off the bottom edge of viewport
            if (top + boxHeight > viewportHeight - margin) {
                // Try to show above the item instead
                const topAbove = itemRect.top - boxHeight - 5;
                
                if (topAbove >= margin) {
                    // Can fit above
                    top = topAbove;
                } else {
                    // Can't fit above, constrain height and show below or center
                    const spaceBelow = viewportHeight - itemRect.bottom - margin;
                    const spaceAbove = itemRect.top - margin;
                    
                    if (spaceBelow >= spaceAbove && spaceBelow > 150) {
                        // Show below with constrained height
                        top = itemRect.bottom + 5;
                        box.style.maxHeight = (spaceBelow - 10) + 'px';
                        box.style.overflowY = 'auto';
                    } else if (spaceAbove > 150) {
                        // Show above with constrained height
                        top = margin;
                        box.style.maxHeight = (spaceAbove - 10) + 'px';
                        box.style.overflowY = 'auto';
                    } else {
                        // Center vertically with constrained height
                        top = margin;
                        box.style.maxHeight = (viewportHeight - margin * 2) + 'px';
                        box.style.overflowY = 'auto';
                    }
                }
            }
            
            // Apply position (using fixed positioning)
            box.style.top = top + 'px';
            box.style.left = left + 'px';
        }

        function toggleTaskBox(taskId) {
            const box = document.getElementById('task-box-' + taskId);
            if (box) {
                if (stickyBoxes[taskId]) {
                    box.classList.remove('sticky');
                    box.style.display = 'none';
                    delete stickyBoxes[taskId];
                } else {
                    // Close all other sticky boxes
                    Object.keys(stickyBoxes).forEach(id => {
                        const otherBox = document.getElementById('task-box-' + id);
                        if (otherBox) {
                            otherBox.classList.remove('sticky');
                            otherBox.style.display = 'none';
                        }
                        delete stickyBoxes[id];
                    });
                    box.classList.add('sticky');
                    box.style.display = 'block';
                    stickyBoxes[taskId] = true;
                    // Reposition when made sticky
                    const taskItem = box.closest('.task-item');
                    if (taskItem) {
                        const fakeEvent = { clientX: 0, clientY: 0 };
                        positionTaskBox(box, fakeEvent);
                    }
                }
            }
        }

        function closeTaskBox(taskId) {
            const box = document.getElementById('task-box-' + taskId);
            if (box) {
                box.classList.remove('sticky');
                box.style.display = 'none';
                delete stickyBoxes[taskId];
                delete hoveredBoxes[taskId];
            }
        }

        function handleCellMouseLeave(dateKey) {
            // Hide all non-sticky boxes in this cell
            const cell = document.querySelector(`[data-date="${dateKey}"]`);
            if (cell) {
                const taskItems = cell.querySelectorAll('.task-item');
                taskItems.forEach(item => {
                    const taskId = item.dataset.taskId;
                    if (taskId && !stickyBoxes[taskId]) {
                        hideTaskBox(taskId);
                    }
                });
            }
        }

        // Close floating boxes when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.task-item') && !event.target.closest('.task-floating-box')) {
                Object.keys(stickyBoxes).forEach(id => {
                    if (!hoveredBoxes[id]) {
                        const box = document.getElementById('task-box-' + id);
                        if (box) {
                            box.classList.remove('sticky');
                            box.style.display = 'none';
                        }
                        delete stickyBoxes[id];
                    }
                });
            }
        });

        // Close boxes when mouse leaves the calendar day cell or floating box
        document.addEventListener('mouseout', function(event) {
            const relatedTarget = event.relatedTarget;
            
            // Check if leaving a calendar day cell
            if (event.target.classList.contains('calendar-day-cell')) {
                if (!relatedTarget || !event.target.contains(relatedTarget)) {
                    const taskItems = event.target.querySelectorAll('.task-item');
                    taskItems.forEach(item => {
                        const taskId = item.dataset.taskId;
                        if (taskId && !stickyBoxes[taskId]) {
                            hideTaskBox(taskId);
                        }
                    });
                }
            }
            
            // Check if leaving a floating box (but not moving to its parent task item)
            if (event.target.classList.contains('task-floating-box')) {
                const box = event.target;
                const taskId = box.dataset.taskId;
                
                // If not moving to task item or staying in box, hide it (unless sticky)
                if (!stickyBoxes[taskId] && (!relatedTarget || !box.contains(relatedTarget))) {
                    hideTaskBox(taskId);
                }
            }
        }, true);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.runner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/runner/calendar/index.blade.php ENDPATH**/ ?>