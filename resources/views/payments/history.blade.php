@extends('layouts.building-admin')

@section('title', 'سجل المدفوعات')

@section('page-title', 'سجل المدفوعات')

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
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" id="filter_date_from" name="date_from">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" id="filter_date_to" name="date_to">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="applyFilter">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">إجمالي المدفوعات</h6>
                            <h3 class="mb-0 mt-2" id="total_payments">0.00 ج.م</h3>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">عدد المدفوعات</h6>
                            <h3 class="mb-0 mt-2" id="payments_count">0</h3>
                        </div>
                        <div>
                            <i class="fas fa-receipt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">متوسط الدفعة</h6>
                            <h3 class="mb-0 mt-2" id="average_payment">0.00 ج.م</h3>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments History Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history"></i> سجل المدفوعات</h5>
                <div>
                    <button class="btn btn-success btn-sm" id="exportExcel">
                        <i class="fas fa-file-excel"></i> تصدير Excel
                    </button>
                    <button class="btn btn-danger btn-sm" id="exportPdf">
                        <i class="fas fa-file-pdf"></i> تصدير PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="historyTable" class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>رقم الوحدة</th>
                            <th>المصروف</th>
                            <th>المبلغ</th>
                            <th>تاريخ الدفع</th>
                            <th>طريقة الدفع</th>
                            <th>رقم المرجع</th>
                            <th>المسجل بواسطة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">الإجمالي:</th>
                            <th id="footer_total">0.00 ج.م</th>
                            <th colspan="5"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> تأكيد الحذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف هذه الدفعة؟</p>
                <p class="text-danger"><strong>تحذير:</strong> هذا الإجراء سيؤدي إلى تحديث حالة الدفع في النظام.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let deletePaymentId = null;

    // Initialize DataTable
    const table = $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('payments.history') }}',
            data: function(d) {
                d.apartment_id = $('#filter_apartment').val();
                d.date_from = $('#filter_date_from').val();
                d.date_to = $('#filter_date_to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'apartment_number', name: 'apartment.number' },
            { data: 'expense_title', name: 'expenseShare.expense.title' },
            { data: 'amount', name: 'amount' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'reference_number', name: 'reference_number' },
            { data: 'recorded_by', name: 'recordedBy.name' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-sm btn-danger delete-payment-btn" data-id="${row.id}">
                        <i class="fas fa-trash"></i> حذف
                    </button>`;
                }
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        },
        order: [[4, 'desc']],
        footerCallback: function(row, data, start, end, display) {
            const api = this.api();

            // Calculate total from all data (server-side)
            // Note: This will only sum the current page. For server-side total,
            // you'd need to return it from the server
            let total = 0;
            api.column(3, {page: 'current'}).data().each(function(value) {
                // Extract numeric value from "X.XX ج.م" format
                const numValue = parseFloat(value.replace(' ج.م', '').replace(/,/g, ''));
                if(!isNaN(numValue)) {
                    total += numValue;
                }
            });

            $('#footer_total').html(total.toFixed(2) + ' ج.م');
        },
        drawCallback: function(settings) {
            updateStatistics();
        }
    });

    // Apply filter
    $('#applyFilter').click(function() {
        table.ajax.reload();
    });

    // Reset filter on Enter or change
    $('#filterForm input, #filterForm select').on('keypress change', function(e) {
        if(e.type === 'change' || e.which === 13) {
            e.preventDefault();
            table.ajax.reload();
        }
    });

    // Delete payment button
    $(document).on('click', '.delete-payment-btn', function() {
        deletePaymentId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    // Confirm delete
    $('#confirmDelete').click(function() {
        if(deletePaymentId) {
            $.ajax({
                url: `/payments/${deletePaymentId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#deleteModal').modal('hide');
                    table.ajax.reload();
                    deletePaymentId = null;
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء حذف الدفعة');
                }
            });
        }
    });

    // Export Excel
    $('#exportExcel').click(function() {
        table.button('.buttons-excel').trigger();
    });

    // Export PDF
    $('#exportPdf').click(function() {
        table.button('.buttons-pdf').trigger();
    });

    // Update statistics
    function updateStatistics() {
        const api = table;
        const data = api.ajax.json();

        // These would ideally come from the server response
        // For now, we'll calculate from current page
        let total = 0;
        let count = 0;

        api.column(3, {page: 'current'}).data().each(function(value) {
            const numValue = parseFloat(value.replace(' ج.م', '').replace(/,/g, ''));
            if(!isNaN(numValue)) {
                total += numValue;
                count++;
            }
        });

        const average = count > 0 ? total / count : 0;

        $('#total_payments').text(total.toFixed(2) + ' ج.م');
        $('#payments_count').text(count);
        $('#average_payment').text(average.toFixed(2) + ' ج.م');
    }
});
</script>
@endpush
