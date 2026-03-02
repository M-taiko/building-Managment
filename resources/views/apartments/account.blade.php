@extends('layouts.building-admin')

@section('title', 'حساب الشقة ' . $apartment->number)
@section('page-title', 'حساب الشقة')

@push('styles')
<style>
.balance-hero {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border-radius: 16px;
    color: #fff;
    padding: 28px 24px;
}
.balance-hero .amount {
    font-size: 2.6rem;
    font-weight: 700;
    line-height: 1.1;
}
.tx-debit  { color: #dc3545; font-weight: 600; }
.tx-credit { color: #198754; font-weight: 600; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('apartments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div>
            <span class="fw-bold">شقة {{ $apartment->number }}</span>
            <span class="text-muted small ms-1">— {{ $apartment->owner_name }}</span>
        </div>
    </div>
    <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#depositModal">
        <i class="fas fa-plus me-1"></i>إيداع رصيد
    </button>
</div>

{{-- Balance Card --}}
<div class="balance-hero shadow mb-4">
    <p class="mb-1 small" style="opacity:.8;">رصيد الحساب الحالي</p>
    <div class="amount">
        {{ number_format($balance, 2) }}
        <small class="fs-5 fw-normal">ج.م</small>
    </div>
    @if($apartment->resident)
        <p class="mb-0 mt-2 small" style="opacity:.75;">
            <i class="fas fa-user me-1"></i>{{ $apartment->resident->name }}
        </p>
    @endif
</div>

{{-- Transactions Table --}}
<div class="card border-0 shadow-sm">
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
                        <th>بواسطة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="text-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
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
                            <td>{{ $tx->creator->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
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

{{-- Deposit Modal --}}
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-wallet me-2"></i>إيداع رصيد — شقة {{ $apartment->number }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">المبلغ (ج.م) <span class="text-danger">*</span></label>
                    <input type="number" id="depositAmount" class="form-control form-control-lg"
                           min="0.01" step="0.01" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ملاحظات</label>
                    <textarea id="depositNotes" class="form-control" rows="3"
                              placeholder="مثال: دفعة مقدمة 3 أشهر"></textarea>
                </div>
                <div class="alert alert-info mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    سيتم خصم المستحقات المعلقة تلقائياً من الرصيد المُودع.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success px-4" id="confirmDeposit">
                    <i class="fas fa-check me-1"></i>تأكيد الإيداع
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#confirmDeposit').on('click', function () {
    const amount = parseFloat($('#depositAmount').val());
    const notes  = $('#depositNotes').val();

    if (!amount || amount <= 0) {
        toastr.error('يرجى إدخال مبلغ صحيح');
        return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>جاري المعالجة...');

    $.ajax({
        url:    '{{ route('apartments.account.deposit', $apartment->id) }}',
        method: 'POST',
        data:   {
            amount: amount,
            notes:  notes,
            _token: '{{ csrf_token() }}'
        },
        success: function (r) {
            toastr.success(r.message);
            setTimeout(() => location.reload(), 1500);
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message ?? 'حدث خطأ أثناء الإيداع';
            toastr.error(msg);
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>تأكيد الإيداع');
        }
    });
});
</script>
@endpush
