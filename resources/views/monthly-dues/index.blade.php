@extends('layouts.building-admin')

@section('title', 'المستحقات الشهرية')
@section('page-title', 'المستحقات الشهرية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-calendar-check"></i> المستحقات الشهرية</h2>
                <div>
                    <a href="{{ route('monthly-dues.settings') }}" class="btn btn-primary">
                        <i class="fas fa-cog"></i> إعدادات المبلغ الشهري
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                        <i class="fas fa-plus"></i> توليد مستحقات شهر جديد
                    </button>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                        <i class="fas fa-layer-group"></i> إنشاء متعدد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">السنة</label>
                            <select name="year" id="filterYear" class="form-select">
                                <option value="">الكل</option>
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الشهر</label>
                            <select name="month" id="filterMonth" class="form-select">
                                <option value="">الكل</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" id="filterStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="unpaid">غير مدفوع</option>
                                <option value="partial">مدفوع جزئياً</option>
                                <option value="paid">مدفوع</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> تطبيق الفلاتر
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="monthlyDuesTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الشقة</th>
                                    <th>الساكن</th>
                                    <th>الفترة</th>
                                    <th>المبلغ المطلوب</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">توليد مستحقات شهر جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">السنة</label>
                        <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الشهر</label>
                        <select name="month" class="form-select" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ الشهري (ج.م)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">توليد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تسديد المستحق الشهري</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payForm">
                @csrf
                <input type="hidden" id="dueId" name="due_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">المبلغ (ج.م)</label>
                        <input type="number" name="amount" id="payAmount" class="form-control" step="0.01" min="0" required>
                        <small class="text-muted">المتبقي: <span id="remainingAmount"></span> ج.م</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تسديد</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable
    const table = $('#monthlyDuesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("monthly-dues.index") }}',
            data: function(d) {
                d.year = $('#filterYear').val();
                d.month = $('#filterMonth').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'apartment_number', name: 'apartment_number' },
            { data: 'resident_name', name: 'resident_name' },
            { data: 'period', name: 'period' },
            { data: 'amount_formatted', name: 'amount_formatted' },
            { data: 'paid_amount_formatted', name: 'paid_amount_formatted' },
            { data: 'remaining_formatted', name: 'remaining_formatted' },
            { data: 'status_badge', name: 'status_badge' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        }
    });

    // Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Generate Form
    $('#generateForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("monthly-dues.generate") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response.message);
                $('#generateModal').modal('hide');
                $('#generateForm')[0].reset();
                table.ajax.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'حدث خطأ');
            }
        });
    });

    // Pay Button
    $(document).on('click', '.pay-btn', function() {
        const dueId = $(this).data('id');
        const row = table.row($(this).parents('tr')).data();

        $('#dueId').val(dueId);
        $('#remainingAmount').text(parseFloat(row.amount_formatted.replace(/[^\d.]/g, '')) - parseFloat(row.paid_amount_formatted.replace(/[^\d.]/g, '')));
        $('#payAmount').val('');
        $('#payModal').modal('show');
    });

    // Pay Form
    $('#payForm').on('submit', function(e) {
        e.preventDefault();
        const dueId = $('#dueId').val();

        $.ajax({
            url: `/monthly-dues/${dueId}/pay`,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response.message);
                $('#payModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'حدث خطأ');
            }
        });
    });
});
</script>
@endpush

<!-- Bulk Generate Modal -->
<div class="modal fade" id="bulkGenerateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i> إنشاء مطالب متعددة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkGenerateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نوع المطلب <span class="text-danger">*</span></label>
                        <input type="text" name="type" id="bulkType" class="form-control" placeholder="مثال: صيانة، نظافة، كهرباء" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="bulkAmount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر الشهور <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-secondary w-100" id="selectAllMonths">
                                    ✓ تحديد الكل
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 mt-2" id="monthsContainer">
                            @php $months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']; @endphp
                            @foreach($months as $index => $month)
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input month-check" type="checkbox" name="months" value="{{ $index + 1 }}" id="month{{ $index }}">
                                        <label class="form-check-label" for="month{{ $index }}">{{ $month }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر الشقق <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-secondary w-100" id="selectAllApartments">
                                    ✓ تحديد الكل
                                </button>
                            </div>
                        </div>
                        <div id="apartmentsContainer" class="mt-3" style="max-height: 300px; overflow-y: auto;">
                            <!-- Will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i> إنشاء
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const bulkGenerateModal = new bootstrap.Modal(document.getElementById('bulkGenerateModal'));

// Load apartments when modal opens
document.getElementById('bulkGenerateModal').addEventListener('show.bs.modal', function() {
    const container = document.getElementById('apartmentsContainer');
    container.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm"></span> جاري التحميل...</div>';
    fetchApartments();
});

function fetchApartments() {
    const container = document.getElementById('apartmentsContainer');

    $.ajax({
        url: '{{ route("apartments.index") }}',
        type: 'GET',
        data: { ajax: true },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                let html = '';
                response.data.forEach((apt, idx) => {
                    const aptId = apt.DT_RowId?.replace('row_', '') || idx;
                    const aptNum = apt[0] ? $(apt[0]).text().trim() : apt.apartment_number;

                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input apartment-check" type="checkbox" name="apartment_ids" value="${aptId}" id="apt${aptId}">
                            <label class="form-check-label" for="apt${aptId}">${aptNum}</label>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">لا توجد شقق</p>';
            }
        },
        error: function() {
            container.innerHTML = '<p class="text-danger">خطأ في تحميل الشقق</p>';
        }
    });
}

// Select all months
document.getElementById('selectAllMonths')?.addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.month-check');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
});

// Select all apartments
document.getElementById('selectAllApartments')?.addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.apartment-check');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
});

// Submit bulk generate
document.getElementById('bulkGenerateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const months = Array.from(document.querySelectorAll('.month-check:checked')).map(cb => cb.value);
    const apartments = Array.from(document.querySelectorAll('.apartment-check:checked')).map(cb => cb.value);

    if (months.length === 0) {
        toastr.error('اختر شهر واحد على الأقل');
        return;
    }
    if (apartments.length === 0) {
        toastr.error('اختر شقة واحدة على الأقل');
        return;
    }

    const data = {
        type: document.getElementById('bulkType').value,
        amount: document.getElementById('bulkAmount').value,
        months: months,
        apartment_ids: apartments,
    };

    $.ajax({
        url: '{{ route("monthly-dues.bulk-generate") }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                bulkGenerateModal.hide();
                document.getElementById('bulkGenerateForm').reset();
                // Reload table if exists
                if (typeof table !== 'undefined') table.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('حدث خطأ');
            }
        }
    });
});
</script>
@endpush
@endsection
