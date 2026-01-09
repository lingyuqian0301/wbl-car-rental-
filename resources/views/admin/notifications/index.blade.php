@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            <i class="bi bi-bell"></i> Notifications
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-2">{{ $unreadCount }} Unread</span>
            @endif
        </h1>
        @if($unreadCount > 0)
            <button class="btn btn-sm btn-danger" onclick="markAllAsRead()">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
        @endif
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item {{ !$notification->is_read ? 'bg-light' : '' }}" 
                             data-notification-id="{{ $notification->id }}"
                             onclick="markAsRead({{ $notification->id }}, this)">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    @if(!$notification->is_read)
                                        <span class="badge bg-danger me-2">New</span>
                                    @endif
                                    
                                    @if($notification->type === 'new_booking')
                                        <i class="bi bi-calendar-plus text-primary"></i>
                                        <strong>New Booking</strong>
                                        @if($notification->booking)
                                            <div class="mt-1 small">
                                                <div><strong>{{ $notification->booking->customer && $notification->booking->customer->user ? $notification->booking->customer->user->name : 'Unknown' }}</strong> booked <strong>{{ $notification->booking->vehicle->full_model ?? 'N/A' }}</strong></div>
                                                <div class="text-muted">
                                                    Date: {{ $notification->booking->start_date->format('d M Y') }} - {{ $notification->booking->end_date->format('d M Y') }}
                                                    | Time: {{ $notification->created_at->format('h:i A') }}
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($notification->type === 'payment_received')
                                        <i class="bi bi-cash-coin text-success"></i>
                                        <strong>Payment Received</strong>
                                        @if($notification->payment)
                                            <div class="mt-1 small">
                                                <div><strong>{{ $notification->payment->booking->user->name }}</strong> - 
                                                    <strong>{{ $notification->payment->payment_type === 'Deposit' ? 'Deposit Payment' : ($notification->payment->payment_type === 'Full Payment' ? 'Full Payment' : ($notification->payment->payment_type === 'Balance' ? 'Rental Balance Payment' : $notification->payment->payment_type)) }}</strong>
                                                </div>
                                                <div class="text-muted">
                                                    Date: {{ $notification->payment->payment_date->format('d M Y') }} | 
                                                    Time: {{ $notification->created_at->format('h:i A') }} | 
                                                    Car: {{ $notification->payment->booking->vehicle->full_model }} | 
                                                    Amount: RM {{ number_format($notification->payment->amount, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($notification->type === 'new_customer')
                                        <i class="bi bi-person-plus text-info"></i>
                                        <strong>New Customer Registered</strong>
                                        @if($notification->user)
                                            <div class="mt-1 small">
                                                <div><strong>{{ $notification->user->name }}</strong> ({{ $notification->user->email }})</div>
                                            </div>
                                        @endif
                                    @else
                                        <i class="bi bi-info-circle"></i>
                                        <strong>{{ $notification->message }}</strong>
                                    @endif
                                    
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div>
                                    @if($notification->booking)
                                        <a href="{{ route('admin.vehicles.show', $notification->booking->vehicleID ?? $notification->booking->vehicle_id ?? '') }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           onclick="event.stopPropagation()">
                                            View Booking
                                        </a>
                                    @endif
                                    @if($notification->payment)
                                        <a href="{{ route('admin.payments.show', $notification->payment_id) }}" 
                                           class="btn btn-sm btn-outline-success"
                                           onclick="event.stopPropagation()">
                                            View Payment
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted">No notifications yet.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function markAsRead(notificationId, element) {
            fetch(`/admin/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                element.classList.remove('bg-light');
                element.querySelector('.badge')?.remove();
                location.reload();
            });
        }

        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                fetch('/admin/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => {
                    location.reload();
                });
            }
        }
    </script>
@endsection















