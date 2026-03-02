@extends('layouts.auth')

@section('title', 'إنشاء حساب جديد')

@section('header-title', 'إنشاء حساب')
@section('header-subtitle', 'قم بإنشاء حساب جديد للوصول إلى النظام')

@section('content')
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('status') }}
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0 pe-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">
                <i class="fas fa-user me-1"></i>
                الاسم الكامل
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-id-card"></i>
                </span>
                <input
                    id="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="أدخل اسمك الكامل"
                >
            </div>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="fas fa-envelope me-1"></i>
                البريد الإلكتروني
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-at"></i>
                </span>
                <input
                    id="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    placeholder="example@domain.com"
                >
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="fas fa-lock me-1"></i>
                كلمة المرور
            </label>
            <div class="input-group position-relative-custom">
                <span class="input-group-text">
                    <i class="fas fa-key"></i>
                </span>
                <input
                    id="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="6 أحرف على الأقل"
                    style="padding-left: 45px;"
                >
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                يجب أن تكون كلمة المرور 6 أحرف على الأقل
            </small>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">
                <i class="fas fa-lock me-1"></i>
                تأكيد كلمة المرور
            </label>
            <div class="input-group position-relative-custom">
                <span class="input-group-text">
                    <i class="fas fa-shield-alt"></i>
                </span>
                <input
                    id="password_confirmation"
                    type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="أعد إدخال كلمة المرور"
                    style="padding-left: 45px;"
                >
                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus me-2"></i>
                إنشاء الحساب
            </button>
        </div>

        <!-- Already registered Link -->
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="btn-link">
                <i class="fas fa-sign-in-alt me-1"></i>
                لديك حساب بالفعل؟ تسجيل الدخول
            </a>
        </div>
    </form>
@endsection
