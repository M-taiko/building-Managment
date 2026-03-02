@extends('layouts.resident')
@section('title', 'تفاصيل المدفوعات')
@section('page-title', 'مدفوعاتي')

@push('styles')
<style>
.payment-row { border-right: 4px solid #28a745; }
.section-header {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: #fff;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
@php
    $arabicMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
@endphp
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للرئيسية
        </a>
        <div class="text-center">
            <h4 class="mb-0 text-success fw-bold">
                <i class="fas fa-money-bill-wave me-2"></i>إجمالي المدفوعات:
                {{ number_format($totalPaid, 2) }} ج.م
            </h4>
        </div>
        <div></div>
    </div>

    {{-- اشتراكات مدفوعة --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-check-circle me-2"></i>
                الاشتراكات المدفوعة
                <span class="badge bg-white text-success ms-2">{{ $paymentDetails->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($paymentDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>تاريخ الدفع</th>
                                <th>نوع الاشتراك</th>
                                <th>الفترة</th>
                                <th>المبلغ المدفوع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentDetails as $i => $payment)
                                <tr class="payment-row">
                                    <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $payment->paid_at->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ $payment->paid_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $payment->subscriptionType->name ?? 'اشتراك شهري' }}</span>
                                    </td>
                                    <td>
                                        @if(isset($payment->month) && isset($payment->year))
                                            <span class="badge bg-light text-dark border">
                                                {{ $arabicMonths[$payment->month - 1] }} {{ $payment->year }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success fs-6">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ number_format($payment->paid_amount, 2) }} ج.م
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <th colspan="4" class="text-end pe-3">إجمالي الاشتراكات:</th>
                                <th class="text-success fw-bold fs-6">{{ number_format($paymentDetails->sum('paid_amount'), 2) }} ج.م</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">لا توجد اشتراكات مدفوعة</p>
                </div>
            @endif
        </div>
    </div>

    {{-- حصص مصروفات مدفوعة --}}
    @if($paidExpenseShares->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-receipt me-2"></i>
                    حصصي المسددة من مصروفات العمارة
                    <span class="badge bg-white text-info ms-2">{{ $paidExpenseShares->count() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>تاريخ السداد</th>
                                <th>المصروف</th>
                                <th>حصتي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paidExpenseShares as $i => $share)
                                <tr style="border-right: 4px solid #0dcaf0;">
                                    <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $share->paid_at ? \Carbon\Carbon::parse($share->paid_at)->format('Y-m-d') : '-' }}
                                        </div>
                                        @if($share->paid_at)
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($share->paid_at)->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $share->expense->title ?? '-' }}</td>
                                    <td>
                                        <span class="fw-bold text-info">{{ number_format($share->share_amount, 2) }} ج.م</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="3" class="text-end pe-3">إجمالي حصص المصروفات:</th>
                                <th class="text-info fw-bold">{{ number_format($paidExpenseShares->sum('share_amount'), 2) }} ج.م</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ملخص إجمالي --}}
    <div class="card border-success shadow-sm">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <p class="text-muted mb-1">اشتراكات مدفوعة</p>
                    <h4 class="text-success fw-bold">{{ number_format($paymentDetails->sum('paid_amount'), 2) }} ج.م</h4>
                </div>
                <div class="col-md-4">
                    <p class="text-muted mb-1">حصص مصروفات مسددة</p>
                    <h4 class="text-info fw-bold">{{ number_format($paidExpenseShares->sum('share_amount'), 2) }} ج.م</h4>
                </div>
                <div class="col-md-4 border-start">
                    <p class="text-muted mb-1">الإجمالي الكلي</p>
                    <h3 class="text-success fw-bold">{{ number_format($totalPaid, 2) }} ج.م</h3>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
