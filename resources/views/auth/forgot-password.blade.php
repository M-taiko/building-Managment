@extends('layouts.auth')

@section('title', 'نسيت كلمة المرور')

@section('header-title', 'استعادة كلمة المرور')
@section('header-subtitle', 'أدخل بريدك الإلكتروني لاستلام رابط إعادة تعيين كلمة المرور')

@section('content')
    <!-- Info Message -->
    <div class="alert alert-info mb-4" style="background: #e0f2fe; color: #075985; border-radius: 10px;">
        <i class="fas fa-info-circle me-2"></i>
        <small>
            نسيت كلمة المرور؟ لا مشكلة. فقط أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة مرور جديدة.
        </small>
    </div>

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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
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
                    placeholder="example@domain.com"
                >
            </div>
            <small class="text-muted">
                <i class="fas fa-paper-plane me-1"></i>
                سنرسل رابط إعادة التعيين إلى هذا البريد
            </small>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane me-2"></i>
                إرسال رابط إعادة التعيين
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
