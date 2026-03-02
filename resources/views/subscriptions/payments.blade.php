@extends('layouts.building-admin')
@section('title', 'تسجيل الدفعات')
@section('page-title', 'تسجيل الدفعات')

@push('styles')
<style>
/* ===== Filters ===== */
.month-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 6px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.month-scroll::-webkit-scrollbar { display: none; }

.filter-pill {
    flex-shrink: 0;
    padding: 6px 14px;
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
.filter-pill:hover {
    border-color: #667eea;
    background: #f5f3ff;
}
.filter-pill.active {
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(102,126,234,.35);
}
.filter-pill.paid-pill:not(.active)   { border-color:#28a745; color:#28a745; }
.filter-pill.pending-pill:not(.active){ border-color:#f59e0b; color:#f59e0b; }
.filter-pill.overdue-pill:not(.active){ border-color:#dc3545; color:#dc3545; }

/* ===== Table ===== */
#paymentsTable thead th { font-size: 12px; white-space: nowrap; }
#paymentsTable td        { font-size: 13px; vertical-align: middle; }

@media (max-width: 575px) {
    .col-hide-xs { display: none !important; }
}

/* ===== Modals ===== */
.sub-item {
    border-radius: 10px;
    border: 2px solid #dee2e6;
    padding: 12px;
    margin-bottom: 12px;
    transition: box-shadow .2s;
}
.sub-item.paid    { border-color:#28a745; background:#f1f9f3; }
.sub-item.pending { border-color:#ffc107; background:#fffbf0; }
.sub-item.overdue { border-color:#dc3545; background:#fff5f5; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-money-bill-wave me-2"></i>تسجيل الدفعات
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" id="addPaymentBtn">
                <i class="fas fa-plus-circle me-1"></i> دفعة جديدة
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" id="sendRemindersBtn">
                <i class="fas fa-bell me-1"></i> تذكير
            </button>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-body p-3">

            {{-- Year + Status Row --}}
            <div class="row g-2 mb-2">
                <div class="col-5 col-sm-4">
                    <label class="form-label small fw-bold mb-1 text-muted">السنة</label>
                    <select id="yearSelect" class="form-select form-select-sm">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-7 col-sm-8">
                    <label class="form-label small fw-bold mb-1 text-muted">الحالة</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="filter-pill active" data-status="">الكل</button>
                        <button class="filter-pill paid-pill"    data-status="paid">مدفوع</button>
                        <button class="filter-pill pending-pill" data-status="pending">معلق</button>
                        <button class="filter-pill overdue-pill" data-status="overdue">متأخر</button>
                    </div>
                </div>
            </div>

            {{-- Month Scroll --}}
            @php
                $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            @endphp
            <label class="form-label small fw-bold mb-1 text-muted">الشهر</label>
            <div class="month-scroll">
                @foreach($months as $i => $mn)
                    <button class="filter-pill month-pill {{ ($i+1) == $month ? 'active' : '' }}"
                            data-month="{{ $i+1 }}">{{ $mn }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm" style="border-radius:12px;">
        <div class="card-body p-2 p-sm-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="paymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>الشقة</th>
                            <th class="col-hide-xs">الساكن</th>
                            <th class="col-hide-xs">الفواتير</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal: تفاصيل فواتير شقة --}}
<div class="modal fade" id="subscriptionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    فواتير الشقة <span id="modalApartmentNumber"></span>
                    — <span id="modalMonth"></span>/<span id="modalYear"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="subscriptionsModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: إضافة دفعة --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="addPaymentModalContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedYear  = {{ $year }};
let selectedMonth = {{ $month }};
let selectedStatus = '';

$(document).ready(function() {
    // DataTable
    const table = $('#paymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('subscriptions.payments') }}",
            data: d => { d.year = selectedYear; d.month = selectedMonth; d.status = selectedStatus; }
        },
        columns: [
            { data: 'apartment_number', name: 'apartment_number' },
            { data: 'resident_name',    name: 'resident_name',       className: 'col-hide-xs' },
            { data: 'subscriptions_count', name: 'subscriptions_count', className: 'col-hide-xs' },
            { data: 'amounts',          name: 'amounts',          orderable: false, searchable: false },
            { data: 'status_label',     name: 'status',           orderable: false },
            { data: 'action',           name: 'action',           orderable: false, searchable: false }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' },
        order: [[0, 'asc']],
        pageLength: 15,
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
    });

    // Year select
    $('#yearSelect').change(function() {
        selectedYear = +$(this).val();
        table.ajax.reload();
    });
    // Scroll to active month
    const activeMonth = $('.month-pill.active')[0];
    if (activeMonth) activeMonth.scrollIntoView({ inline: 'center', behavior: 'smooth' });

    // Month pills
    $(document).on('click', '.month-pill', function() {
        $('.month-pill').removeClass('active');
        $(this).addClass('active');
        selectedMonth = +$(this).data('month');
        table.ajax.reload();
    });

    // Status pills
    $(document).on('click', '.filter-pill[data-status]', function() {
        if ($(this).hasClass('month-pill')) return;
        $('.filter-pill[data-status]').not('.month-pill').removeClass('active');
        $(this).addClass('active');
        selectedStatus = $(this).data('status');
        table.ajax.reload();
    });

    // View details button
    $(document).on('click', '.view-details-btn', function() {
        loadApartmentSubscriptions($(this).data('apartment-id'), $(this).data('year'), $(this).data('month'));
    });

    // Add payment
    $('#addPaymentBtn').click(function() {
        $.get("{{ route('subscriptions.create') }}", function(res) {
            $('#addPaymentModalContent').html(res.html);
            $('#addPaymentModal').modal('show');
        });
    });

    // Send reminders
    $('#sendRemindersBtn').click(function() {
        if (!confirm('إرسال تذكير لجميع المتأخرين عن الدفع؟')) return;
        $.post('{{ route('subscriptions.send-late-reminders') }}', {
            _token: '{{ csrf_token() }}', year: selectedYear, month: selectedMonth
        }, res => toastr.success(res.message))
        .fail(() => toastr.error('حدث خطأ أثناء الإرسال'));
    });
});

// Load apartment subscriptions
function loadApartmentSubscriptions(apartmentId, year, month) {
    $('#modalYear').text(year);
    $('#modalMonth').text(month);
    $('#subscriptionsModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    $('#subscriptionsModal').modal('show');

    $.get(`/apartments/${apartmentId}/subscriptions`, { year, month }, function(res) {
        const subs = res.subscriptions;
        $('#modalApartmentNumber').text(res.apartment.number);

        if (!subs.length) {
            $('#subscriptionsModalBody').html('<div class="alert alert-info">لا توجد فواتير لهذا الشهر</div>');
            return;
        }

        let html = '';
        subs.forEach(sub => {
            const sc = sub.status === 'paid' ? 'paid' : (sub.status === 'overdue' ? 'overdue' : 'pending');
            const remaining = sub.amount - sub.paid_amount;
            html += `
                <div class="sub-item ${sc}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong class="small">${sub.subscription_type.name}</strong>
                            <span class="text-muted ms-2" style="font-size:11px;">#${sub.id}</span>
                        </div>
                        ${getStatusBadge(sub.status)}
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-4 text-center">
                            <div style="font-size:11px;" class="text-muted">الكلي</div>
                            <div class="fw-bold small">${fmt(sub.amount)}</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size:11px;" class="text-muted">المدفوع</div>
                            <div class="fw-bold small text-success">${fmt(sub.paid_amount)}</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size:11px;" class="text-muted">المتبقي</div>
                            <div class="fw-bold small text-danger">${fmt(remaining)}</div>
                        </div>
                    </div>
                    ${sub.status !== 'paid' ? `
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm flex-grow-1 pay-btn"
                                    data-id="${sub.id}" data-apartment-id="${apartmentId}" data-amount="${remaining}">
                                <i class="fas fa-money-bill-wave me-1"></i> سداد ${fmt(remaining)}
                            </button>
                            <button class="btn btn-outline-info btn-sm notify-btn" data-apartment-id="${apartmentId}">
                                <i class="fas fa-bell"></i>
                            </button>
                        </div>
                    ` : '<div class="text-success small text-center"><i class="fas fa-check-circle me-1"></i>تم السداد بالكامل</div>'}
                </div>`;
        });
        $('#subscriptionsModalBody').html(html);
    });
}

// Pay
$(document).on('click', '.pay-btn', function() {
    const subId = $(this).data('id');
    const aptId = $(this).data('apartment-id');
    const amount = $(this).data('amount');
    if (!confirm(`تأكيد سداد ${fmt(amount)}؟`)) return;
    $.post(`/subscriptions/${subId}/mark-paid`, { _token: '{{ csrf_token() }}', amount })
        .done(() => {
            toastr.success('تم تسجيل الدفع بنجاح');
            loadApartmentSubscriptions(aptId, selectedYear, selectedMonth);
            $('#paymentsTable').DataTable().ajax.reload();
        })
        .fail(() => toastr.error('حدث خطأ أثناء تسجيل الدفع'));
});

// Notify
$(document).on('click', '.notify-btn', function() {
    const aptId = $(this).data('apartment-id');
    if (!confirm('إرسال تذكير بالدفع للساكن؟')) return;
    $.post('{{ route('subscriptions.send-late-reminders') }}', {
        _token: '{{ csrf_token() }}', year: selectedYear, month: selectedMonth, apartment_id: aptId
    }).done(() => toastr.success('تم إرسال التذكير'))
      .fail(() => toastr.error('حدث خطأ'));
});

function getStatusBadge(s) {
    return ({
        pending: '<span class="badge bg-warning text-dark rounded-pill">معلق</span>',
        paid:    '<span class="badge bg-success rounded-pill">مدفوع</span>',
        partial: '<span class="badge bg-info rounded-pill">جزئي</span>',
        overdue: '<span class="badge bg-danger rounded-pill">متأخر</span>',
    })[s] || '<span class="badge bg-secondary rounded-pill">-</span>';
}

function fmt(n) {
    return new Intl.NumberFormat('ar-EG',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n) + ' ج.م';
}
</script>
@endpush
