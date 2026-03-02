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

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Modern UI/UX -->
    <link rel="stylesheet" href="{{ asset('css/modern-ui.css') }}">

    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }

        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar .logo {
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .sidebar .nav-link i {
            margin-left: 10px;
            width: 20px;
        }

        .sidebar .section-title {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 15px 20px 5px;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        .sidebar .nav-content {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar .nav-content::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .main-content {
            margin-right: 260px;
            padding: 20px;
        }

        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            font-weight: 600;
        }

        .stat-card {
            border-right: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary { border-color: #667eea; }
        .stat-card.success { border-color: #10b981; }
        .stat-card.warning { border-color: #f59e0b; }
        .stat-card.danger { border-color: #ef4444; }
        .stat-card.info { border-color: #3b82f6; }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-right: 10px;
        }

        /* Toastr Custom Styles - ألوان واضحة وغير شفافة */
        #toast-container > div {
            opacity: 1 !important;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3) !important;
            border-radius: 12px !important;
            padding: 18px 25px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            min-width: 300px !important;
            backdrop-filter: none !important;
            border: none !important;
        }

        /* Success - أخضر واضح */
        #toast-container > .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            background-color: #10b981 !important;
            color: #ffffff !important;
        }

        #toast-container > .toast-success:before {
            content: "✓" !important;
            font-size: 26px !important;
            font-weight: bold !important;
            margin-left: 12px !important;
        }

        /* Error - أحمر واضح */
        #toast-container > .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            background-color: #ef4444 !important;
            color: #ffffff !important;
        }

        #toast-container > .toast-error:before {
            content: "✕" !important;
            font-size: 26px !important;
            font-weight: bold !important;
            margin-left: 12px !important;
        }

        /* Warning - برتقالي/أصفر واضح */
        #toast-container > .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }

        #toast-container > .toast-warning:before {
            content: "⚠" !important;
            font-size: 26px !important;
            font-weight: bold !important;
            margin-left: 12px !important;
        }

        /* Info - أزرق واضح */
        #toast-container > .toast-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            background-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        #toast-container > .toast-info:before {
            content: "ℹ" !important;
            font-size: 26px !important;
            font-weight: bold !important;
            margin-left: 12px !important;
        }

        /* Toast Title */
        #toast-container > div .toast-title {
            font-weight: 700 !important;
            color: #ffffff !important;
            opacity: 1 !important;
        }

        /* Toast Message */
        #toast-container > div .toast-message {
            color: #ffffff !important;
            opacity: 1 !important;
        }

        /* Progress Bar */
        #toast-container > div .toast-progress {
            background-color: rgba(255, 255, 255, 0.9) !important;
            height: 5px !important;
            opacity: 1 !important;
        }

        /* Close Button */
        #toast-container > div .toast-close-button {
            color: #ffffff !important;
            opacity: 0.9 !important;
            font-size: 22px !important;
            font-weight: bold !important;
        }

        #toast-container > div .toast-close-button:hover {
            opacity: 1 !important;
        }

        /* Hover Effect */
        #toast-container > div:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4) !important;
            transform: translateY(-3px) !important;
            transition: all 0.3s ease !important;
            opacity: 1 !important;
        }

        /* Override any conflicting styles */
        .toast {
            opacity: 1 !important;
        }

        /* Container positioning */
        #toast-container {
            z-index: 999999 !important;
        }

        /* ========== Mobile Responsive Styles ========== */

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1100;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Tablet and Mobile (less than 992px) */
        @media (max-width: 991px) {
            /* Show mobile menu toggle */
            .mobile-menu-toggle {
                display: block;
            }

            /* Hide sidebar by default on mobile */
            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            /* Show sidebar when active */
            .sidebar.active {
                transform: translateX(0);
            }

            /* Adjust main content */
            .main-content {
                margin-right: 0;
                padding: 15px;
                padding-top: 70px;
            }

            /* Top navbar adjustments */
            .top-navbar {
                padding: 10px 15px;
                font-size: 14px;
            }

            .top-navbar h5 {
                font-size: 16px;
            }

            /* Cards responsive */
            .card {
                margin-bottom: 15px;
            }

            /* Table responsive wrapper */
            .table-responsive {
                font-size: 12px;
            }

            /* Buttons smaller on mobile */
            .btn-sm {
                font-size: 11px;
                padding: 4px 8px;
            }

            /* Stat cards stack on mobile */
            .stat-card {
                margin-bottom: 15px;
            }

            /* Modal adjustments */
            .modal-dialog {
                margin: 10px;
            }

            /* Form controls */
            .form-control,
            .form-select {
                font-size: 14px;
            }

            /* Sidebar logo */
            .sidebar .logo {
                font-size: 18px;
                padding: 15px;
            }

            /* Sidebar nav links */
            .sidebar .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }

            .sidebar .section-title {
                font-size: 10px;
                padding: 10px 15px 3px;
            }
        }

        /* Small Mobile (less than 576px) */
        @media (max-width: 575px) {
            .main-content {
                padding: 10px;
                padding-top: 65px;
            }

            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            /* Hide some columns in tables on very small screens */
            .table td:not(:first-child):not(:last-child),
            .table th:not(:first-child):not(:last-child) {
                display: none;
            }

            /* Stack action buttons */
            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                width: 100%;
                margin-bottom: 5px;
            }

            /* Adjust card padding */
            .card-body {
                padding: 15px;
            }

            /* Smaller font sizes */
            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }

            h3 {
                font-size: 18px;
            }

            h4 {
                font-size: 16px;
            }

            h5 {
                font-size: 14px;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Touch-friendly spacing */
        @media (max-width: 991px) {
            .btn {
                min-height: 44px;
            }

            .nav-link {
                min-height: 44px;
            }

            a, button {
                -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
            }
        }

        /* DataTables responsive */
        @media (max-width: 991px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: center;
                margin-bottom: 10px;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
                margin-top: 10px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-building"></i> نظام العمارات
        </div>

        <div class="nav-content">
            <nav class="nav flex-column">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> الرئيسية
                </a>

                @if(auth()->user()->role !== 'resident')
                    <div class="section-title">إدارة الشقق</div>
                    <a href="{{ route('apartments.index') }}" class="nav-link {{ request()->routeIs('apartments.*') ? 'active' : '' }}">
                        <i class="fas fa-door-open"></i> الشقق
                    </a>

                    <div class="section-title">الاشتراكات الشهرية</div>
                    <a href="{{ route('subscription-types.index') }}" class="nav-link {{ request()->routeIs('subscription-types.*') ? 'active' : '' }}">
                        <i class="fas fa-list-alt"></i> أنواع الاشتراكات
                    </a>

                    <a href="{{ route('subscriptions.payments') }}" class="nav-link {{ request()->is('subscription-payments') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i> تسجيل الدفعات
                    </a>

                    <a href="{{ route('subscriptions.index') }}" class="nav-link {{ request()->routeIs('subscriptions.index') || request()->routeIs('subscriptions.create') || request()->routeIs('subscriptions.edit') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> سجل الاشتراكات
                    </a>

                    <div class="section-title">المصروفات</div>
                    <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i> إدارة المصروفات
                    </a>

                    <div class="section-title">المدفوعات</div>
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.index') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i> تسجيل المدفوعات
                    </a>
                    <a href="{{ route('payments.history') }}" class="nav-link {{ request()->routeIs('payments.history') ? 'active' : '' }}">
                        <i class="fas fa-history"></i> سجل المدفوعات
                    </a>

                    <div class="section-title">المستحقات الشهرية</div>
                    <a href="{{ route('monthly-dues.index') }}" class="nav-link {{ request()->routeIs('monthly-dues.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i> المستحقات الشهرية
                    </a>

                    <div class="section-title">حساب العمارة</div>
                    <a href="{{ route('building-fund.index') }}" class="nav-link {{ request()->routeIs('building-fund.index') ? 'active' : '' }}">
                        <i class="fas fa-piggy-bank"></i> حساب العمارة
                    </a>
                @endif

                <div class="section-title">الصيانة</div>
                <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                    <i class="fas fa-wrench"></i> طلبات الصيانة
                </a>

                @if(auth()->user()->role === 'super_admin')
                    <div class="section-title">إدارة النظام</div>
                    <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i> العمارات
                    </a>
                @endif

                @if(auth()->user()->role === 'building_admin')
                    <div class="section-title">المستخدمين</div>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> إدارة المستخدمين
                    </a>
                @endif

                @if(auth()->user()->role === 'resident')
                    <div class="section-title">الإشعارات</div>
                    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i> الإشعارات
                        @php
                            $unreadCount = auth()->user()->unreadNotifications()->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger rounded-pill ms-auto" id="notification-count">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <div class="section-title">الشفافية المالية</div>
                    <a href="{{ route('building-fund.resident') }}" class="nav-link {{ request()->routeIs('building-fund.resident') ? 'active' : '' }}">
                        <i class="fas fa-eye"></i> حساب العمارة
                    </a>
                @endif
            </nav>
        </div>

        <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link" style="border: none; background: none; width: 100%; text-align: right;">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'لوحة التحكم')</h5>
                <small class="text-muted">مرحباً، {{ auth()->user()->name }}</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link position-relative p-2" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; color: #667eea;">
                        <i class="fas fa-bell fa-lg"></i>
                        @php
                            $unreadCount = auth()->user()->role === 'resident'
                                ? \App\Models\NotificationLog::where('user_id', auth()->id())->where('is_read', false)->count()
                                : 0;
                        @endphp
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell"></i> الإشعارات</span>
                            @if($unreadCount > 0)
                                <span class="badge bg-primary">{{ $unreadCount }}</span>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @if(auth()->user()->role === 'resident')
                            @php
                                $notifications = \App\Models\NotificationLog::where('user_id', auth()->id())
                                    ->orderBy('sent_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @forelse($notifications as $notification)
                                <li>
                                    <a class="dropdown-item {{ !$notification->is_read ? 'bg-light' : '' }}" href="{{ route('notifications.index') }}" style="white-space: normal; padding: 10px 15px;">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-{{ $notification->type === 'expense' ? 'receipt' : ($notification->type === 'payment' ? 'money-bill' : 'info-circle') }} text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <strong class="d-block">{{ $notification->title }}</strong>
                                                <small class="text-muted d-block" style="font-size: 12px;">{{ Str::limit($notification->message, 50) }}</small>
                                                <small class="text-muted" style="font-size: 11px;">
                                                    <i class="far fa-clock"></i> {{ $notification->sent_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                @if(!$loop->last)
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @empty
                                <li>
                                    <div class="dropdown-item text-center text-muted" style="padding: 20px;">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        لا توجد إشعارات
                                    </div>
                                </li>
                            @endforelse
                            @if($notifications->count() > 0)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center text-primary" href="{{ route('notifications.index') }}">
                                        <strong>عرض جميع الإشعارات</strong>
                                    </a>
                                </li>
                            @endif
                        @else
                            <li>
                                <div class="dropdown-item text-center text-muted" style="padding: 20px;">
                                    <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                                    الإشعارات متاحة للمقيمين فقط
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link d-flex align-items-center gap-2 p-0" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; color: inherit;">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="text-end d-none d-md-block">
                            <div style="font-size: 14px; font-weight: 600;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 11px;" class="text-muted">{{ auth()->user()->role === 'building_admin' ? 'مدير العمارة' : (auth()->user()->role === 'super_admin' ? 'مدير النظام' : 'مقيم') }}</div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-circle"></i> الملف الشخصي
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fas fa-key"></i> تغيير كلمة المرور
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        @yield('content')

        <!-- Footer -->
        <footer class="mt-5 py-3 text-center" style="border-top: 1px solid #e9ecef;">
            <p class="text-muted mb-0" style="font-size: 14px;">
                تم التطوير بواسطة <a href="https://masarsoft.io" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">masarsoft.io</a>
            </p>
        </footer>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="fas fa-key"></i> تغيير كلمة المرور
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changePasswordForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">يجب أن تكون 6 أحرف على الأقل</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            <small>
                                <strong>ملاحظة:</strong> سيتم تسجيل خروجك تلقائياً بعد تغيير كلمة المرور للتأكد من أمان حسابك.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Toastr configuration - إعدادات محسنة للوضوح
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-left",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "400",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "slideDown",
            "hideMethod": "slideUp",
            "opacity": 1,
            "toastClass": "toast",
            "iconClass": "toast-info",
            "positionClass": "toast-top-left",
            "containerId": "toast-container"
        };

        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Change password form submission
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();

            const newPassword = $('#new_password').val();
            const confirmPassword = $('#new_password_confirmation').val();

            // التحقق من تطابق كلمات المرور
            if (newPassword !== confirmPassword) {
                toastr.error('كلمة المرور الجديدة وتأكيد كلمة المرور غير متطابقين');
                return;
            }

            // التحقق من طول كلمة المرور
            if (newPassword.length < 6) {
                toastr.error('يجب أن تكون كلمة المرور 6 أحرف على الأقل');
                return;
            }

            $.ajax({
                url: '{{ route("profile.password.update") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    $('#changePasswordModal').modal('hide');
                    $('#changePasswordForm')[0].reset();

                    // تسجيل خروج تلقائي بعد 2 ثانية
                    setTimeout(function() {
                        window.location.href = '{{ route("logout") }}';
                        // أو يمكن عمل submit للـ logout form
                        $('form[action="{{ route("logout") }}"]').submit();
                    }, 2000);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            toastr.error(errors[key][0]);
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('حدث خطأ أثناء تغيير كلمة المرور');
                    }
                }
            });
        });

        // تحديث عدد الإشعارات كل 30 ثانية
        @if(auth()->user()->role === 'resident')
        setInterval(function() {
            $.get('{{ route("notifications.unread-count") }}', function(response) {
                const count = response.count;
                const badge = $('.fa-bell').parent().find('.badge');

                if (count > 0) {
                    if (badge.length === 0) {
                        $('.fa-bell').parent().append(
                            '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">' +
                            (count > 99 ? '99+' : count) +
                            '</span>'
                        );
                    } else {
                        badge.text(count > 99 ? '99+' : count);
                    }
                } else {
                    badge.remove();
                }
            });
        }, 30000); // كل 30 ثانية
        @endif
    </script>

    <!-- Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Toggle sidebar on mobile
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                });
            }

            // Close sidebar when clicking a link on mobile
            const sidebarLinks = sidebar.querySelectorAll('.nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });

            // Close sidebar on window resize if larger than mobile breakpoint
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
