@extends('layouts.admin')

@section('title', 'إعدادات المستحقات الشهرية')
@section('page-title', 'إعدادات المستحقات الشهرية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-cog"></i> إعدادات المستحقات الشهرية</h2>
                <a href="{{ route('monthly-dues.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> رجوع
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">تحديد المبلغ الشهري لجميع الشقق</h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">المبلغ الشهري (ج.م)</label>
                            <input type="number" name="monthly_amount" class="form-control form-control-lg"
                                   step="0.01" min="0" required placeholder="مثال: 100.00">
                            <small class="text-muted">هذا المبلغ سيتم تطبيقه على جميع الشقق النشطة</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">تطبيق على</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="apply_to"
                                       id="applyNew" value="existing" checked>
                                <label class="form-check-label" for="applyNew">
                                    الشقق الجديدة فقط (لم يتم إنشاء مستحقات لها بعد)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="apply_to"
                                       id="applyAll" value="all">
                                <label class="form-check-label" for="applyAll">
                                    جميع الشقق (تحديث المستحقات الموجودة غير المدفوعة)
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>ملاحظة:</strong>
                            سيتم إنشاء مستحقات شهرية للشهر الحالي ({{ now()->format('Y-m') }}) لجميع الشقق النشطة.
                            المستحقات المدفوعة بالكامل لن تتأثر.
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> حفظ وتطبيق
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">الحالة الحالية</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-primary">{{ $apartments->count() }}</h3>
                                <p class="text-muted">عدد الشقق النشطة</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-success">{{ now()->format('Y-m') }}</h3>
                                <p class="text-muted">الشهر الحالي</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-info">{{ now()->locale('ar')->monthName }}</h3>
                                <p class="text-muted">اسم الشهر</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stat-box {
    padding: 20px;
    border-radius: 8px;
    background-color: #f8f9fa;
}
.stat-box h3 {
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("monthly-dues.update-amount") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response.message);
                window.location.href = '{{ route("monthly-dues.index") }}';
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'حدث خطأ');
            }
        });
    });
});
</script>
@endpush
@endsection
