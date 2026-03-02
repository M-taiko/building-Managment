<div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <h5 class="modal-title text-white">
        <i class="fas fa-plus-circle me-2"></i>
        إضافة دفعة اشتراك جديدة
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form id="subscriptionForm" action="{{ route('subscriptions.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <!-- Info Alert -->
        <div class="alert alert-info mb-3" style="background: #e0f2fe; color: #075985; border-radius: 10px; border: none;">
            <i class="fas fa-info-circle me-2"></i>
            <small>قم بتعبئة جميع الحقول المطلوبة لتسجيل دفعة اشتراك جديدة</small>
        </div>

        <div class="row">
            <!-- Apartment Selection -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-building text-primary me-1"></i>
                    الشقة <span class="text-danger">*</span>
                </label>
                <select name="apartment_id" class="form-select modern-select" required>
                    <option value="">اختر الشقة</option>
                    @foreach($apartments as $apartment)
                        <option value="{{ $apartment->id }}">
                            {{ $apartment->number }} - {{ $apartment->owner_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subscription Type -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-tag text-warning me-1"></i>
                    نوع الاشتراك <span class="text-danger">*</span>
                </label>
                <select name="subscription_type_id" id="subscription_type_id" class="form-select modern-select" required>
                    <option value="">اختر نوع الاشتراك</option>
                    @foreach($subscriptionTypes as $type)
                        <option value="{{ $type->id }}" data-amount="{{ $type->amount }}">
                            {{ $type->name }} - {{ number_format($type->amount, 2) }} ج.م
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <!-- Year -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-alt text-success me-1"></i>
                    السنة <span class="text-danger">*</span>
                </label>
                <input
                    type="number"
                    name="year"
                    class="form-control modern-input"
                    value="{{ date('Y') }}"
                    required
                    min="2020"
                    max="2100"
                >
            </div>

            <!-- Month -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-week text-info me-1"></i>
                    الشهر <span class="text-danger">*</span>
                </label>
                <select name="month" class="form-select modern-select" required>
                    @php
                        $currentMonth = date('n');
                        $months = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <!-- Amount Required -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-money-bill-wave text-success me-1"></i>
                    المبلغ المطلوب <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input
                        type="number"
                        name="amount"
                        id="amount"
                        class="form-control modern-input"
                        step="0.01"
                        required
                        placeholder="0.00"
                    >
                    <span class="input-group-text">ج.م</span>
                </div>
            </div>

            <!-- Amount Paid -->
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-hand-holding-usd text-primary me-1"></i>
                    المبلغ المدفوع
                </label>
                <div class="input-group">
                    <input
                        type="number"
                        name="paid_amount"
                        id="paid_amount"
                        class="form-control modern-input"
                        step="0.01"
                        value="0"
                        placeholder="0.00"
                    >
                    <span class="input-group-text">ج.م</span>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-flag text-danger me-1"></i>
                حالة الدفع <span class="text-danger">*</span>
            </label>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="radio" class="btn-check" name="status" id="status_pending" value="pending" checked>
                    <label class="btn btn-outline-warning w-100" for="status_pending">
                        <i class="fas fa-clock me-1"></i>
                        معلق
                    </label>
                </div>
                <div class="col-md-4">
                    <input type="radio" class="btn-check" name="status" id="status_paid" value="paid">
                    <label class="btn btn-outline-success w-100" for="status_paid">
                        <i class="fas fa-check-circle me-1"></i>
                        مدفوع
                    </label>
                </div>
                <div class="col-md-4">
                    <input type="radio" class="btn-check" name="status" id="status_partial" value="partial">
                    <label class="btn btn-outline-info w-100" for="status_partial">
                        <i class="fas fa-circle-notch me-1"></i>
                        مدفوع جزئياً
                    </label>
                </div>
            </div>
        </div>

        <!-- Payment Date (conditional) -->
        <div class="mb-3" id="payment_date_field" style="display: none;">
            <label class="form-label fw-bold">
                <i class="fas fa-calendar-check text-success me-1"></i>
                تاريخ الدفع
            </label>
            <input
                type="date"
                name="paid_at"
                class="form-control modern-input"
                value="{{ date('Y-m-d') }}"
            >
        </div>

        <!-- Notes -->
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-sticky-note text-secondary me-1"></i>
                ملاحظات
            </label>
            <textarea
                name="notes"
                class="form-control modern-input"
                rows="2"
                placeholder="أضف أي ملاحظات إضافية..."
            ></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>
            إلغاء
        </button>
        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
            <i class="fas fa-save me-1"></i>
            حفظ الدفعة
        </button>
    </div>
</form>

<style>
    .modern-select, .modern-input {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 15px;
        transition: all 0.3s;
    }

    .modern-select:focus, .modern-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .btn-check:checked + .btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    .input-group-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
    }
</style>

<script>
$(document).ready(function() {
    // Auto-fill amount when subscription type is selected
    $('#subscription_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const amount = selectedOption.data('amount');
        if (amount) {
            $('#amount').val(amount);
        }
    });

    // Show/hide payment date field based on status
    $('input[name="status"]').change(function() {
        if ($(this).val() === 'paid' || $(this).val() === 'partial') {
            $('#payment_date_field').slideDown();
        } else {
            $('#payment_date_field').slideUp();
        }
    });

    // Auto-fill paid amount when status is paid
    $('#status_paid').change(function() {
        if ($(this).is(':checked')) {
            const amount = $('#amount').val();
            if (amount) {
                $('#paid_amount').val(amount);
            }
        }
    });

    // Form submission
    $('#subscriptionForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success(response.message);
                $('#addPaymentModal').modal('hide');
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error('حدث خطأ أثناء حفظ الدفعة');
                }
            }
        });
    });
});
</script>
