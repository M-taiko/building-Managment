@extends('layouts.building-admin')

@section('title', 'مستحقاتي الشهرية')
@section('page-title', 'مستحقاتي الشهرية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-calendar-check"></i> مستحقاتي الشهرية</h2>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        @php
            $totalDue = $dues->where('status', '!=', 'paid')->sum('amount');
            $totalPaid = $dues->sum('paid_amount');
            $totalRemaining = $dues->where('status', '!=', 'paid')->sum(function($due) {
                return $due->amount - $due->paid_amount;
            });
            $unpaidCount = $dues->where('status', '!=', 'paid')->count();
        @endphp

        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">المتبقي عليّ</h6>
                            <h3 class="mb-0">{{ number_format($totalRemaining, 2) }} ج.م</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">عدد الشهور غير المدفوعة</h6>
                            <h3 class="mb-0">{{ $unpaidCount }}</h3>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">المدفوع</h6>
                            <h3 class="mb-0">{{ number_format($totalPaid, 2) }} ج.م</h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">إجمالي المطلوب</h6>
                            <h3 class="mb-0">{{ number_format($totalDue, 2) }} ج.م</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dues List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">سجل المستحقات الشهرية</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الفترة</th>
                                    <th>المبلغ المطلوب</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الدفع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dues as $due)
                                    <tr>
                                        <td>
                                            <strong>{{ $due->year }}-{{ str_pad($due->month, 2, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>{{ number_format($due->amount, 2) }} ج.م</td>
                                        <td>{{ number_format($due->paid_amount, 2) }} ج.م</td>
                                        <td>
                                            @if($due->status !== 'paid')
                                                <span class="text-danger fw-bold">
                                                    {{ number_format($due->remaining_amount, 2) }} ج.م
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($due->status === 'paid')
                                                <span class="badge bg-success">مدفوع</span>
                                            @elseif($due->status === 'partial')
                                                <span class="badge bg-warning">مدفوع جزئياً</span>
                                            @else
                                                <span class="badge bg-danger">غير مدفوع</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($due->paid_at)
                                                {{ $due->paid_at->format('Y-m-d') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد مستحقات شهرية مسجلة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($unpaidCount > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>تنبيه:</strong>
                    لديك {{ $unpaidCount }} شهر/شهور غير مدفوعة بإجمالي {{ number_format($totalRemaining, 2) }} ج.م.
                    يرجى التواصل مع إدارة العمارة لتسديد المستحقات.
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
