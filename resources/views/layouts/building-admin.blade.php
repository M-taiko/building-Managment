<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة العمارات')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        * { font-family: 'Cairo', sans-serif; }

        body {
            background-color: #f0f2f5;
            padding-bottom: 70px;
        }

        /* ===== Top Navbar ===== */
        .admin-topbar {
            position: fixed;
            top: 0; right: 0; left: 0;
            z-index: 200;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .admin-topbar .logo-text {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .admin-topbar .page-title-text {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            font-weight: 600;
        }

        .admin-topbar .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-topbar .user-info {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            display: none;
        }
        @media (min-width: 480px) { .admin-topbar .user-info { display: block; } }

        .user-avatar {
            background: rgba(255,255,255,0.25);
            border: 2px solid rgba(255,255,255,0.5);
            color: #fff;
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            cursor: pointer;
        }

        /* ===== Main Content ===== */
        .admin-content {
            padding: 72px 12px 16px;
        }

        /* ===== Bottom Navigation ===== */
        .bottom-nav {
            position: fixed;
            bottom: 0; right: 0; left: 0;
            z-index: 200;
            height: 64px;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            display: flex;
            align-items: stretch;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
        }

        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            color: #9e9e9e;
            font-size: 10px;
            gap: 3px;
            transition: color .2s;
        }

        .bottom-nav-item i { font-size: 20px; transition: transform .2s; }
        .bottom-nav-item.active,
        .bottom-nav-item:hover { color: #667eea; }
        .bottom-nav-item.active i { transform: scale(1.15); }

        /* ===== Cards ===== */
        .card { border-radius: 12px; border: none; }

        /* ===== Bottom Sheet (المزيد) ===== */
        .bottom-sheet-overlay {
            position: fixed;
            bottom: 64px; right: 0; left: 0;
            z-index: 150;
            background: rgba(0, 0, 0, 0.5);
            display: none;
        }

        .bottom-sheet-overlay.show {
            display: block;
        }

        .bottom-sheet {
            position: fixed;
            bottom: 64px; right: 0; left: 0;
            z-index: 160;
            background: #fff;
            border-radius: 20px 20px 0 0;
            max-height: 60vh;
            overflow-y: auto;
            transform: translateY(100%);
            transition: transform .3s ease;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.1);
        }

        .bottom-sheet.show {
            transform: translateY(0);
        }

        .bottom-sheet-handle {
            padding: 12px;
            text-align: center;
        }

        .bottom-sheet-handle::before {
            content: '';
            display: inline-block;
            width: 40px;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
        }

        .bottom-sheet-content {
            padding: 8px 16px 16px;
        }

        .bottom-sheet-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #2c3e50;
            border-radius: 8px;
            transition: all .2s;
        }

        .bottom-sheet-item:hover {
            background: #f0f2f5;
            color: #667eea;
        }

        .bottom-sheet-item i {
            font-size: 18px;
            min-width: 24px;
        }

        .bottom-sheet-item span {
            flex: 1;
            font-weight: 500;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Top Navbar -->
    <nav class="admin-topbar">
        <a href="{{ route('dashboard') }}" class="logo-text">
            <i class="fas fa-building"></i>
            <span class="d-none d-sm-inline">نظام العمارات</span>
        </a>

        <div class="page-title-text d-none d-md-block">@yield('page-title', '')</div>

        <div class="topbar-actions">
            <span class="user-info">{{ auth()->user()->name }}</span>
            <div class="dropdown">
                <button class="user-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-tie"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-start" style="min-width:200px;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:11px;">مدير العمارة</div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-circle me-2"></i> الملف الشخصي
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-2"></i> تغيير كلمة المرور
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="admin-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}"
           class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="{{ route('subscriptions.index') }}"
           class="bottom-nav-item {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>الاشتراكات</span>
        </a>
        <a href="{{ route('expenses.index') }}"
           class="bottom-nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>المصروفات</span>
        </a>
        <a href="{{ route('payments.index') }}"
           class="bottom-nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i>
            <span>المدفوعات</span>
        </a>
        <a href="{{ route('building-fund.index') }}"
           class="bottom-nav-item {{ request()->routeIs('building-fund.*') ? 'active' : '' }}">
            <i class="fas fa-piggy-bank"></i>
            <span>الصندوق</span>
        </a>
        <button class="bottom-nav-item" onclick="toggleBottomSheet()" type="button"
                style="background: none; border: none; cursor: pointer;">
            <i class="fas fa-ellipsis-h"></i>
            <span>المزيد</span>
        </button>
    </nav>

    <!-- Bottom Sheet (المزيد) -->
    <div class="bottom-sheet-overlay" id="bottomSheetOverlay" onclick="closeBottomSheet()"></div>
    <div class="bottom-sheet" id="bottomSheet">
        <div class="bottom-sheet-handle"></div>
        <div class="bottom-sheet-content">
            <a href="{{ route('apartments.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('apartments.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-building"></i>
                <span>إدارة الشقق</span>
            </a>
            <a href="{{ route('maintenance.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('maintenance.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-tools"></i>
                <span>الصيانة</span>
            </a>
            <a href="{{ route('users.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('users.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-users"></i>
                <span>المستخدمين</span>
            </a>
            <a href="{{ route('subscription-types.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('subscription-types.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-list"></i>
                <span>أنواع الاشتراكات</span>
            </a>
            <a href="{{ route('monthly-dues.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('monthly-dues.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>المطالب الشهرية</span>
            </a>
            <a href="{{ route('tenants.index') }}"
               class="bottom-sheet-item {{ request()->routeIs('tenants.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-building"></i>
                <span>العمارات</span>
            </a>
            <hr class="my-2">
            <a href="{{ route('profile.edit') }}"
               class="bottom-sheet-item {{ request()->routeIs('profile.*') ? 'text-primary fw-bold' : '' }}">
                <i class="fas fa-user-circle"></i>
                <span>الملف الشخصي</span>
            </a>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i> تغيير كلمة المرور</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="changePasswordForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')"><i class="fas fa-eye"></i></button>
                            </div>
                            <small class="text-muted">6 أحرف على الأقل</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        toastr.options = {
            closeButton: true, progressBar: true, newestOnTop: true,
            positionClass: 'toast-top-left', preventDuplicates: true, timeOut: 5000
        };

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function togglePassword(id) {
            const f = document.getElementById(id);
            const icon = f.nextElementSibling.querySelector('i');
            if (f.type === 'password') { f.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { f.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            const np = $('#new_password').val(), cp = $('#new_password_confirmation').val();
            if (np !== cp) { toastr.error('كلمة المرور وتأكيدها غير متطابقين'); return; }
            if (np.length < 6) { toastr.error('كلمة المرور يجب أن تكون 6 أحرف على الأقل'); return; }
            $.ajax({
                url: '{{ route("profile.password.update") }}', method: 'POST', data: $(this).serialize(),
                success: function(r) {
                    toastr.success(r.message);
                    $('#changePasswordModal').modal('hide');
                    setTimeout(() => $('form[action="{{ route("logout") }}"]').submit(), 2000);
                },
                error: function(xhr) {
                    if (xhr.status === 422) { Object.values(xhr.responseJSON.errors).forEach(e => toastr.error(e[0])); }
                    else { toastr.error('حدث خطأ أثناء تغيير كلمة المرور'); }
                }
            });
        });

        // Bottom Sheet Functions
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottomSheet');
            const overlay = document.getElementById('bottomSheetOverlay');
            sheet.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function closeBottomSheet() {
            const sheet = document.getElementById('bottomSheet');
            const overlay = document.getElementById('bottomSheetOverlay');
            sheet.classList.remove('show');
            overlay.classList.remove('show');
        }

        // Close sheet when clicking on a link
        document.querySelectorAll('.bottom-sheet-item').forEach(link => {
            link.addEventListener('click', closeBottomSheet);
        });
    </script>

    @stack('scripts')
</body>
</html>
