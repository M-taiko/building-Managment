@extends('layouts.admin')

@section('title', 'إدارة الاشتراكات')
@section('page-title', 'إدارة الاشتراكات')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">قائمة الاشتراكات</h5>
                <button type="button" class="btn btn-primary" id="addBtn">
                    <i class="fas fa-plus"></i> إضافة اشتراك
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="subscriptionsTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الشقة</th>
                        <th>نوع الاشتراك</th>
                        <th>السنة</th>
                        <th>الشهر</th>
                        <th>المبلغ</th>
                        <th>المدفوع</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" id="modalContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#subscriptionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('subscriptions.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'apartment_number', name: 'apartment_number' },
            { data: 'subscription_type_name', name: 'subscription_type_name' },
            { data: 'year', name: 'year' },
            { data: 'month', name: 'month' },
            { data: 'amount', name: 'amount' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'status_label', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf']
    });

    $('#addBtn').click(function() {
        $.get('{{ route('subscriptions.create') }}', function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    $(document).on('click', '.edit-btn', function() {
        $.get(`/subscriptions/${$(this).data('id')}/edit`, function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    $(document).on('click', '.mark-paid-btn', function() {
        const id = $(this).data('id');
        if(confirm('هل تريد تسجيل هذا الاشتراك كمدفوع؟')) {
            $.ajax({
                url: `/subscriptions/${id}/mark-paid`,
                method: 'POST',
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء تسجيل الدفع');
                }
            });
        }
    });

    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if(confirm('هل أنت متأكد من حذف هذا الاشتراك؟')) {
            $.ajax({
                url: `/subscriptions/${id}`,
                method: 'DELETE',
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                }
            });
        }
    });

    $(document).on('submit', '#subscriptionForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).find('input[name="_method"]').val() || 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success(response.message);
                $('#formModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                }
            }
        });
    });
});
</script>
@endpush
