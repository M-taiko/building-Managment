@extends('layouts.building-admin')

@section('title', 'إدارة الشقق')
@section('page-title', 'الشقق')

@push('styles')
<style>
    @media (max-width: 575px) {
        .col-hide-xs { display: none !important; }
    }
    @media (max-width: 767px) {
        .col-hide-sm { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0 text-muted">قائمة الشقق</h6>
    <button type="button" class="btn btn-primary btn-sm px-3" id="addBtn">
        <i class="fas fa-plus me-1"></i>شقة جديدة
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table id="apartmentsTable" class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="col-hide-xs">#</th>
                        <th>رقم الشقة</th>
                        <th class="col-hide-sm">الدور</th>
                        <th>اسم المالك</th>
                        <th class="col-hide-sm">النوع</th>
                        <th class="col-hide-sm">الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content" id="modalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#apartmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('apartments.index') }}',
        columns: [
            { data: 'id',         name: 'id',         className: 'col-hide-xs' },
            { data: 'number',     name: 'number' },
            { data: 'floor',      name: 'floor',      className: 'col-hide-sm' },
            { data: 'owner_name', name: 'owner_name' },
            { data: 'type',       name: 'type',       className: 'col-hide-sm', orderable: false },
            { data: 'is_active',  name: 'is_active',  className: 'col-hide-sm', orderable: false },
            { data: 'action',     name: 'action',     orderable: false, searchable: false }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
        pageLength: 10,
    });

    // Add
    $('#addBtn').click(function() {
        $.get('{{ route('apartments.create') }}', function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Edit
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get(`/apartments/${id}/edit`, function(response) {
            $('#modalContent').html(response.html);
            $('#formModal').modal('show');
        });
    });

    // Delete
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (confirm('هل أنت متأكد من حذف هذه الشقة؟')) {
            $.ajax({
                url: `/apartments/${id}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function() { toastr.error('حدث خطأ أثناء الحذف'); }
            });
        }
    });

    // Submit form
    $(document).on('submit', '#apartmentForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const url    = form.attr('action');
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
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(k => toastr.error(errors[k][0]));
                } else {
                    toastr.error('حدث خطأ أثناء الحفظ');
                }
            }
        });
    });
});
</script>
@endpush
