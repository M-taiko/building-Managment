@extends('layouts.admin')

@section('page-title', 'إدارة أنواع الاشتراكات')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">أنواع الاشتراكات الشهرية</h5>
                <button type="button" class="btn btn-primary" id="addBtn">
                    <i class="fas fa-plus"></i> إضافة نوع اشتراك جديد
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="subscriptionTypesTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الاشتراك</th>
                        <th>الوصف</th>
                        <th>المبلغ الشهري</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#subscriptionTypesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('subscription-types.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'formatted_amount', name: 'amount' },
            { data: 'is_active_label', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        }
    });

    // Add button
    $('#addBtn').click(function() {
        $.get('{{ route('subscription-types.create') }}', function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Edit button
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get(`/subscription-types/${id}/edit`, function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if(confirm('هل أنت متأكد من حذف نوع الاشتراك هذا؟')) {
            $.ajax({
                url: `/subscription-types/${id}`,
                method: 'DELETE',
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء الحذف');
                }
            });
        }
    });

    // Submit form
    $(document).on('submit', '#subscriptionTypeForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function(response) {
                toastr.success(response.message);
                $('#formModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error('حدث خطأ أثناء الحفظ');
                }
            }
        });
    });
});
</script>
@endpush
