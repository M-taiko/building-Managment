@extends('layouts.resident')
@section('title', 'الإشعارات')
@section('page-title', 'الإشعارات')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bell"></i> الإشعارات</h5>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <button id="mark-all-read" class="btn btn-sm btn-primary">
                    <i class="fas fa-check-double"></i> تعليم الكل كمقروء
                </button>
            @endif
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <a href="{{ route('notifications.show', $notification->id) }}"
                           class="list-group-item list-group-item-action {{ $notification->is_read ? '' : 'list-group-item-primary' }}"
                           id="notification-{{ $notification->id }}"
                           style="cursor: pointer;">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        @if(!$notification->is_read)
                                            <span class="badge bg-primary me-2">جديد</span>
                                        @endif
                                        {{ $notification->title ?? 'إشعار' }}
                                    </h6>
                                    <p class="mb-1 text-truncate" style="max-width: 90%;">
                                        {{ Str::limit($notification->message, 100) }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $notification->sent_at ? $notification->sent_at->diffForHumans() : $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-chevron-left"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد إشعارات</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.mark-read').on('click', function() {
        const notificationId = $(this).data('id');
        markAsRead(notificationId);
    });

    $('#mark-all-read').on('click', function() {
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('تم تعليم جميع الإشعارات كمقروءة');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function() {
                toastr.error('حدث خطأ');
            }
        });
    });

    function markAsRead(notificationId) {
        $.ajax({
            url: `/notifications/${notificationId}/mark-read`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`#notification-${notificationId}`)
                        .removeClass('list-group-item-primary')
                        .find('.badge').remove();
                    $(`#notification-${notificationId}`)
                        .find('.mark-read').remove();
                    toastr.success('تم تعليم الإشعار كمقروء');
                    updateNotificationCount();
                }
            },
            error: function() {
                toastr.error('حدث خطأ');
            }
        });
    }

    function updateNotificationCount() {
        $.ajax({
            url: '{{ route("notifications.unread-count") }}',
            method: 'GET',
            success: function(response) {
                const count = response.count;
                const badge = $('#notification-count');

                if (count > 0) {
                    badge.text(count).show();
                } else {
                    badge.hide();
                }
            }
        });
    }
});
</script>
@endpush
