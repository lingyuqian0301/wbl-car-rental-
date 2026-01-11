@extends('layouts.runner')

@section('title', 'Task Calendar')
@section('page-title', 'Task Calendar')

@section('content')
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

        .task-item.pickup {
            background: #3b82f6;
            color: white;
            border-left: 3px solid #1d4ed8;
        }

        .task-item.return {
            background: #8b5cf6;
            color: white;
            border-left: 3px solid #6d28d9;
        }

        .task-item.done {
            opacity: 0.6;
        }

        .task-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 3px;
        }

        .task-label.pickup-label {
            background: rgba(255,255,255,0.2);
        }

        .task-label.return-label {
            background: rgba(255,255,255,0.2);
        }

        .task-floating-box {
            position: fixed;
            background: white;
            border: 2px solid var(--admin-red);
            border-radius: 8px;
            padding: 15px;
            min-width: 320px;
            max-width: 380px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }

        .task-floating-box.show {
            display: block;
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

        .task-count-badge {
            background: var(--admin-red);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <h4 class="mb-0">
                <i class="bi bi-calendar-event"></i> Task Calendar
            </h4>
            <div class="calendar-controls">
                <div class="btn-group ms-2" role="group">
                    <a href="{{ route('runner.calendar', ['view' => $currentView, 'date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('datePicker').showPicker()">
                        {{ \Carbon\Carbon::parse($currentDate)->format('M Y') }}
                    </button>
                    <input type="month" id="datePicker" value="{{ \Carbon\Carbon::parse($currentDate)->format('Y-m') }}" 
                           style="display: none;" 
                           onchange="window.location.href='{{ route('runner.calendar') }}?date=' + this.value + '-01'">
                    <a href="{{ route('runner.calendar', ['view' => $currentView, 'date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <a href="{{ route('runner.calendar', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-danger">
                    Today
                </a>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-month-view">
                <!-- Day Headers -->
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                @php
                    $startOfMonth = \Carbon\Carbon::parse($currentDate)->startOfMonth();
                    $endOfMonth = \Carbon\Carbon::parse($currentDate)->endOfMonth();
                    $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                    $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                    $currentDay = $startOfCalendar->copy();
                @endphp

                @while($currentDay->lte($endOfCalendar))
                    @php
                        $dateKey = $currentDay->format('Y-m-d');
                        $isToday = $currentDay->isToday();
                        $isOtherMonth = !$currentDay->isSameMonth($startOfMonth);
                        $dayTasks = $tasksByDate[$dateKey] ?? [];
                        $cellClass = 'calendar-day-cell';
                        if ($isOtherMonth) $cellClass .= ' other-month';
                        if ($isToday) $cellClass .= ' today';
                    @endphp
                    <div class="{{ $cellClass }}" data-date="{{ $dateKey }}">
                        <div class="calendar-day-number">
                            {{ $currentDay->format('j') }}
                            @if(count($dayTasks) > 0)
                                <span class="task-count-badge">{{ count($dayTasks) }}</span>
                            @endif
                        </div>
                        @foreach($dayTasks as $index => $task)
                            @php
                                $booking = $task['booking'];
                                $taskType = $task['type'];
                                $taskId = $booking->bookingID . '_' . $taskType . '_' . $index;
                                $isDone = $task['is_done'];
                            @endphp
                            <div class="task-item {{ $taskType }} {{ $isDone ? 'done' : '' }}"
                                 data-task-id="{{ $taskId }}"
                                 onclick="toggleTaskBox('{{ $taskId }}', event)">
                                <div class="task-label {{ $taskType }}-label">
                                    {{ $taskType === 'pickup' ? 'Pickup' : 'Return' }}
                                </div>
                                <div><strong>#{{ $booking->bookingID }}</strong></div>
                                <div class="small">{{ $booking->vehicle->plate_number ?? 'N/A' }}</div>

                                <!-- Floating Task Details Box -->
                                <div class="task-floating-box" id="task-box-{{ $taskId }}" onclick="event.stopPropagation()">
                                    <div class="floating-box-header">
                                        <div>
                                            <span class="badge {{ $taskType === 'pickup' ? 'bg-primary' : 'bg-purple' }}" style="{{ $taskType === 'return' ? 'background: #8b5cf6;' : '' }}">
                                                {{ $taskType === 'pickup' ? 'PICKUP TASK' : 'RETURN TASK' }}
                                            </span>
                                            <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                                                {{ $task['date']->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                        <button type="button" class="floating-box-close" onclick="event.stopPropagation(); closeTaskBox('{{ $taskId }}')" title="Close">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Booking ID:</span>
                                        <span class="task-detail-value"><strong>#{{ $booking->bookingID }}</strong></span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Vehicle:</span>
                                        <span class="task-detail-value">{{ $booking->vehicle->plate_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Customer:</span>
                                        <span class="task-detail-value">{{ $booking->customer->user->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Phone:</span>
                                        <span class="task-detail-value">{{ $booking->customer->user->phone ?? 'N/A' }}</span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Location:</span>
                                        <span class="task-detail-value">{{ $task['location'] }}</span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Date & Time:</span>
                                        <span class="task-detail-value">{{ $task['date']->format('d M Y, H:i') }}</span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Status:</span>
                                        <span class="task-detail-value">
                                            @if($isDone)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Upcoming</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="task-detail-row">
                                        <span class="task-detail-label">Commission:</span>
                                        <span class="task-detail-value"><strong class="text-danger">RM 2.00</strong></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php $currentDay->addDay(); @endphp
                @endwhile
            </div>
        </div>

        <!-- Legend -->
        <div class="p-3 border-top">
            <div class="d-flex gap-4 justify-content-center">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 4px;"></div>
                    <span class="small">Pickup Task</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 20px; height: 20px; background: #8b5cf6; border-radius: 4px;"></div>
                    <span class="small">Return Task</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 20px; height: 20px; background: #dc2626; border-radius: 4px;"></div>
                    <span class="small">Today</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let openTaskBox = null;

        function toggleTaskBox(taskId, event) {
            event.stopPropagation();
            const box = document.getElementById('task-box-' + taskId);
            
            if (!box) return;

            // Close any other open box
            if (openTaskBox && openTaskBox !== taskId) {
                const otherBox = document.getElementById('task-box-' + openTaskBox);
                if (otherBox) {
                    otherBox.classList.remove('show');
                }
            }

            // Toggle current box
            if (box.classList.contains('show')) {
                box.classList.remove('show');
                openTaskBox = null;
            } else {
                box.classList.add('show');
                openTaskBox = taskId;
                positionTaskBox(box, event);
            }
        }

        function closeTaskBox(taskId) {
            const box = document.getElementById('task-box-' + taskId);
            if (box) {
                box.classList.remove('show');
                openTaskBox = null;
            }
        }

        function positionTaskBox(box, event) {
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const margin = 15;

            // Get the task item element
            const taskItem = box.closest('.task-item');
            if (!taskItem) return;

            const itemRect = taskItem.getBoundingClientRect();
            
            // Get box dimensions
            requestAnimationFrame(() => {
                const boxRect = box.getBoundingClientRect();
                const boxWidth = boxRect.width;
                const boxHeight = boxRect.height;

                // Calculate position
                let top = itemRect.bottom + 5;
                let left = itemRect.left;

                // Check if box goes off the right edge
                if (left + boxWidth > viewportWidth - margin) {
                    left = viewportWidth - boxWidth - margin;
                }

                // Check if box goes off the left edge
                if (left < margin) {
                    left = margin;
                }

                // Check if box goes off the bottom edge
                if (top + boxHeight > viewportHeight - margin) {
                    // Show above the item
                    top = itemRect.top - boxHeight - 5;
                    if (top < margin) {
                        top = margin;
                    }
                }

                box.style.top = top + 'px';
                box.style.left = left + 'px';
            });
        }

        // Close task box when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.task-item') && !event.target.closest('.task-floating-box')) {
                if (openTaskBox) {
                    const box = document.getElementById('task-box-' + openTaskBox);
                    if (box) {
                        box.classList.remove('show');
                    }
                    openTaskBox = null;
                }
            }
        });
    </script>
@endsection

