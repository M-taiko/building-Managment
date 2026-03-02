@extends('layouts.resident')
@section('title', 'تفاصيل الإشعار')
@section('page-title', 'تفاصيل الإشعار')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bell"></i> تفاصيل الإشعار</h5>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للإشعارات
            </a>
        </div>
        <div class="card-body">
            <div class="notification-details">
                <!-- Notification Header -->
                <div class="notification-header mb-4 pb-3 border-bottom">
                    <h4 class="mb-3">
                        @if(!$notification->is_read)
                            <span class="badge bg-primary me-2">جديد</span>
                        @endif
                        {{ $notification->title ?? 'إشعار' }}
                    </h4>
                    <div class="notification-meta text-muted">
                        <div class="mb-2">
                            <i class="fas fa-clock me-2"></i>
                            <strong>تاريخ الإرسال:</strong>
                            {{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : $notification->created_at->format('Y-m-d H:i') }}
                            <span class="ms-2">({{ $notification->sent_at ? $notification->sent_at->diffForHumans() : $notification->created_at->diffForHumans() }})</span>
                        </div>
                        @if($notification->is_read && $notification->read_at)
                            <div class="mb-2">
                                <i class="fas fa-check-double me-2"></i>
                                <strong>تاريخ القراءة:</strong>
                                {{ $notification->read_at->format('Y-m-d H:i') }}
                                <span class="ms-2">({{ $notification->read_at->diffForHumans() }})</span>
                            </div>
                        @endif
                        <div class="mb-2">
                            <i class="fas fa-tag me-2"></i>
                            <strong>النوع:</strong>
                            @php
                                $typeLabels = [
                                    'payment_received' => 'إيصال دفع',
                                    'payment_reminder' => 'تذكير دفع',
                                    'late_payment_reminder' => 'تذكير دفع متأخر',
                                    'building_expense' => 'مصروف العمارة',
                                    'maintenance_update' => 'تحديث صيانة',
                                    'general' => 'عام',
                                ];
                                $typeColors = [
                                    'payment_received' => 'success',
                                    'payment_reminder' => 'warning',
                                    'late_payment_reminder' => 'danger',
                                    'building_expense' => 'info',
                                    'maintenance_update' => 'primary',
                                    'general' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$notification->notification_type] ?? 'secondary' }}">
                                {{ $typeLabels[$notification->notification_type] ?? 'غير محدد' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notification Content -->
                <div class="notification-content mb-4">
                    <h5 class="mb-3">المحتوى:</h5>
                    <div class="alert alert-light border p-4">
                        <p class="mb-0" style="white-space: pre-line; font-size: 1.1rem; line-height: 1.8;">
                            {{ $notification->message }}
                        </p>
                    </div>
                </div>

                <!-- Related Information -->
                @if($notification->related_type && $notification->related_id)
                    <div class="related-info mb-4 p-3 bg-light rounded">
                        <h6 class="mb-2">
                            <i class="fas fa-link me-2"></i>معلومات ذات صلة:
                        </h6>
                        <p class="mb-0 text-muted">
                            <strong>النوع:</strong> {{ $notification->related_type }}
                            <br>
                            <strong>المعرف:</strong> #{{ $notification->related_id }}
                        </p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="notification-actions mt-4 pt-3 border-top">
                    <div class="d-flex gap-2 justify-content-between align-items-center flex-wrap">
                        <div>
                            @if(!$notification->is_read)
                                <button class="btn btn-primary" id="markAsRead" data-id="{{ $notification->id }}">
                                    <i class="fas fa-check"></i> تعليم كمقروء
                                </button>
                            @else
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> تم القراءة
                                </span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> جميع الإشعارات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#markAsRead').on('click', function() {
        const notificationId = $(this).data('id');

        $.ajax({
            url: `/notifications/${notificationId}/mark-read`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('تم تعليم الإشعار كمقروء');
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
});
</script>
@endpush

@push('styles')
<style>
.notification-details {
    max-width: 900px;
    margin: 0 auto;
}

.notification-header h4 {
    color: #2c3e50;
    font-weight: 600;
}

.notification-meta {
    font-size: 0.95rem;
}

.notification-content {
    font-size: 1rem;
}

.notification-content p {
    color: #34495e;
}

.related-info {
    border: 1px solid #e3e6ea;
}

@media (max-width: 768px) {
    .notification-details {
        padding: 10px;
    }

    .notification-header h4 {
        font-size: 1.3rem;
    }

    .notification-meta {
        font-size: 0.85rem;
    }

    .notification-content .alert {
        padding: 15px !important;
        font-size: 1rem !important;
    }

    .notification-actions .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }

    .notification-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
@endpush
