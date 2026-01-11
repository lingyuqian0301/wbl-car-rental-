@extends('layouts.runner')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="runner-card">
        <div class="card-header-green d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell"></i> All Notifications</span>
            @if($notifications->count() > 0)
                <button class="btn btn-sm btn-light" onclick="markAllAsRead()">
                    <i class="bi bi-check-all"></i> Mark All as Read
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="notification-list-item p-3 border-bottom {{ !$notification->is_read ? 'unread' : '' }}" 
                     id="notification-{{ $notification->id }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notification-icon" style="font-size: 1.5rem;">
                            @if($notification->type === 'new_pickup_task')
                                <i class="bi bi-truck text-primary"></i>
                            @elseif($notification->type === 'new_return_task')
                                <i class="bi bi-arrow-return-left" style="color: #8b5cf6;"></i>
                            @else
                                <i class="bi bi-bell text-secondary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge {{ $notification->type === 'new_pickup_task' ? 'bg-primary' : ($notification->type === 'new_return_task' ? 'bg-purple' : 'bg-secondary') }}" 
                                      style="{{ $notification->type === 'new_return_task' ? 'background: #8b5cf6 !important;' : '' }}">
                                    @if($notification->type === 'new_pickup_task')
                                        Pickup Task
                                    @elseif($notification->type === 'new_return_task')
                                        Return Task
                                    @else
                                        Notification
                                    @endif
                                </span>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $notification->message }}</p>
                            @if($notification->booking_id)
                                <a href="{{ route('runner.tasks') }}" class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="bi bi-eye"></i> View Task
                                </a>
                            @endif
                        </div>
                        @if(!$notification->is_read)
                            <button class="btn btn-sm btn-outline-success" onclick="markAsRead({{ $notification->id }})">
                                <i class="bi bi-check"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No notifications yet</p>
                </div>
            @endforelse
        </div>
        
        @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator && $notifications->hasPages())
            <div class="p-3 border-top">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <style>
        .notification-list-item {
            transition: background 0.2s;
        }
        .notification-list-item:hover {
            background: #f8f9fa;
        }
        .notification-list-item.unread {
            background: #fff8e1;
            border-left: 3px solid var(--admin-red);
        }
    </style>

    <script>
        function markAsRead(notificationId) {
            fetch(`{{ url('runner/notifications') }}/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById('notification-' + notificationId);
                    if (item) {
                        item.classList.remove('unread');
                        const btn = item.querySelector('.btn-outline-success');
                        if (btn) btn.remove();
                    }
                    // Refresh notification count
                    if (typeof loadRunnerNotifications === 'function') {
                        loadRunnerNotifications();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllAsRead() {
            fetch('{{ route("runner.notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
@endsection

