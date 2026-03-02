@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('header-title', 'مرحباً بك')
@section('header-subtitle', 'قم بتسجيل الدخول للوصول إلى حسابك')

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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Tenant Code -->
        <div class="mb-3">
            <label for="tenant_code" class="form-label">
                <i class="fas fa-building me-1"></i>
                رمز العمارة
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-hashtag"></i>
                </span>
                <input
                    id="tenant_code"
                    type="text"
                    class="form-control @error('tenant_code') is-invalid @enderror"
                    name="tenant_code"
                    value="{{ old('tenant_code') }}"
                    placeholder="مثال: E001 (اتركه فارغاً لمدير النظام)"
                    autocomplete="off"
                >
            </div>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                اترك هذا الحقل فارغاً إذا كنت مدير النظام
            </small>
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
                    autofocus
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
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="padding-left: 45px;"
                >
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember_me"
                >
                <label class="form-check-label" for="remember_me">
                    تذكرني
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>
                تسجيل الدخول
            </button>
        </div>

        <!-- Forgot Password Link -->
        @if (Route::has('password.request'))
            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}" class="btn-link">
                    <i class="fas fa-question-circle me-1"></i>
                    نسيت كلمة المرور؟
                </a>
            </div>
        @endif
    </form>
@endsection
