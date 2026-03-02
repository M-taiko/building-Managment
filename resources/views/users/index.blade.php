@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-dark fw-bold">إدارة المستخدمين</h1>
                    <p class="text-muted small mt-2">إدارة المقيمين وأصحاب الوحدات السكنية</p>
                </div>
                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>إضافة مقيم جديد
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h6 class="text-muted small mb-1">إجمالي المستخدمين</h6>
                    <h3 class="mb-0" id="totalUsers">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h6 class="text-muted small mb-1">مستخدمين نشطين</h6>
                    <h3 class="mb-0" id="activeUsers">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <h6 class="text-muted small mb-1">قيد الانتظار</h6>
                    <h3 class="mb-0" id="pendingUsers">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h6 class="text-muted small mb-1">مستخدمين معطلين</h6>
                    <h3 class="mb-0" id="inactiveUsers">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Section -->
    <div class="card modern-card">
        <div class="card-header bg-white border-0 pb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">قائمة المستخدمين</h5>
                </div>
                <div class="col-auto">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="ابحث عن مستخدم...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>العمارة</th>
                            <th>رقم الوحدة</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header bg-gradient border-0">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-user-plus me-2"></i>إضافة مقيم جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="userId" name="user_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tenant_id" class="form-label">العمارة <span class="text-danger">*</span></label>
                                <select class="form-control" id="tenant_id" name="tenant_id" required>
                                    <option value="">-- اختر عمارة --</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="unit_number" class="form-label">رقم الوحدة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                    <input type="text" class="form-control" id="unit_number" name="unit_number" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_type" class="form-label">نوع المستخدم <span class="text-danger">*</span></label>
                                <select class="form-control" id="user_type" name="user_type" required>
                                    <option value="resident">مقيم</option>
                                    <option value="owner">مالك</option>
                                    <option value="tenant">مستأجر</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="password" class="form-label" id="passwordLabel">كلمة المرور <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <small class="text-muted d-block mt-1" id="passwordHint">اتركها فارغة للاحتفاظ بكلمة المرور الحالية</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active">نشط</option>
                                    <option value="inactive">معطل</option>
                                    <option value="pending">قيد الانتظار</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
                                    <span class="form-check-label">منح صلاحيات إدارية</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save me-2"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header bg-gradient border-0">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-info-circle me-2"></i>تفاصيل المستخدم
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --bg-light: #f8f9fa;
    }

    .btn-gradient {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }

    .stat-icon.bg-primary {
        background: var(--primary-gradient);
    }

    .stat-icon.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .stat-icon.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .modern-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .bg-gradient {
        background: var(--primary-gradient) !important;
    }

    .search-wrapper {
        position: relative;
        max-width: 300px;
    }

    .search-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        pointer-events: none;
    }

    .search-wrapper input {
        padding-left: 35px;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .search-wrapper input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .table thead th {
        background-color: var(--bg-light);
        border: none;
        font-weight: 600;
        color: #333;
        padding: 15px;
        text-align: right;
    }

    .table tbody td {
        padding: 15px;
        border: none;
        color: #555;
        vertical-align: middle;
    }

    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 12px;
    }

    .badge-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .badge-warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .badge-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .btn-action {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .btn-edit {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .btn-edit:hover {
        background-color: #3b82f6;
        color: white;
    }

    .btn-delete {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .btn-delete:hover {
        background-color: #ef4444;
        color: white;
    }

    .btn-view {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .btn-view:hover {
        background-color: #667eea;
        color: white;
    }

    .modern-modal .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 12px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-left: none;
        color: #667eea;
    }

    .input-group .form-control {
        border-right: none;
    }

    .form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #e0e0e0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-label {
        cursor: pointer;
        margin-left: 8px;
        font-weight: 500;
        color: #333;
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 13px;
        margin-top: 5px;
    }

    .modal-backdrop {
        backdrop-filter: blur(5px);
    }

    @media (max-width: 768px) {
        .search-wrapper {
            max-width: 100%;
            margin-top: 10px;
        }

        .table-responsive {
            font-size: 14px;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    let table;
    const apiUrl = '{{ route("api.users") }}';

    $(document).ready(function() {
        initDataTable();
        loadTenants();
        handleFormSubmit();
        handleSearch();
        attachEventHandlers();
    });

    function initDataTable() {
        table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: apiUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'tenant_name', name: 'tenant_name' },
                { data: 'unit_number', name: 'unit_number' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 5,
                    render: function(data) {
                        const badges = {
                            'active': '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>نشط</span>',
                            'inactive': '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>معطل</span>',
                            'pending': '<span class="badge badge-warning"><i class="fas fa-hourglass-half me-1"></i>قيد الانتظار</span>'
                        };
                        return badges[data] || data;
                    }
                },
                {
                    targets: 6,
                    render: function(data) {
                        return new Date(data).toLocaleDateString('ar-SA');
                    }
                },
                {
                    targets: 7,
                    render: function(data, type, row) {
                        return `
                            <button class="btn-action btn-view" onclick="viewUser(${row.id})" title="عرض التفاصيل">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action btn-edit" onclick="editUser(${row.id})" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteUser(${row.id})" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function() {
                updateStatistics();
            }
        });
    }

    function loadTenants() {
        axios.get('{{ route("api.tenants.list") }}', {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(response => {
            const select = $('#tenant_id');
            response.data.forEach(tenant => {
                select.append(`<option value="${tenant.id}">${tenant.name}</option>`);
            });
        }).catch(error => {
            console.error('Error loading tenants:', error);
            toastr.error('حدث خطأ في تحميل بيانات العمارات');
        });
    }

    function handleFormSubmit() {
        $('#userForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const userId = $('#userId').val();
            const url = userId ? `{{ route('api.users') }}/${userId}` : '{{ route("api.users") }}';
            const method = userId ? 'post' : 'post';

            if (userId) {
                formData.append('_method', 'PUT');
            }

            axios({
                method: method,
                url: url,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                $('#addUserModal').modal('hide');
                resetForm();
                table.ajax.reload();
                toastr.success(userId ? 'تم تحديث المستخدم بنجاح' : 'تم إضافة المستخدم بنجاح');
            }).catch(error => {
                if (error.response?.data?.errors) {
                    handleValidationErrors(error.response.data.errors);
                }
                toastr.error(error.response?.data?.message || 'حدث خطأ أثناء الحفظ');
            });
        });
    }

    function viewUser(id) {
        axios.get(`{{ route('api.users') }}/${id}`, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(response => {
            const data = response.data;
            const userTypeBadge = {
                'resident': 'مقيم',
                'owner': 'مالك',
                'tenant': 'مستأجر'
            };
            const content = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">الاسم الكامل</label>
                        <p class="fw-bold">${data.name}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">البريد الإلكتروني</label>
                        <p class="fw-bold">${data.email}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">رقم الهاتف</label>
                        <p class="fw-bold">${data.phone}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">العمارة</label>
                        <p class="fw-bold">${data.tenant_name || '-'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">رقم الوحدة</label>
                        <p class="fw-bold">${data.unit_number}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">نوع المستخدم</label>
                        <p class="fw-bold">${userTypeBadge[data.user_type] || data.user_type}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">الحالة</label>
                        <p>${getStatusBadge(data.status)}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">تاريخ التسجيل</label>
                        <p class="fw-bold">${new Date(data.created_at).toLocaleDateString('ar-SA')}</p>
                    </div>
                </div>
            `;
            $('#viewUserContent').html(content);
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        }).catch(error => {
            toastr.error('حدث خطأ في تحميل البيانات');
        });
    }

    function editUser(id) {
        axios.get(`{{ route('api.users') }}/${id}`, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(response => {
            const data = response.data;
            $('#userId').val(id);
            $('#name').val(data.name);
            $('#email').val(data.email);
            $('#phone').val(data.phone);
            $('#tenant_id').val(data.tenant_id);
            $('#unit_number').val(data.unit_number);
            $('#user_type').val(data.user_type);
            $('#status').val(data.status);
            $('#is_admin').prop('checked', data.is_admin);

            $('#passwordLabel').text('كلمة المرور (اختياري)');
            $('#passwordHint').text('اتركها فارغة للاحتفاظ بكلمة المرور الحالية');
            $('#password').removeAttr('required');

            document.querySelector('#addUserModal .modal-title').innerHTML =
                '<i class="fas fa-edit me-2"></i>تعديل بيانات المستخدم';
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        }).catch(error => {
            toastr.error('حدث خطأ في تحميل البيانات');
        });
    }

    function deleteUser(id) {
        if (!confirm('هل أنت متأكد من حذف هذا المستخدم؟')) return;

        axios.delete(`{{ route('api.users') }}/${id}`, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(response => {
            table.ajax.reload();
            toastr.success('تم حذف المستخدم بنجاح');
        }).catch(error => {
            toastr.error(error.response?.data?.message || 'حدث خطأ أثناء الحذف');
        });
    }

    function handleSearch() {
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });
    }

    function attachEventHandlers() {
        $('#addUserModal').on('hidden.bs.modal', function() {
            resetForm();
            $('#passwordLabel').text('كلمة المرور <span class="text-danger">*</span>');
            $('#passwordHint').text('');
            $('#password').attr('required', 'required');
            document.querySelector('#addUserModal .modal-title').innerHTML =
                '<i class="fas fa-user-plus me-2"></i>إضافة مقيم جديد';
        });
    }

    function resetForm() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerHTML = '');
        document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    }

    function handleValidationErrors(errors) {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.innerHTML = '');
        document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

        Object.keys(errors).forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.innerHTML = errors[field][0];
                }
            }
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>نشط</span>',
            'inactive': '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>معطل</span>',
            'pending': '<span class="badge badge-warning"><i class="fas fa-hourglass-half me-1"></i>قيد الانتظار</span>'
        };
        return badges[status] || status;
    }

    function updateStatistics() {
        const data = table.data().toArray();
        const total = data.length;
        const active = data.filter(row => row.status === 'active').length;
        const pending = data.filter(row => row.status === 'pending').length;
        const inactive = data.filter(row => row.status === 'inactive').length;

        $('#totalUsers').text(total);
        $('#activeUsers').text(active);
        $('#pendingUsers').text(pending);
        $('#inactiveUsers').text(inactive);
    }
</script>
@endpush
