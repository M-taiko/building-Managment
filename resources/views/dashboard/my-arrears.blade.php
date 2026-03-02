@extends('layouts.resident')
@section('title', 'تفاصيل المتأخرات')
@section('page-title', 'متأخراتي')

@push('styles')
<style>
.arrear-row-unpaid  { border-right: 4px solid #dc3545; }
.arrear-row-partial { border-right: 4px solid #ffc107; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للرئيسية
        </a>
        @if($totalArrears > 0)
            <div class="alert alert-danger mb-0 py-2 px-4 rounded-pill d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>إجمالي المتأخرات: <strong>{{ number_format($totalArrears, 2) }} ج.م</strong></span>
            </div>
        @else
            <div class="alert alert-success mb-0 py-2 px-4 rounded-pill d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><strong>لا توجد متأخرات</strong></span>
            </div>
        @endif
        <div></div>
    </div>

    @if($totalArrears == 0)
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                <h4 class="text-success fw-bold">رائع! لا توجد متأخرات عليك</h4>
                <p class="text-muted">جميع مستحقاتك مسددة بالكامل</p>
                <a href="{{ route('dashboard') }}" class="btn btn-success mt-2">
                    <i class="fas fa-home me-1"></i> العودة للرئيسية
                </a>
            </div>
        </div>
    @else

        {{-- متأخرات الاشتراكات الشهرية --}}
        @if($arrearsDetails->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-times me-2"></i>
                        متأخرات الاشتراكات الشهرية
                        <span class="badge bg-white text-danger ms-2">{{ $arrearsDetails->count() }} شهر</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">الفترة</th>
                                    <th>المطلوب</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrearsDetails as $detail)
                                    <tr class="{{ $detail['status'] === 'unpaid' ? 'arrear-row-unpaid' : 'arrear-row-partial' }}">
                                        <td class="ps-3">
                                            <span class="fw-semibold">{{ $detail['month_name'] }}</span>
                                            <small class="text-muted d-block">{{ $detail['year'] }}</small>
                                        </td>
                                        <td>{{ number_format($detail['amount'], 2) }} ج.م</td>
                                        <td class="text-success">{{ number_format($detail['paid_amount'], 2) }} ج.م</td>
                                        <td>
                                            <span class="fw-bold text-danger fs-6">{{ number_format($detail['remaining'], 2) }} ج.م</span>
                                        </td>
                                        <td>
                                            @if($detail['status'] === 'unpaid')
                                                <span class="badge bg-danger rounded-pill px-3">
                                                    <i class="fas fa-times me-1"></i>غير مدفوع
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-3">
                                                    <i class="fas fa-clock me-1"></i>جزئي
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-danger">
                                <tr>
                                    <th class="ps-3">الإجمالي</th>
                                    <th>{{ number_format($arrearsDetails->sum('amount'), 2) }} ج.م</th>
                                    <th class="text-success">{{ number_format($arrearsDetails->sum('paid_amount'), 2) }} ج.م</th>
                                    <th class="text-danger fw-bold">{{ number_format($arrearsDetails->sum('remaining'), 2) }} ج.م</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- حصص مصروفات غير مسددة --}}
        @if($unpaidExpenseShares->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        حصصي غير المسددة من مصروفات العمارة
                        <span class="badge bg-dark ms-2">{{ $unpaidExpenseShares->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>المصروف</th>
                                    <th>تاريخ التوزيع</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidExpenseShares as $i => $share)
                                    <tr class="{{ $share->paid_amount > 0 ? 'arrear-row-partial' : 'arrear-row-unpaid' }}">
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $share->expense->title ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $share->created_at->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ $share->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-success">{{ number_format($share->paid_amount, 2) }} ج.م</td>
                                        <td>
                                            <span class="fw-bold text-danger">{{ number_format($share->remaining_amount, 2) }} ج.م</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-warning">
                                <tr>
                                    <th colspan="3" class="text-end pe-3">إجمالي حصص المصروفات:</th>
                                    <th class="text-success">{{ number_format($unpaidExpenseShares->sum('paid_amount'), 2) }} ج.م</th>
                                    <th class="text-danger fw-bold">{{ number_format($unpaidExpenseShares->sum(fn($s) => $s->remaining_amount), 2) }} ج.م</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ملخص إجمالي --}}
        <div class="card border-danger shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    @if($arrearsDetails->count() > 0)
                        <div class="col-md-4">
                            <p class="text-muted mb-1">متأخرات الاشتراكات</p>
                            <h4 class="text-danger fw-bold">{{ number_format($arrearsDetails->sum('remaining'), 2) }} ج.م</h4>
                        </div>
                    @endif
                    @if($unpaidExpenseShares->count() > 0)
                        <div class="col-md-4">
                            <p class="text-muted mb-1">حصص مصروفات غير مسددة</p>
                            <h4 class="text-warning fw-bold">{{ number_format($unpaidExpenseShares->sum(fn($s) => $s->remaining_amount), 2) }} ج.م</h4>
                        </div>
                    @endif
                    <div class="col-md-4 border-start">
                        <p class="text-muted mb-1">الإجمالي الكلي</p>
                        <h3 class="text-danger fw-bold">{{ number_format($totalArrears, 2) }} ج.م</h3>
                    </div>
                </div>
                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        يرجى التواصل مع إدارة العمارة لتسوية المتأخرات
                    </p>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection
