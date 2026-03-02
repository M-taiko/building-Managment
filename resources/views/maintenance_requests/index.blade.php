@extends('layouts.building-admin')
@section('title', 'طلبات الصيانة')
@section('page-title', 'طلبات الصيانة')

@push('styles')
<style>
/* Filter pills scroll */
.filter-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.filter-scroll::-webkit-scrollbar { display: none; }

.fpill {
    flex-shrink: 0;
    padding: 5px 13px;
    border: 2px solid #dee2e6;
    background: #fff;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    color: #555;
    white-space: nowrap;
    transition: all .2s;
}
.fpill:hover { border-color: #667eea; background: #f5f3ff; }
.fpill.active {
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(102,126,234,.35);
}
/* Colored active states */
.fpill.s-pending.active    { background: linear-gradient(135deg,#ffc107,#ff9800); border-color:transparent; }
.fpill.s-progress.active   { background: linear-gradient(135deg,#17a2b8,#138496); border-color:transparent; }
.fpill.s-completed.active  { background: linear-gradient(135deg,#28a745,#20c997); border-color:transparent; }
.fpill.s-cancelled.active  { background: linear-gradient(135deg,#dc3545,#c82333); border-color:transparent; }
.fpill.p-low.active        { background: linear-gradient(135deg,#6c757d,#5a6268);  border-color:transparent; }
.fpill.p-medium.active     { background: linear-gradient(135deg,#007bff,#0056b3);  border-color:transparent; }
.fpill.p-high.active       { background: linear-gradient(135deg,#ffc107,#e0a800);  border-color:transparent; }
.fpill.p-urgent.active     { background: linear-gradient(135deg,#dc3545,#bd2130);  border-color:transparent; }

/* Table */
#maintenanceTable thead th { font-size: 12px; white-space: nowrap; }
#maintenanceTable td        { font-size: 13px; vertical-align: middle; }
@media (max-width: 575px)  { .col-hide-xs  { display: none !important; } }
@media (max-width: 767px)  { .col-hide-sm  { display: none !important; } }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-tools me-2"></i>طلبات الصيانة
        </h5>
        <button type="button" class="btn btn-primary btn-sm" id="addMaintenanceBtn">
            <i class="fas fa-plus-circle me-1"></i> طلب جديد
        </button>
    </div>

    {{-- Filters Card --}}
    <div class="card shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-body p-3">

            {{-- Status --}}
            <div class="mb-2">
                <div class="text-muted small fw-bold mb-1">
                    <i class="fas fa-info-circle me-1"></i>الحالة
                </div>
                <div class="filter-scroll">
                    <button class="fpill active"       data-status="">الكل</button>
                    <button class="fpill s-pending"    data-status="pending">
                        <i class="fas fa-clock me-1"></i>قيد الانتظار
                    </button>
                    <button class="fpill s-progress"   data-status="in_progress">
                        <i class="fas fa-spinner me-1"></i>قيد التنفيذ
                    </button>
                    <button class="fpill s-completed"  data-status="completed">
                        <i class="fas fa-check-circle me-1"></i>مكتمل
                    </button>
                    <button class="fpill s-cancelled"  data-status="cancelled">
                        <i class="fas fa-times-circle me-1"></i>ملغي
                    </button>
                </div>
            </div>

            {{-- Priority --}}
            <div>
                <div class="text-muted small fw-bold mb-1">
                    <i class="fas fa-flag me-1"></i>الأولوية
                </div>
                <div class="filter-scroll">
                    <button class="fpill active"   data-priority="">الكل</button>
                    <button class="fpill p-low"    data-priority="low">منخفض</button>
                    <button class="fpill p-medium" data-priority="medium">متوسط</button>
                    <button class="fpill p-high"   data-priority="high">عالي</button>
                    <button class="fpill p-urgent" data-priority="urgent">
                        <i class="fas fa-exclamation-circle me-1"></i>عاجل
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm" style="border-radius:12px;">
        <div class="card-body p-2 p-sm-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="maintenanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الشقة</th>
                            <th>العنوان</th>
                            <th class="col-hide-xs">الأولوية</th>
                            <th>الحالة</th>
                            <th class="col-hide-sm">المنشئ</th>
                            <th class="col-hide-sm">التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal --}}
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" id="modalContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let statusFilter   = '';
    let priorityFilter = '';

    const table = $('#maintenanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('maintenance.index') }}",
            data: d => { d.status = statusFilter; d.priority = priorityFilter; }
        },
        columns: [
            { data: 'id',               name: 'id',             width: '40px' },
            { data: 'apartment_number', name: 'apartment.number' },
            { data: 'title',            name: 'title' },
            { data: 'priority_label',   name: 'priority',   orderable: false, className: 'col-hide-xs' },
            { data: 'status_label',     name: 'status',     orderable: false },
            { data: 'creator_name',     name: 'creator.name',              className: 'col-hide-sm' },
            { data: 'created_at',       name: 'created_at',                className: 'col-hide-sm' },
            { data: 'action',           name: 'action',     orderable: false, searchable: false }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' },
        order: [[0, 'desc']],
        pageLength: 15,
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
    });

    // Status filter
    $(document).on('click', '.fpill[data-status]', function() {
        if ($(this).is('[data-priority]') && !$(this).is('[data-status]')) return;
        if (!$(this).is('[data-status]')) return;
        $(`.fpill[data-status]`).removeClass('active');
        $(this).addClass('active');
        statusFilter = $(this).data('status');
        table.ajax.reload();
    });

    // Priority filter
    $(document).on('click', '.fpill[data-priority]', function() {
        if (!$(this).is('[data-priority]')) return;
        if ($(this).is('[data-status]') && !$(this).is('[data-priority]')) return;
        // Only priority pills (those without data-status)
        $(`.fpill[data-priority]`).not('[data-status]').removeClass('active');
        // Also handle pills that only have data-priority
        $('[data-priority]').not('[data-status]').removeClass('active');
        $(this).addClass('active');
        priorityFilter = $(this).data('priority');
        table.ajax.reload();
    });

    // Add
    $('#addMaintenanceBtn').click(function() {
        $.get("{{ route('maintenance.create') }}", res => {
            $('#modalContent').html(res.html);
            $('#maintenanceModal').modal('show');
        });
    });

    // Edit
    $(document).on('click', '.edit-btn', function() {
        $.get(`/maintenance/${$(this).data('id')}/edit`, res => {
            $('#modalContent').html(res.html);
            $('#maintenanceModal').modal('show');
        });
    });

    // Delete
    $(document).on('click', '.delete-btn', function() {
        if (!confirm('هل أنت متأكد من حذف هذا الطلب؟')) return;
        $.ajax({
            url: `/maintenance/${$(this).data('id')}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: res => { toastr.success(res.message); table.ajax.reload(); },
            error:   ()  => toastr.error('حدث خطأ أثناء الحذف')
        });
    });

    // Submit
    $(document).on('submit', '#maintenanceForm', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.find('input[name="_method"]').val() || 'POST',
            data: form.serialize(),
            success: res => {
                toastr.success(res.message);
                $('#maintenanceModal').modal('hide');
                table.ajax.reload();
            },
            error: xhr => {
                const errs = xhr.responseJSON?.errors;
                if (errs) Object.values(errs).forEach(e => toastr.error(e[0]));
                else toastr.error('حدث خطأ أثناء الحفظ');
            }
        });
    });
});
</script>
@endpush
