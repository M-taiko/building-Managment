@extends('layouts.building-admin')
@section('title', 'إدارة المصروفات')
@section('page-title', 'المصروفات')

@push('styles')
<style>
#expensesTable thead th { font-size: 12px; white-space: nowrap; }
#expensesTable td        { font-size: 13px; vertical-align: middle; }
@media (max-width: 575px) { .col-hide-xs { display: none !important; } }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-receipt me-2"></i>المصروفات
        </h5>
        <button type="button" class="btn btn-primary btn-sm" id="addBtn">
            <i class="fas fa-plus-circle me-1"></i> مصروف جديد
        </button>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm" style="border-radius:12px;">
        <div class="card-body p-2 p-sm-3">
            <div class="table-responsive">
                <table id="expensesTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>المبلغ</th>
                            <th class="col-hide-xs">التوزيع</th>
                            <th class="col-hide-xs">التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal: إضافة / تعديل --}}
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" id="modalContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#expensesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('expenses.index') }}',
        columns: [
            { data: 'id',                name: 'id',                width: '40px' },
            { data: 'title',             name: 'title' },
            { data: 'formatted_amount',  name: 'amount' },
            { data: 'distribution_label',name: 'distribution_type', className: 'col-hide-xs', orderable: false },
            { data: 'formatted_date',    name: 'date',              className: 'col-hide-xs' },
            { data: 'action',            name: 'action',            orderable: false, searchable: false }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
        order: [[0, 'desc']],
        pageLength: 15,
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
    });

    $('#addBtn').click(function() {
        $.get('{{ route('expenses.create') }}', res => {
            $('#modalContent').html(res.html);
            $('#formModal').modal('show');
        });
    });

    $(document).on('click', '.edit-btn', function() {
        $.get(`/expenses/${$(this).data('id')}/edit`, res => {
            $('#modalContent').html(res.html);
            $('#formModal').modal('show');
        });
    });

    $(document).on('click', '.delete-btn', function() {
        if (!confirm('هل أنت متأكد من حذف هذا المصروف؟')) return;
        $.ajax({
            url: `/expenses/${$(this).data('id')}`, method: 'DELETE',
            success: res => { toastr.success(res.message); table.ajax.reload(); },
            error:   ()  => toastr.error('حدث خطأ أثناء الحذف')
        });
    });

    $(document).on('click', '.view-shares-btn', function() {
        window.location.href = `/expenses/${$(this).data('id')}/shares`;
    });

    $(document).on('submit', '#expenseForm', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: form.find('input[name="_method"]').val() || 'POST',
            data: form.serialize(),
            success: res => {
                toastr.success(res.message);
                $('#formModal').modal('hide');
                table.ajax.reload();
            },
            error: xhr => {
                if (xhr.status === 422)
                    Object.values(xhr.responseJSON.errors).forEach(e => toastr.error(e[0]));
                else
                    toastr.error('حدث خطأ أثناء الحفظ');
            }
        });
    });
});
</script>
@endpush
