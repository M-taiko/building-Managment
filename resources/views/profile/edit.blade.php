@extends('layouts.admin')

@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')

@section('content')
<div class="container-fluid">
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            تم تحديث معلومات الملف الشخصي بنجاح!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Profile Information Card -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-user-circle me-2"></i>
                        معلومات الملف الشخصي
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" class="needs-validation">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-12">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    الاسم الكامل
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    autofocus
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-12">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-primary me-1"></i>
                                    البريد الإلكتروني
                                </label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="alert alert-warning mt-2">
                                        <small>
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            بريدك الإلكتروني غير موثق.
                                            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-link p-0">
                                                    انقر هنا لإعادة إرسال رابط التوثيق
                                                </button>
                                            </form>
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- Phone (if exists in users table) -->
                            @if(Schema::hasColumn('users', 'phone'))
                            <div class="col-md-12">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone text-primary me-1"></i>
                                    رقم الهاتف
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', $user->phone) }}"
                                >
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Info Card -->
        <div class="col-lg-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>

                    <div class="badge bg-primary mb-3" style="font-size: 14px;">
                        @if($user->role === 'super_admin')
                            <i class="fas fa-crown me-1"></i> مدير النظام
                        @elseif($user->role === 'building_admin')
                            <i class="fas fa-user-tie me-1"></i> مدير العمارة
                        @else
                            <i class="fas fa-user me-1"></i> مقيم
                        @endif
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-2"></i>
                            تغيير كلمة المرور
                        </button>

                        @if($user->tenant)
                        <div class="text-start mt-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-building me-1"></i>
                                معلومات العمارة
                            </h6>
                            <p class="small mb-1">
                                <strong>الاسم:</strong> {{ $user->tenant->name }}
                            </p>
                            <p class="small mb-1">
                                <strong>الرمز:</strong>
                                <span class="badge bg-secondary">{{ $user->tenant->tenant_code }}</span>
                            </p>
                        </div>
                        @endif

                        @if($user->apartment)
                        <div class="text-start mt-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-door-open me-1"></i>
                                معلومات الشقة
                            </h6>
                            <p class="small mb-1">
                                <strong>رقم الشقة:</strong> {{ $user->apartment->number }}
                            </p>
                            <p class="small mb-1">
                                <strong>الدور:</strong> {{ $user->apartment->floor }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Stats -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-chart-line text-primary me-1"></i>
                        إحصائيات الحساب
                    </h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">تاريخ الانضمام</span>
                        <span class="badge bg-light text-dark">{{ $user->created_at->format('Y-m-d') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">آخر تحديث</span>
                        <span class="badge bg-light text-dark">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .bg-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .avatar-circle {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endsection
