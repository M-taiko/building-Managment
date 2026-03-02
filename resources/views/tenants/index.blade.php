@extends('layouts.admin')

@section('page-title', 'إدارة العمارات')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">قائمة العمارات</h5>
                <button type="button" class="btn btn-primary" id="addBtn">
                    <i class="fas fa-plus"></i> إضافة عمارة جديدة
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="tenantsTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم العمارة</th>
                        <th>اسم العمارة</th>
                        <th>العنوان</th>
                        <th>رئيس الاتحاد</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>سعر الاشتراك</th>
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
    const table = $('#tenantsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('tenants.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tenant_code', name: 'tenant_code' },
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'admin_name', name: 'admin_name' },
            { data: 'admin_email', name: 'admin_email' },
            { data: 'admin_phone', name: 'admin_phone' },
            {
                data: 'subscription_price',
                name: 'subscription_price',
                render: function(data) {
                    return data + ' ج.م';
                }
            },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        }
    });

    // Add button
    $('#addBtn').click(function() {
        $.get('{{ route('tenants.create') }}', function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Edit button
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get(`/tenants/${id}/edit`, function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if(confirm('هل أنت متأكد من حذف هذه العمارة؟ سيتم حذف جميع البيانات المرتبطة بها.')) {
            $.ajax({
                url: `/tenants/${id}`,
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
    $(document).on('submit', '#tenantForm', function(e) {
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
