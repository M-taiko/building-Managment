@extends('layouts.resident')
@section('title', 'لوحة التحكم')
@section('page-title', 'الرئيسية')

@push('styles')
<style>
.overview-card {
    border-radius: 14px;
    border: none;
    transition: transform .2s, box-shadow .2s;
    cursor: pointer;
    text-decoration: none !important;
    display: block;
}
.overview-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,.12) !important;
}
.overview-card .icon-circle {
    width: 65px; height: 65px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
}
.overview-card .card-value {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.1;
}
.overview-card .card-label { font-size: .85rem; }
.overview-card .card-arrow {
    font-size: .8rem;
    opacity: .6;
}
.section-card { border-radius: 12px; border: none; }
.maintenance-item { border-right: 4px solid; border-radius: 8px; }
.maintenance-item.urgent  { border-color: #dc3545; }
.maintenance-item.high    { border-color: #fd7e14; }
.maintenance-item.medium  { border-color: #ffc107; }
.maintenance-item.low     { border-color: #28a745; }
</style>
@endpush

@section('content')
@php
    $arabicMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
@endphp
<div class="container-fluid">

    {{-- ===== بطاقات الإحصائيات (قابلة للنقر) ===== --}}
    <div class="row g-3 mb-4">

        {{-- إجمالي المدفوعات → صفحة تفاصيل --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('resident.payments') }}" class="overview-card card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="card-label text-muted mb-1">إجمالي مدفوعاتي</p>
                        <div class="card-value text-success">{{ number_format($stats['total_paid'], 0) }}</div>
                        <small class="text-muted">ج.م</small>
                    </div>
                    <i class="fas fa-chevron-left card-arrow text-success"></i>
                </div>
            </a>
        </div>

        {{-- المتأخرات → صفحة تفاصيل --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('resident.arrears') }}" class="overview-card card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="card-label text-muted mb-1">المتأخرات</p>
                        <div class="card-value text-danger">{{ number_format($stats['arrears'], 0) }}</div>
                        <small class="text-muted">ج.م</small>
                    </div>
                    @if($stats['arrears'] > 0)
                        <i class="fas fa-chevron-left card-arrow text-danger"></i>
                    @endif
                </div>
            </a>
        </div>

        {{-- رصيد العمارة (عرض فقط) --}}
        <div class="col-6 col-md-3">
            <div class="overview-card card shadow-sm h-100" style="cursor: default;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="card-label text-muted mb-1">رصيد العمارة</p>
                        <div class="card-value text-primary">{{ number_format($buildingBalance, 0) }}</div>
                        <small class="text-muted">ج.م</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- طلبات الصيانة --}}
        <div class="col-6 col-md-3">
            <div class="overview-card card shadow-sm h-100" style="cursor: default;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="card-label text-muted mb-1">طلبات الصيانة</p>
                        <div class="card-value text-warning">{{ $myMaintenanceRequests->count() }}</div>
                        <small class="text-muted">طلب</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- رصيدي المدفوع مقدماً --}}
        <div class="col-12 col-md-6">
            <a href="{{ route('resident.account') }}" class="overview-card card shadow-sm h-100"
               style="border-right: 4px solid #11998e !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle text-white" style="background: linear-gradient(135deg,#11998e,#38ef7d);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="card-label text-muted mb-1">رصيدي المدفوع مقدماً</p>
                        <div class="card-value" style="color:#11998e;">{{ number_format($myAccountBalance, 0) }}</div>
                        <small class="text-muted">ج.م</small>
                    </div>
                    <i class="fas fa-chevron-left card-arrow" style="color:#11998e;"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- ===== مصروفات العمارة بفلتر شهري ===== --}}
    <div class="card section-card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-building me-2"></i>
                مصروفات العمارة — {{ $arabicMonths[$month-1] }} {{ $year }}
            </h5>
            <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 align-items-center">
                <select name="year" class="form-select form-select-sm" style="width:90px" onchange="this.form.submit()">
                    @for ($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select name="month" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $arabicMonths[$m-1] }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="card-body">
            @if($buildingExpenses->count() > 0)
                {{-- ملخص سريع --}}
                <div class="row g-3 mb-3">
                    <div class="col-4 text-center">
                        <div class="p-3 bg-info bg-opacity-10 rounded-3">
                            <div class="fw-bold text-info fs-5">{{ $buildingExpenses->where('expense_type','subscription')->count() }}</div>
                            <small class="text-muted">اشتراكات</small>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3">
                            <div class="fw-bold text-warning fs-5">{{ $buildingExpenses->where('expense_type','maintenance')->count() }}</div>
                            <small class="text-muted">صيانة</small>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="p-3 bg-danger bg-opacity-10 rounded-3">
                            <div class="fw-bold text-danger fs-5">{{ number_format($buildingExpenses->sum('amount'), 0) }}</div>
                            <small class="text-muted">إجمالي ج.م</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>التاريخ</th>
                                <th>النوع</th>
                                <th>الوصف</th>
                                <th>المبلغ</th>
                                <th>الرصيد بعده</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buildingExpenses as $bExp)
                                <tr>
                                    <td>{{ $bExp->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if($bExp->expense_type === 'subscription')
                                            <span class="badge bg-info rounded-pill">اشتراك</span>
                                        @elseif($bExp->expense_type === 'maintenance')
                                            <span class="badge bg-warning text-dark rounded-pill">صيانة</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">أخرى</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($bExp->description, 45) }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($bExp->amount, 2) }} ج.م</td>
                                    <td class="text-primary">{{ number_format($bExp->balance_after, 2) }} ج.م</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted mb-0">لا توجد مصروفات لشهر {{ $arabicMonths[$month-1] }} {{ $year }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== طلبات الصيانة ===== --}}
    @if($myMaintenanceRequests->count() > 0)
        <div class="card section-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-warning">
                    <i class="fas fa-tools me-2"></i>طلبات الصيانة الخاصة بي
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($myMaintenanceRequests->take(6) as $mReq)
                        @php
                            $pClass = match($mReq->priority ?? 'medium') {
                                'urgent' => 'urgent',
                                'high'   => 'high',
                                'low'    => 'low',
                                default  => 'medium'
                            };
                            $pLabel = match($mReq->priority ?? 'medium') {
                                'urgent' => ['label' => 'عاجل',     'badge' => 'bg-danger'],
                                'high'   => ['label' => 'عالية',    'badge' => 'bg-warning text-dark'],
                                'low'    => ['label' => 'منخفضة',   'badge' => 'bg-success'],
                                default  => ['label' => 'متوسطة',   'badge' => 'bg-info']
                            };
                            $sLabel = match($mReq->status) {
                                'completed'   => ['label' => 'مكتمل',       'badge' => 'bg-success'],
                                'in_progress' => ['label' => 'قيد التنفيذ', 'badge' => 'bg-primary'],
                                'pending'     => ['label' => 'معلق',        'badge' => 'bg-secondary'],
                                default       => ['label' => 'مفتوح',       'badge' => 'bg-danger']
                            };
                        @endphp
                        <div class="col-md-6">
                            <div class="maintenance-item {{ $pClass }} card shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $mReq->title }}</h6>
                                    <span class="badge {{ $sLabel['badge'] }} rounded-pill ms-2 flex-shrink-0">{{ $sLabel['label'] }}</span>
                                </div>
                                @if($mReq->description)
                                    <p class="text-muted small mb-2">{{ Str::limit($mReq->description, 80) }}</p>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ $pLabel['badge'] }} rounded-pill small">{{ $pLabel['label'] }}</span>
                                    <small class="text-muted">{{ $mReq->created_at->format('Y-m-d') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
