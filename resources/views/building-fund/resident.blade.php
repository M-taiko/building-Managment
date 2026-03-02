@extends('layouts.resident')
@section('title', 'شفافية حساب العمارة')
@section('page-title', 'شفافية حساب العمارة')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-eye"></i> شفافية حساب العمارة</h2>
            <p class="text-muted">عرض تفصيلي لكل إيرادات ومصروفات العمارة</p>
        </div>
    </div>

    <!-- Current Balance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-white mb-2">الرصيد الحالي لحساب العمارة</h4>
                            <h1 class="display-3 mb-0">{{ number_format($currentBalance, 2) }} ج.م</h1>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-wallet fa-5x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">إجمالي الإيرادات</h6>
                            <h2 class="text-success mb-0">{{ number_format($totalIncome, 2) }} ج.م</h2>
                        </div>
                        <i class="fas fa-arrow-down fa-3x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">إجمالي المصروفات</h6>
                            <h2 class="text-danger mb-0">{{ number_format($totalExpense, 2) }} ج.م</h2>
                        </div>
                        <i class="fas fa-arrow-up fa-3x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> تفصيل المصروفات</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-file-invoice fa-2x text-info mb-2"></i>
                                <h6 class="text-muted mb-1">الاشتراكات</h6>
                                <h4 class="text-info mb-0">{{ number_format($subscriptionExpenses, 2) }} ج.م</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                                <h6 class="text-muted mb-1">الصيانة</h6>
                                <h4 class="text-warning mb-0">{{ number_format($maintenanceExpenses, 2) }} ج.م</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-ellipsis-h fa-2x text-secondary mb-2"></i>
                                <h6 class="text-muted mb-1">مصروفات أخرى</h6>
                                <h4 class="text-secondary mb-0">{{ number_format($otherExpenses, 2) }} ج.م</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> آخر المعاملات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>التصنيف</th>
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>الرصيد بعدها</th>
                                    <th>بواسطة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($transaction->transaction_type === 'income')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-arrow-down"></i> إيراد
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-arrow-up"></i> مصروف
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->transaction_type === 'income')
                                                @php
                                                    $types = [
                                                        'monthly_due' => 'مستحق شهري',
                                                        'direct_payment' => 'دفعة مباشرة',
                                                        'subscription_payment' => 'دفع اشتراك',
                                                    ];
                                                @endphp
                                                {{ $types[$transaction->source_type] ?? '-' }}
                                            @else
                                                @php
                                                    $types = [
                                                        'subscription' => 'اشتراك',
                                                        'maintenance' => 'صيانة',
                                                        'other' => 'مصروف آخر',
                                                    ];
                                                @endphp
                                                {{ $types[$transaction->expense_type] ?? '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $transaction->description ?? '-' }}</td>
                                        <td>
                                            @if($transaction->transaction_type === 'income')
                                                <span class="text-success fw-bold">
                                                    +{{ number_format($transaction->amount, 2) }} ج.م
                                                </span>
                                            @else
                                                <span class="text-danger fw-bold">
                                                    -{{ number_format($transaction->amount, 2) }} ج.م
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($transaction->balance_after, 2) }} ج.م</span>
                                        </td>
                                        <td>{{ $transaction->creator->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد معاملات مسجلة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recentTransactions->count() >= 10)
                        <div class="text-center mt-3">
                            <p class="text-muted">عرض آخر 10 معاملات فقط</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>معلومة:</strong>
                هذه الصفحة للعرض فقط. يتم تحديث الرصيد تلقائياً عند دفع المستحقات الشهرية أو عند صرف المصروفات من قبل إدارة العمارة.
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endpush
@endsection
