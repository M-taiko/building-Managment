@extends('layouts.building-admin')
@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@push('styles')
<style>
/* ===== Stat Cards ===== */
.stat-card {
    border-radius: 14px;
    border: none;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    text-decoration: none !important;
    display: block;
    position: relative;
    cursor: pointer;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,.12) !important;
}
.stat-card .card-top-bar {
    height: 4px;
    width: 100%;
}
.stat-card .card-body { padding: 16px; }
.stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-card .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.1;
}
.stat-card .stat-label { font-size: 12px; color: #6c757d; }
.stat-card .stat-arrow { font-size: 11px; opacity: .6; }

/* ===== Quick Actions ===== */
.quick-action-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 12px;
    color: #fff !important;
    text-decoration: none !important;
    font-size: 12px;
    transition: background .2s, transform .2s;
}
.quick-action-link:hover {
    background: rgba(255,255,255,0.28);
    transform: translateY(-2px);
}
.quick-action-link i { font-size: 22px; }

/* ===== Chart Card ===== */
.chart-card { border-radius: 14px; border: none; }

/* ===== Arrears Table ===== */
.arrears-table-card { border-radius: 14px; overflow: hidden; border: none; }

/* Mobile: hide less important columns */
@media (max-width: 575px) {
    .col-hide-sm { display: none; }
}
@media (max-width: 767px) {
    .col-hide-md { display: none; }
}

/* ===== Activity Items ===== */
.activity-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #e9ecef;
    margin-bottom: 10px;
    transition: border-color .2s;
}
.activity-item:hover { border-color: #667eea; background: #fff; }
.activity-item-title { font-size: 13px; font-weight: 600; color: #1e293b; }
.activity-item-sub { font-size: 11px; color: #64748b; }
.activity-item-value { font-size: 14px; font-weight: 700; color: #10b981; }

/* Skeleton */
.skeleton { background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%); background-size:200% 100%; animation:skel 1.5s infinite; border-radius:6px; }
@keyframes skel { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    {{-- ===== أهلاً بك ===== --}}
    <div class="rounded-4 text-white mb-3 p-3"
         style="background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); box-shadow:0 4px 15px rgba(102,126,234,.3);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                 style="width:50px;height:50px;font-size:22px;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">مرحباً، {{ auth()->user()->name }}</h5>
                <small class="opacity-75">نظرة عامة على أداء عمارتك</small>
            </div>
        </div>
    </div>

    {{-- ===== بطاقات الإحصائيات (2×2) ===== --}}
    <div class="row g-2 mb-3">

        {{-- إجمالي الإيرادات --}}
        <div class="col-6">
            <a href="{{ route('subscriptions.payments') }}" class="stat-card card shadow-sm h-100">
                <div class="card-top-bar bg-success"></div>
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="stat-label">الإيرادات</div>
                        <div class="stat-value text-success" id="stat-income">
                            <span class="skeleton d-block" style="height:28px;width:90px;"></span>
                        </div>
                        <small class="text-muted" style="font-size:10px;">ج.م</small>
                    </div>
                    <i class="fas fa-chevron-left stat-arrow text-success"></i>
                </div>
            </a>
        </div>

        {{-- إجمالي المصروفات --}}
        <div class="col-6">
            <a href="{{ route('expenses.index') }}" class="stat-card card shadow-sm h-100">
                <div class="card-top-bar bg-danger"></div>
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-chart-line-down"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="stat-label">المصروفات</div>
                        <div class="stat-value text-danger" id="stat-expenses">
                            <span class="skeleton d-block" style="height:28px;width:90px;"></span>
                        </div>
                        <small class="text-muted" style="font-size:10px;">ج.م</small>
                    </div>
                    <i class="fas fa-chevron-left stat-arrow text-danger"></i>
                </div>
            </a>
        </div>

        {{-- الرصيد الحالي --}}
        <div class="col-6">
            <a href="{{ route('building-fund.index') }}" class="stat-card card shadow-sm h-100">
                <div class="card-top-bar bg-primary"></div>
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="stat-label">الرصيد</div>
                        <div class="stat-value text-primary" id="stat-balance">
                            <span class="skeleton d-block" style="height:28px;width:90px;"></span>
                        </div>
                        <small class="text-muted" style="font-size:10px;">ج.م</small>
                    </div>
                    <i class="fas fa-chevron-left stat-arrow text-primary"></i>
                </div>
            </a>
        </div>

        {{-- طلبات الصيانة --}}
        <div class="col-6">
            <a href="{{ route('maintenance.index') }}" class="stat-card card shadow-sm h-100">
                <div class="card-top-bar bg-warning"></div>
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-screwdriver-wrench"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="stat-label">الصيانة</div>
                        <div class="stat-value text-warning" id="stat-maintenance">
                            <span class="skeleton d-block" style="height:28px;width:60px;"></span>
                        </div>
                        <small class="text-muted" style="font-size:10px;">طلب مفتوح</small>
                    </div>
                    <i class="fas fa-chevron-left stat-arrow text-warning"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- ===== إجراءات سريعة ===== --}}
    <div class="rounded-4 p-3 mb-3"
         style="background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);">
        <p class="text-white fw-bold mb-2 small"><i class="fas fa-bolt me-1"></i> إجراءات سريعة</p>
        <div class="row g-2">
            <div class="col-3">
                <a href="{{ route('subscriptions.payments') }}" class="quick-action-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>تسجيل دفعة</span>
                </a>
            </div>
            <div class="col-3">
                <a href="{{ route('expenses.create') }}" class="quick-action-link">
                    <i class="fas fa-receipt"></i>
                    <span>مصروف جديد</span>
                </a>
            </div>
            <div class="col-3">
                <a href="{{ route('maintenance.create') }}" class="quick-action-link">
                    <i class="fas fa-wrench"></i>
                    <span>طلب صيانة</span>
                </a>
            </div>
            <div class="col-3">
                <a href="{{ route('monthly-dues.index') }}" class="quick-action-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>المستحقات</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ===== الرسم البياني ===== --}}
    <div class="card chart-card shadow-sm mb-3">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <div>
                <h6 class="mb-0 fw-bold text-primary">الإيرادات مقابل المصروفات</h6>
                <small class="text-muted">تحليل شهري</small>
            </div>
            <select id="year-filter" class="form-select form-select-sm" style="width:90px;">
                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
            </select>
        </div>
        <div class="card-body p-2">
            <canvas id="incomeExpensesChart" style="max-height:260px;"></canvas>
        </div>
    </div>

    {{-- ===== جدول المتأخرين ===== --}}
    @if(count($residentsWithArrears) > 0)
    <div class="card arrears-table-card shadow-sm mb-3">
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-users-slash me-2"></i>
                    السكان المتأخرين
                    <span class="badge bg-white text-danger ms-1">{{ count($residentsWithArrears) }}</span>
                </h6>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="arrears-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">الشقة</th>
                            <th class="col-hide-sm">الطابق</th>
                            <th>المقيم</th>
                            <th class="col-hide-md">الهاتف</th>
                            <th>المتأخرات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residentsWithArrears as $resident)
                        <tr>
                            <td class="ps-3">
                                <span class="badge rounded-pill fw-bold"
                                      style="background:linear-gradient(135deg,#667eea,#764ba2);font-size:12px;">
                                    {{ $resident['apartment_number'] }}
                                </span>
                            </td>
                            <td class="col-hide-sm text-muted small">{{ $resident['floor'] }}</td>
                            <td>
                                <div class="fw-semibold small">{{ $resident['resident_name'] ?: $resident['owner_name'] }}</div>
                                <small class="text-muted col-hide-md">{{ $resident['resident_email'] }}</small>
                            </td>
                            <td class="col-hide-md text-muted small">{{ $resident['resident_phone'] }}</td>
                            <td>
                                <span class="fw-bold text-danger">{{ number_format($resident['total_arrears'], 0) }} ج.م</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <th colspan="4" class="text-end pe-3">إجمالي المتأخرات:</th>
                            <th class="text-danger fw-bold">
                                {{ number_format(collect($residentsWithArrears)->sum('total_arrears'), 0) }} ج.م
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== آخر النشاطات ===== --}}
    <div class="row g-3 mb-2">
        {{-- آخر المدفوعات --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success"
                         style="width:36px;height:36px;">
                        <i class="fas fa-money-bill-trend-up"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">آخر المدفوعات</h6>
                        <small class="text-muted">أحدث 5 عمليات دفع</small>
                    </div>
                </div>
                <div class="card-body p-3" id="recent-payments-list">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin me-1"></i> جاري التحميل...
                    </div>
                </div>
            </div>
        </div>

        {{-- آخر الصيانة --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning"
                         style="width:36px;height:36px;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">طلبات الصيانة الأخيرة</h6>
                        <small class="text-muted">أحدث 5 طلبات</small>
                    </div>
                </div>
                <div class="card-body p-3" id="recent-maintenance-list">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin me-1"></i> جاري التحميل...
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load stats
    $.get('/api/dashboard/stats', function(r) {
        $('#stat-income').text(r.total_income + ' ج.م');
        $('#stat-expenses').text(r.total_expenses + ' ج.م');
        $('#stat-balance').text(r.balance + ' ج.م');
        $('#stat-maintenance').text(r.open_maintenance);
    }).fail(function() {
        ['stat-income','stat-expenses','stat-balance','stat-maintenance']
            .forEach(id => $('#'+id).text('-'));
    });

    // Load recent payments
    $.get('/api/dashboard/recent-payments', function(res) {
        let html = '';
        if (!res.length) {
            html = '<div class="text-center text-muted py-3 small">لا توجد مدفوعات حديثة</div>';
        } else {
            res.forEach(p => {
                html += `<div class="activity-item">
                    <div>
                        <div class="activity-item-title">شقة ${p.apartment_number}</div>
                        <div class="activity-item-sub">${p.month} — ${p.date}</div>
                    </div>
                    <div class="activity-item-value">${p.amount} ج.م</div>
                </div>`;
            });
        }
        $('#recent-payments-list').html(html);
    });

    // Load recent maintenance
    $.get('/api/dashboard/recent-maintenance', function(res) {
        const pMap = {'عالية':'danger','متوسطة':'warning','منخفضة':'info','عاجلة':'danger'};
        const sMap = {'مكتمل':'success','قيد التنفيذ':'primary','معلق':'secondary'};
        let html = '';
        if (!res.length) {
            html = '<div class="text-center text-muted py-3 small">لا توجد طلبات حديثة</div>';
        } else {
            res.forEach(r => {
                const pc = pMap[r.priority] || 'secondary';
                const sc = sMap[r.status] || 'secondary';
                html += `<div class="activity-item">
                    <div style="flex:1;min-width:0;">
                        <div class="activity-item-title text-truncate">${r.title}</div>
                        <div class="activity-item-sub">شقة ${r.apartment_number}</div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0 ms-2">
                        <span class="badge bg-${pc} rounded-pill" style="font-size:10px;">${r.priority}</span>
                        <span class="badge bg-${sc} rounded-pill" style="font-size:10px;">${r.status}</span>
                    </div>
                </div>`;
            });
        }
        $('#recent-maintenance-list').html(html);
    });

    // Chart
    const ctx = document.getElementById('incomeExpensesChart');
    let chart = null;
    const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

    function loadChart(year) {
        $.get('/api/dashboard/chart-data', { year }, function(r) {
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'الإيرادات',
                            data: r.income,
                            backgroundColor: 'rgba(16,185,129,0.7)',
                            borderRadius: 6
                        },
                        {
                            label: 'المصروفات',
                            data: r.expenses,
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { family: 'Cairo', size: 12 }, usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString() + ' ج.م'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: v => v.toLocaleString(),
                                font: { family: 'Cairo', size: 11 }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Cairo', size: 11 } }
                        }
                    }
                }
            });
        });
    }

    loadChart($('#year-filter').val());
    $('#year-filter').change(function() { loadChart($(this).val()); });
});
</script>
@endpush
