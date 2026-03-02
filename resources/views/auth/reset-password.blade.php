@extends('layouts.auth')

@section('title', 'إعادة تعيين كلمة المرور')

@section('header-title', 'تعيين كلمة مرور جديدة')
@section('header-subtitle', 'أدخل كلمة المرور الجديدة لحسابك')

@section('content')
    <!-- Info Message -->
    <div class="alert alert-info mb-4" style="background: #e0f2fe; color: #075985; border-radius: 10px;">
        <i class="fas fa-shield-alt me-2"></i>
        <small>
            يرجى إدخال كلمة مرور جديدة قوية لحماية حسابك. يجب أن تكون 6 أحرف على الأقل.
        </small>
    </div>

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

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address (Read-only) -->
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
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    readonly
                    style="background-color: #f8f9fa;"
                >
            </div>
            <small class="text-muted">
                <i class="fas fa-lock me-1"></i>
                هذا هو حسابك المرتبط برابط إعادة التعيين
            </small>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="fas fa-lock me-1"></i>
                كلمة المرور الجديدة
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
                <i class="fas fa-check-circle me-2"></i>
                إعادة تعيين كلمة المرور
            </button>
        </div>

        <!-- Back to Login Link -->
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="btn-link">
                <i class="fas fa-arrow-right me-1"></i>
                العودة إلى تسجيل الدخول
            </a>
        </div>
    </form>
@endsection
