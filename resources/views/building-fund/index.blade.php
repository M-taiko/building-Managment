@extends('layouts.building-admin')

@section('title', 'حساب العمارة')
@section('page-title', 'حساب العمارة')

@push('styles')
<style>
    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .balance-card.cash-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .balance-card.bank-card {
        background: linear-gradient(135deg, #00a8e8 0%, #0077be 100%);
    }

    .balance-card.total-card {
        background: linear-gradient(135deg, #06a77d 0%, #055a4a 100%);
    }

    .balance-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .balance-card .card-body {
        position: relative;
        z-index: 1;
    }

    .balance-value {
        font-size: 28px;
        font-weight: 700;
        margin: 10px 0;
    }

    .balance-label {
        font-size: 13px;
        opacity: 0.9;
    }

    .balance-icon {
        font-size: 40px;
        opacity: 0.3;
        text-align: right;
    }

    .transfer-btn {
        width: 100%;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        margin-top: 12px;
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 6px;
        transition: all .2s;
    }

    .transfer-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .custodies-section {
        background: #fffbea;
        border-right: 4px solid #ffc107;
        padding: 16px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .custody-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: white;
        border-radius: 6px;
        margin-bottom: 8px;
        border-right: 3px solid #ffc107;
    }

    .custody-amount {
        color: #856404;
        font-weight: 700;
        font-size: 16px;
    }

    .custody-label {
        color: #856404;
        font-size: 12px;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-piggy-bank"></i> حساب العمارة</h2>
        </div>
    </div>

    <!-- Balance Cards Grid -->
    <div class="row mb-4 g-3">
        <!-- Cash Balance -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card balance-card cash-card">
                <div class="card-body">
                    <div class="balance-label">💵 النقد</div>
                    <div class="balance-value" id="cashBalance">0.00</div>
                    <small class="text-white-50">ج.م</small>
                    <button type="button" class="transfer-btn" onclick="openTransferModal('cash')">
                        <i class="fas fa-arrow-left"></i> ترحيل
                    </button>
                </div>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card balance-card bank-card">
                <div class="card-body">
                    <div class="balance-label">🏦 البنك</div>
                    <div class="balance-value" id="bankBalance">0.00</div>
                    <small class="text-white-50">ج.م</small>
                    <button type="button" class="transfer-btn" onclick="openTransferModal('bank')">
                        <i class="fas fa-arrow-left"></i> ترحيل
                    </button>
                </div>
            </div>
        </div>

        <!-- Total Balance -->
        <div class="col-12 col-md-12 col-lg-4">
            <div class="card balance-card total-card">
                <div class="card-body">
                    <div class="balance-label">💰 الإجمالي</div>
                    <div class="balance-value">{{ number_format($currentBalance, 2) }}</div>
                    <small class="text-white-50">ج.م</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Custodies Section -->
    <div id="custodiesSection" style="display: none;">
        <div class="custodies-section">
            <h5 class="mb-3"><i class="fas fa-shield-alt me-2"></i> العهدات الحالية</h5>
            <div id="custodiesList"></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">نوع المعاملة</label>
                            <select name="transaction_type" id="filterTransactionType" class="form-select">
                                <option value="">الكل</option>
                                <option value="income">إيرادات</option>
                                <option value="expense">مصروفات</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نوع المصروف</label>
                            <select name="expense_type" id="filterExpenseType" class="form-select">
                                <option value="">الكل</option>
                                <option value="subscription">اشتراكات</option>
                                <option value="maintenance">صيانة</option>
                                <option value="other">مصروفات أخرى</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> تطبيق الفلاتر
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">سجل المعاملات المالية</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transactionsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>المحفظة</th>
                                    <th>التصنيف</th>
                                    <th>المبلغ</th>
                                    <th>الرصيد</th>
                                    <th>بواسطة</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i> ترحيل مبلغ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" id="transferAmount" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">من <span class="text-danger">*</span></label>
                            <select name="from_wallet" id="fromWallet" class="form-select" required>
                                <option value="">-- اختر --</option>
                                <option value="cash">النقد</option>
                                <option value="bank">البنك</option>
                                <option value="general">عام</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">إلى <span class="text-danger">*</span></label>
                            <select name="to_wallet" id="toWallet" class="form-select" required>
                                <option value="">-- اختر --</option>
                                <option value="cash">النقد</option>
                                <option value="bank">البنك</option>
                                <option value="general">عام</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="custodyCheck" onchange="toggleCustodyFields()">
                        <label class="form-check-label" for="custodyCheck">
                            عهدة عند ساكن
                        </label>
                    </div>
                    <div id="custodyFields" style="display: none; margin-top: 15px;">
                        <div class="mb-3">
                            <label class="form-label">الساكن</label>
                            <select name="custody_user_id" id="custodyUser" class="form-select">
                                <option value="">-- اختر الساكن --</option>
                                @forelse(auth()->user()->tenant->users->where('role', 'building_admin') as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="custody_notes" id="custodyNotes" class="form-control" rows="2" placeholder="مثال: لدفع الفواتير"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> تأكيد</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));

function openTransferModal(fromWallet = null) {
    document.getElementById('transferForm').reset();
    if (fromWallet) {
        document.getElementById('fromWallet').value = fromWallet;
    }
    transferModal.show();
}

function toggleCustodyFields() {
    const custodyFields = document.getElementById('custodyFields');
    custodyFields.style.display = document.getElementById('custodyCheck').checked ? 'block' : 'none';
}

document.getElementById('transferForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {
        amount: formData.get('amount'),
        from_wallet: formData.get('from_wallet'),
        to_wallet: formData.get('to_wallet'),
        custody_user_id: formData.get('custody_user_id') || null,
        custody_notes: formData.get('custody_notes') || null,
    };

    $.ajax({
        url: '{{ route("building-fund.transfer") }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            toastr.success(response.message);
            transferModal.hide();
            location.reload();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                toastr.error('بيانات غير صحيحة');
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                toastr.error(xhr.responseJSON.error);
            } else {
                toastr.error('حدث خطأ');
            }
        }
    });
});

$(document).ready(function() {
    // DataTable
    const table = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("building-fund.index") }}',
            data: function(d) {
                d.transaction_type = $('#filterTransactionType').val();
                d.expense_type = $('#filterExpenseType').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'type_badge', name: 'type_badge' },
            { data: 'wallet_type', name: 'wallet_type' },
            { data: 'category', name: 'category' },
            { data: 'amount_formatted', name: 'amount_formatted' },
            { data: 'balance_formatted', name: 'balance_formatted' },
            { data: 'created_by_name', name: 'created_by_name' }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        }
    });

    // Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Update balance cards
    function updateBalances() {
        // In real scenario, fetch via AJAX
        // For now, just show placeholder
        document.getElementById('cashBalance').textContent = '0.00';
        document.getElementById('bankBalance').textContent = '0.00';
    }

    updateBalances();
});
</script>
@endpush
@endsection
