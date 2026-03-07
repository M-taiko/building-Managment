@extends('layouts.building-admin')

@section('title', 'إضافة مقيم جديد')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>عودة
                </a>
                <h1 class="h3 mb-0 text-dark fw-bold">إضافة مقيم جديد</h1>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card modern-card">
                <div class="card-header bg-gradient">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-user-plus me-2"></i>بيانات المقيم
                    </h5>
                </div>
                <div class="card-body">
                    <form id="userForm" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="tenant_id" class="form-label">العمارة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id" required>
                                        <option value="">-- اختر عمارة --</option>
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="unit_number" class="form-label">رقم الوحدة <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                        <input type="text" class="form-control @error('unit_number') is-invalid @enderror" id="unit_number" name="unit_number" value="{{ old('unit_number') }}" required>
                                    </div>
                                    @error('unit_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="user_type" class="form-label">نوع المستخدم <span class="text-danger">*</span></label>
                                    <select class="form-control @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                        <option value="">-- اختر النوع --</option>
                                        <option value="resident" {{ old('user_type') === 'resident' ? 'selected' : '' }}>مقيم</option>
                                        <option value="owner" {{ old('user_type') === 'owner' ? 'selected' : '' }}>مالك</option>
                                        <option value="tenant" {{ old('user_type') === 'tenant' ? 'selected' : '' }}>مستأجر</option>
                                    </select>
                                    @error('user_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>معطل</option>
                                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" {{ old('is_admin') ? 'checked' : '' }}>
                                        <span class="form-check-label">منح صلاحيات إدارية</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                        <button type="submit" form="userForm" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i>حفظ المستخدم
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
