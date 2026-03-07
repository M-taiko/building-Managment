@extends('layouts.building-admin')

@section('title', 'تسجيل المدفوعات')

@section('page-title', 'تسجيل المدفوعات')

@section('content')
<div class="container-fluid">
    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> فلترة البيانات</h5>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">الوحدة</label>
                        <select class="form-select" id="filter_apartment" name="apartment_id">
                            <option value="">جميع الوحدات</option>
                            @foreach($apartments as $apartment)
                                <option value="{{ $apartment->id }}">وحدة {{ $apartment->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الشهر</label>
                        <select class="form-select" id="filter_month" name="month">
                            <option value="">كل الشهور</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                                    {{ ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'][$i-1] }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">السنة</label>
                        <select class="form-select" id="filter_year" name="year">
                            <option value="">كل السنوات</option>
                            @for($i = $currentYear; $i >= $currentYear - 5; $i--)
                                <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">نوع المصروف</label>
                        <select class="form-select" id="filter_type" name="expense_type">
                            <option value="">جميع الأنواع</option>
                            <option value="monthly">شهري</option>
                            <option value="one_time">مرة واحدة</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="applyFilter">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> المدفوعات المطلوبة</h5>
                <div class="text-muted">
                    <small>عرض المصروفات غير المدفوعة والجزئية</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentsTable" class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>رقم الوحدة</th>
                            <th>المصروف</th>
                            <th>الشهر</th>
                            <th>المبلغ المطلوب</th>
                            <th>المبلغ المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill"></i> تسجيل دفعة جديدة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <input type="hidden" name="expense_share_id" id="expense_share_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>الوحدة:</strong> <span id="modal_apartment"></span><br>
                        <strong>المصروف:</strong> <span id="modal_expense"></span><br>
                        <strong>المبلغ المتبقي:</strong> <span id="modal_remaining" class="text-danger fw-bold"></span> ج.م
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ المدفوع <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" required>
                        <small class="text-muted">يجب أن لا يتجاوز المبلغ المتبقي</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">نقدي</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="check">شيك</option>
                            <option value="online">إلكتروني</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم المرجع (اختياري)</label>
                        <input type="text" class="form-control" name="reference_number" placeholder="رقم الإيصال أو رقم التحويل">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات (اختياري)</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> تسجيل الدفعة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#paymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('payments.index') }}',
            data: function(d) {
                d.apartment_id = $('#filter_apartment').val();
                d.month = $('#filter_month').val();
                d.year = $('#filter_year').val();
                d.expense_type = $('#filter_type').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'apartment_number', name: 'apartment.number' },
            { data: 'expense_title', name: 'expense.title' },
            { data: 'expense_month', name: 'expense.month' },
            { data: 'share_amount', name: 'share_amount' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'remaining_amount', name: 'remaining_amount' },
            { data: 'status_label', name: 'paid', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        },
        order: [[1, 'asc']]
    });

    // Apply filter
    $('#applyFilter').click(function() {
        table.ajax.reload();
    });

    // Reset filter on Enter
    $('#filterForm input, #filterForm select').on('keypress change', function(e) {
        if(e.type === 'change' || e.which === 13) {
            e.preventDefault();
            table.ajax.reload();
        }
    });

    // Record payment button
    $(document).on('click', '.record-payment-btn', function() {
        const shareId = $(this).data('id');
        const apartment = $(this).data('apartment');
        const expense = $(this).data('expense');
        const remaining = $(this).data('remaining');

        $('#expense_share_id').val(shareId);
        $('#modal_apartment').text(apartment);
        $('#modal_expense').text(expense);
        $('#modal_remaining').text(parseFloat(remaining).toFixed(2));
        $('#amount').attr('max', remaining);

        $('#paymentForm')[0].reset();
        $('#expense_share_id').val(shareId);
        $('#paymentModal').modal('show');
    });

    // Submit payment form
    $('#paymentForm').submit(function(e) {
        e.preventDefault();

        const amount = parseFloat($('#amount').val());
        const remaining = parseFloat($('#modal_remaining').text());

        if(amount > remaining) {
            toastr.error('المبلغ المدخل أكبر من المبلغ المتبقي');
            return;
        }

        if(amount <= 0) {
            toastr.error('يجب إدخال مبلغ أكبر من صفر');
            return;
        }

        $.ajax({
            url: '{{ route('payments.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success(response.message);
                $('#paymentModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        toastr.error(errors[key][0]);
                    });
                } else if(xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('حدث خطأ أثناء تسجيل الدفعة');
                }
            }
        });
    });
});
</script>
@endpush
