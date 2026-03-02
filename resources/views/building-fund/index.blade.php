@extends('layouts.admin')

@section('title', 'حساب العمارة')
@section('page-title', 'حساب العمارة')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-piggy-bank"></i> حساب العمارة</h2>
        </div>
    </div>

    <!-- Current Balance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-white mb-2">الرصيد الحالي لحساب العمارة</h4>
                            <h1 class="display-3 mb-0">{{ number_format($currentBalance, 2) }} ج.م</h1>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-wallet fa-5x opacity-50"></i>
                        </div>
                    </div>
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
                        <div class="col-md-4">
                            <label class="form-label">نوع المعاملة</label>
                            <select name="transaction_type" id="filterTransactionType" class="form-select">
                                <option value="">الكل</option>
                                <option value="income">إيرادات</option>
                                <option value="expense">مصروفات</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نوع المصروف</label>
                            <select name="expense_type" id="filterExpenseType" class="form-select">
                                <option value="">الكل</option>
                                <option value="subscription">اشتراكات</option>
                                <option value="maintenance">صيانة</option>
                                <option value="other">مصروفات أخرى</option>
                            </select>
                        </div>
                        <div class="col-md-4">
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

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">سجل المعاملات المالية</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transactionsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>التصنيف</th>
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>الرصيد بعدها</th>
                                    <th>بواسطة</th>
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

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable
    const table = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("building-fund.index") }}',
            data: function(d) {
                d.transaction_type = $('#filterTransactionType').val();
                d.expense_type = $('#filterExpenseType').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'type_badge', name: 'type_badge' },
            { data: 'category', name: 'category' },
            { data: 'description', name: 'description' },
            { data: 'amount_formatted', name: 'amount_formatted' },
            { data: 'balance_formatted', name: 'balance_formatted' },
            { data: 'created_by_name', name: 'created_by_name' }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        }
    });

    // Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });
});
</script>
@endpush
@endsection
