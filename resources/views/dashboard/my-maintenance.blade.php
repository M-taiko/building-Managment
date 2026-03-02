@extends('layouts.resident')
@section('title', 'طلبات الصيانة')
@section('page-title', 'طلبات الصيانة')

@push('styles')
<style>
.mreq-card {
    border-right: 4px solid #ccc;
    border-radius: 10px;
    transition: box-shadow .2s;
}
.mreq-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.1) !important; }
.mreq-card.urgent  { border-color: #dc3545; }
.mreq-card.high    { border-color: #fd7e14; }
.mreq-card.medium  { border-color: #0dcaf0; }
.mreq-card.low     { border-color: #28a745; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-tools me-2"></i>طلبات الصيانة
        </h5>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newRequestModal">
            <i class="fas fa-plus me-1"></i> طلب جديد
        </button>
    </div>

    {{-- Stats Row --}}
    <div class="row g-2 mb-3">
        <div class="col-3">
            <div class="card text-center shadow-sm py-2">
                <div class="fw-bold fs-5 text-secondary">{{ $requests->count() }}</div>
                <small class="text-muted">الكل</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card text-center shadow-sm py-2">
                <div class="fw-bold fs-5 text-warning">{{ $requests->where('status','pending')->count() }}</div>
                <small class="text-muted">معلق</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card text-center shadow-sm py-2">
                <div class="fw-bold fs-5 text-primary">{{ $requests->where('status','in_progress')->count() }}</div>
                <small class="text-muted">جاري</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card text-center shadow-sm py-2">
                <div class="fw-bold fs-5 text-success">{{ $requests->where('status','completed')->count() }}</div>
                <small class="text-muted">مكتمل</small>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <ul class="nav nav-pills nav-fill mb-3 bg-white rounded-3 p-1 shadow-sm" id="statusTabs">
        <li class="nav-item">
            <button class="nav-link active small" data-filter="all">الكل</button>
        </li>
        <li class="nav-item">
            <button class="nav-link small" data-filter="pending">معلق</button>
        </li>
        <li class="nav-item">
            <button class="nav-link small" data-filter="in_progress">جاري</button>
        </li>
        <li class="nav-item">
            <button class="nav-link small" data-filter="completed">مكتمل</button>
        </li>
    </ul>

    {{-- Requests List --}}
    @if($requests->count() > 0)
        <div id="requestsList">
            @foreach($requests as $req)
                @php
                    $pClass = match($req->priority ?? 'medium') {
                        'urgent' => 'urgent',
                        'high'   => 'high',
                        'low'    => 'low',
                        default  => 'medium'
                    };
                    $pLabel = match($req->priority ?? 'medium') {
                        'urgent' => ['label' => 'عاجل',   'badge' => 'bg-danger'],
                        'high'   => ['label' => 'عالية',  'badge' => 'bg-warning text-dark'],
                        'low'    => ['label' => 'منخفضة', 'badge' => 'bg-success'],
                        default  => ['label' => 'متوسطة', 'badge' => 'bg-info']
                    };
                    $sLabel = match($req->status) {
                        'completed'   => ['label' => 'مكتمل',       'badge' => 'bg-success'],
                        'in_progress' => ['label' => 'قيد التنفيذ', 'badge' => 'bg-primary'],
                        'cancelled'   => ['label' => 'ملغي',        'badge' => 'bg-secondary'],
                        default       => ['label' => 'معلق',        'badge' => 'bg-warning text-dark']
                    };
                @endphp
                <div class="card mreq-card shadow-sm mb-3 p-3 {{ $pClass }}" data-status="{{ $req->status }}">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 fw-bold">{{ $req->title }}</h6>
                        <span class="badge {{ $sLabel['badge'] }} rounded-pill ms-2 flex-shrink-0">{{ $sLabel['label'] }}</span>
                    </div>
                    @if($req->description)
                        <p class="text-muted small mb-2">{{ $req->description }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge {{ $pLabel['badge'] }} rounded-pill small">
                            <i class="fas fa-flag me-1"></i>{{ $pLabel['label'] }}
                        </span>
                        <small class="text-muted">
                            <i class="far fa-calendar-alt me-1"></i>{{ $req->created_at->format('Y-m-d') }}
                        </small>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد طلبات صيانة</h5>
                <p class="text-muted small mb-3">يمكنك إنشاء طلب صيانة جديد بالضغط على الزر أعلاه</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRequestModal">
                    <i class="fas fa-plus me-1"></i> طلب صيانة جديد
                </button>
            </div>
        </div>
    @endif

</div>

{{-- New Request Modal --}}
<div class="modal fade" id="newRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> طلب صيانة جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="newMaintenanceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">عنوان الطلب <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="req_title" required placeholder="مثال: تسريب مياه في الحمام">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الأولوية <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" id="req_priority" required>
                            <option value="low">منخفضة</option>
                            <option value="medium" selected>متوسطة</option>
                            <option value="high">عالية</option>
                            <option value="urgent">عاجل</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">وصف المشكلة</label>
                        <textarea class="form-control" name="description" id="req_description" rows="3" placeholder="وصف تفصيلي للمشكلة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane me-1"></i> إرسال الطلب
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter tabs
document.querySelectorAll('#statusTabs .nav-link').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#statusTabs .nav-link').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('#requestsList .mreq-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    });
});

// Submit new request
$('#newMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الإرسال...');

    $.ajax({
        url: '{{ route("resident.maintenance.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            toastr.success(res.message || 'تم إرسال الطلب بنجاح');
            $('#newRequestModal').modal('hide');
            setTimeout(() => location.reload(), 800);
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> إرسال الطلب');
            if (xhr.status === 422) {
                Object.values(xhr.responseJSON.errors).forEach(e => toastr.error(e[0]));
            } else {
                toastr.error('حدث خطأ، يرجى المحاولة مرة أخرى');
            }
        }
    });
});
</script>
@endpush
