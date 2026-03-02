@extends('layouts.resident')

@section('title', 'رصيدي')
@section('page-title', 'رصيدي')

@push('styles')
<style>
.balance-hero {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border-radius: 16px;
    color: #fff;
    padding: 28px 24px;
}
.balance-hero .amount {
    font-size: 2.4rem;
    font-weight: 700;
    line-height: 1.1;
}
.tx-debit  { color: #dc3545; font-weight: 600; }
.tx-credit { color: #198754; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Balance Hero --}}
    <div class="balance-hero shadow mb-4">
        <p class="mb-1 small" style="opacity:.8;"><i class="fas fa-wallet me-1"></i>رصيد حسابي المدفوع مقدماً</p>
        <div class="amount">
            {{ number_format($balance, 2) }}
            <small class="fs-5 fw-normal">ج.م</small>
        </div>
        <p class="mb-0 mt-2 small" style="opacity:.75;">
            شقة {{ $apartment->number }} — {{ $apartment->owner_name }}
        </p>
    </div>

    {{-- Status alert --}}
    @if($balance > 0)
        <div class="alert border-0 shadow-sm mb-4" style="background:#d1fae5; color:#065f46;">
            <i class="fas fa-check-circle me-2"></i>
            لديك رصيد كافٍ. سيتم خصم المستحقات الجديدة منه تلقائياً.
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            رصيدك صفر. تواصل مع إدارة العمارة لإيداع رصيد مقدم.
        </div>
    @endif

    {{-- Transaction History --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-history me-2 text-primary"></i>سجل المعاملات
            </h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>الوصف</th>
                            <th class="text-end">المبلغ</th>
                            <th class="text-end">الرصيد بعده</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="text-nowrap">{{ $tx->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if($tx->transaction_type === 'credit')
                                        <span class="badge bg-success rounded-pill">إيداع</span>
                                    @else
                                        @php
                                            $labels = [
                                                'auto_expense_payment'      => 'سداد مصروف',
                                                'auto_subscription_payment' => 'سداد اشتراك',
                                            ];
                                        @endphp
                                        <span class="badge bg-warning text-dark rounded-pill">
                                            {{ $labels[$tx->source_type] ?? 'خصم' }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $tx->description ?? '-' }}</td>
                                <td class="text-end {{ $tx->transaction_type === 'credit' ? 'tx-credit' : 'tx-debit' }}">
                                    {{ $tx->transaction_type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="text-end fw-bold">{{ number_format($tx->balance_after, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-wallet fa-2x mb-2 d-block opacity-25"></i>
                                    لا توجد معاملات بعد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
