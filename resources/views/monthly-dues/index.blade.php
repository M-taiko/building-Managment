@extends('layouts.admin')

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
@endsection
