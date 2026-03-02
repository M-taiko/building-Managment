<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة العمارات')</title>

    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        * { font-family: 'Cairo', sans-serif; }

        body {
            background-color: #f0f2f5;
            padding-bottom: 75px; /* space for bottom nav */
        }

        /* ===== Top Navbar ===== */
        .resident-topbar {
            position: fixed;
            top: 0; right: 0; left: 0;
            z-index: 100;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .resident-topbar .logo-text {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .resident-topbar .page-title-text {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            font-weight: 600;
        }

        .resident-topbar .topbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .resident-topbar .btn-icon {
            background: rgba(255,255,255,0.15);
            border: none;
            color: #fff;
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            position: relative;
            transition: background .2s;
        }
        .resident-topbar .btn-icon:hover { background: rgba(255,255,255,0.25); }

        .resident-topbar .notif-badge {
            position: absolute;
            top: 2px; left: 2px;
            background: #ff4757;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            min-width: 16px; height: 16px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px;
        }

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
        .resident-content {
            padding: 72px 12px 16px;
        }

        /* ===== Bottom Navigation ===== */
        .bottom-nav {
            position: fixed;
            bottom: 0; right: 0; left: 0;
            z-index: 100;
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
            position: relative;
        }

        .bottom-nav-item i {
            font-size: 20px;
            transition: transform .2s;
        }

        .bottom-nav-item.active,
        .bottom-nav-item:hover {
            color: #667eea;
        }

        .bottom-nav-item.active i { transform: scale(1.15); }

        .bottom-nav-item .nav-badge {
            position: absolute;
            top: 6px;
            left: calc(50% + 5px);
            background: #ff4757;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            min-width: 14px; height: 14px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px;
        }

        /* ===== Notification Dropdown ===== */
        .notif-dropdown {
            min-width: 300px;
            max-height: 380px;
            overflow-y: auto;
        }

        /* ===== Cards ===== */
        .card { border-radius: 12px; border: none; }
        .card-header { border-radius: 12px 12px 0 0 !important; }

        /* ===== Responsive tweaks ===== */
        @media (max-width: 575px) {
            .resident-topbar .logo-text span { display: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Top Navbar -->
    <nav class="resident-topbar">
        <a href="{{ route('dashboard') }}" class="logo-text text-decoration-none">
            <i class="fas fa-building"></i>
            <span>نظام العمارات</span>
        </a>

        <div class="page-title-text d-none d-sm-block">@yield('page-title', '')</div>

        <div class="topbar-actions">
            <!-- Notifications -->
            @php
                $topbarUnread = \App\Models\NotificationLog::where('user_id', auth()->id())->where('is_read', false)->count();
                $topbarNotifs = \App\Models\NotificationLog::where('user_id', auth()->id())->orderBy('sent_at','desc')->limit(5)->get();
            @endphp
            <div class="dropdown">
                <button class="btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="topbarNotifBtn">
                    <i class="fas fa-bell"></i>
                    @if($topbarUnread > 0)
                        <span class="notif-badge" id="topbar-notif-count">{{ $topbarUnread > 99 ? '99+' : $topbarUnread }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-start notif-dropdown" aria-labelledby="topbarNotifBtn">
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                        <strong><i class="fas fa-bell me-1 text-primary"></i> الإشعارات</strong>
                        @if($topbarUnread > 0)
                            <span class="badge bg-danger rounded-pill">{{ $topbarUnread }}</span>
                        @endif
                    </li>
                    @forelse($topbarNotifs as $notif)
                        <li>
                            <a class="dropdown-item py-2 {{ !$notif->is_read ? 'bg-light' : '' }}"
                               href="{{ route('notifications.index') }}"
                               style="white-space: normal;">
                                <div class="d-flex gap-2">
                                    <i class="fas fa-{{ $notif->type === 'expense' ? 'receipt' : ($notif->type === 'payment' ? 'money-bill' : 'info-circle') }} text-primary mt-1 flex-shrink-0"></i>
                                    <div>
                                        <div class="fw-semibold small">{{ $notif->title }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ Str::limit($notif->message, 55) }}</div>
                                        <div class="text-muted" style="font-size:10px;"><i class="far fa-clock"></i> {{ $notif->sent_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @if(!$loop->last)<li><hr class="dropdown-divider my-0"></li>@endif
                    @empty
                        <li class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            لا توجد إشعارات
                        </li>
                    @endforelse
                    @if($topbarNotifs->count() > 0)
                        <li><hr class="dropdown-divider my-0"></li>
                        <li>
                            <a class="dropdown-item text-center text-primary small fw-bold py-2" href="{{ route('notifications.index') }}">
                                عرض جميع الإشعارات
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="user-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-start" style="min-width:200px;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:11px;">مقيم</div>
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
    <div class="resident-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    @php
        $bottomUnread = $topbarUnread;
    @endphp
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}"
           class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="{{ route('resident.account') }}"
           class="bottom-nav-item {{ request()->routeIs('resident.account') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            <span>رصيدي</span>
        </a>
        <a href="{{ route('resident.payments') }}"
           class="bottom-nav-item {{ request()->routeIs('resident.payments') ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i>
            <span>مدفوعاتي</span>
        </a>
        <a href="{{ route('resident.arrears') }}"
           class="bottom-nav-item {{ request()->routeIs('resident.arrears') ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle"></i>
            <span>متأخراتي</span>
        </a>
        <a href="{{ route('resident.maintenance') }}"
           class="bottom-nav-item {{ request()->routeIs('resident.maintenance') ? 'active' : '' }}">
            <i class="fas fa-tools"></i>
            <span>الصيانة</span>
        </a>
        <a href="{{ route('building-fund.resident') }}"
           class="bottom-nav-item {{ request()->routeIs('building-fund.resident') ? 'active' : '' }}">
            <i class="fas fa-piggy-bank"></i>
            <span>صندوق</span>
        </a>
        <a href="{{ route('notifications.index') }}"
           class="bottom-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            <span>إشعارات</span>
            @if($bottomUnread > 0)
                <span class="nav-badge" id="bottom-notif-badge">{{ $bottomUnread > 99 ? '99+' : $bottomUnread }}</span>
            @endif
        </a>
    </nav>

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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        toastr.options = {
            closeButton: true, progressBar: true, newestOnTop: true,
            positionClass: 'toast-top-left', preventDuplicates: true,
            timeOut: 5000
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

        // Update notification count every 30s
        setInterval(function() {
            $.get('{{ route("notifications.unread-count") }}', function(r) {
                const c = r.count;
                const topBadge = $('#topbar-notif-count');
                const botBadge = $('#bottom-notif-badge');
                if (c > 0) {
                    const txt = c > 99 ? '99+' : c;
                    if (topBadge.length) topBadge.text(txt); else $('#topbarNotifBtn').append(`<span class="notif-badge" id="topbar-notif-count">${txt}</span>`);
                    if (botBadge.length) botBadge.text(txt); else $('a[href="{{ route("notifications.index") }}"].bottom-nav-item').append(`<span class="nav-badge" id="bottom-notif-badge">${txt}</span>`);
                } else {
                    topBadge.remove();
                    botBadge.remove();
                }
            });
        }, 30000);
    </script>

    @stack('scripts')
</body>
</html>
