@extends('layouts.building-admin')
@section('title', 'توزيع المصروف')
@section('page-title', 'توزيع المصروف')

@push('styles')
<style>
#sharesTable thead th { font-size: 12px; white-space: nowrap; }
#sharesTable td        { font-size: 13px; vertical-align: middle; }
@media (max-width: 575px) { .col-hide-xs { display: none !important; } }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة للمصروفات
        </a>
    </div>

    {{-- Expense Info Card --}}
    <div class="card shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-header py-3"
             style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px 12px 0 0;">
            <h6 class="mb-0 text-white fw-bold">
                <i class="fas fa-receipt me-2"></i>{{ $expense->title }}
            </h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-6 col-sm-3">
                    <div class="text-muted small">المبلغ الإجمالي</div>
                    <div class="fw-bold text-danger">{{ number_format($expense->amount, 2) }} ج.م</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="text-muted small">التاريخ</div>
                    <div class="fw-semibold">{{ $expense->date ? $expense->date->format('Y-m-d') : '-' }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="text-muted small">التوزيع</div>
                    <div>
                        @if($expense->distribution_type === 'all')
                            <span class="badge bg-primary rounded-pill">جميع السكان</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">شقة محددة</span>
                        @endif
                    </div>
                </div>
                @if($expense->description)
                <div class="col-12 col-sm-3">
                    <div class="text-muted small">الوصف</div>
                    <div class="small">{{ $expense->description }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="card shadow-sm text-center py-3" style="border-radius:12px;border-right:4px solid #28a745;">
                <div class="fw-bold fs-4 text-success" id="paidCount">
                    {{ $expense->shares->where('paid', true)->count() }}
                </div>
                <small class="text-muted">تم الدفع</small>
            </div>
        </div>
        <div class="col-6">
            <div class="card shadow-sm text-center py-3" style="border-radius:12px;border-right:4px solid #dc3545;">
                <div class="fw-bold fs-4 text-danger" id="unpaidCount">
                    {{ $expense->shares->where('paid', false)->count() }}
                </div>
                <small class="text-muted">لم يتم الدفع</small>
            </div>
        </div>
    </div>

    {{-- Shares Table --}}
    <div class="card shadow-sm" style="border-radius:12px;">
        <div class="card-body p-2 p-sm-3">
            <div class="table-responsive">
                <table id="sharesTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الشقة</th>
                            <th class="col-hide-xs">الساكن</th>
                            <th>الحصة</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#sharesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('expenses.shares', $expense->id) }}',
        columns: [
            { data: 'apartment_number', name: 'apartment_number' },
            { data: 'resident_name',    name: 'resident_name',   className: 'col-hide-xs' },
            { data: 'formatted_amount', name: 'share_amount' },
            { data: 'paid_label',       name: 'paid' },
            { data: 'action',           name: 'action', orderable: false, searchable: false }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
        pageLength: 15,
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
    });

    $(document).on('click', '.mark-paid-btn', function() {
        if (!confirm('هل تريد تسجيل هذه الحصة كمدفوعة؟')) return;
        $.ajax({
            url: `/expense-shares/${$(this).data('id')}/mark-paid`,
            method: 'POST',
            success: res => {
                toastr.success(res.message);
                table.ajax.reload();
                $('#paidCount').text(+$('#paidCount').text() + 1);
                $('#unpaidCount').text(+$('#unpaidCount').text() - 1);
            },
            error: () => toastr.error('حدث خطأ أثناء تسجيل الدفع')
        });
    });
});
</script>
@endpush
