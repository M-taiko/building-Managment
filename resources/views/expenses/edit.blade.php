<div class="modal-header bg-warning text-dark">
    <h6 class="modal-title fw-bold">
        <i class="fas fa-edit me-2"></i>تعديل المصروف
    </h6>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="expenseForm" action="{{ route('expenses.update', $expense->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">العنوان <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ $expense->title }}" required placeholder="عنوان المصروف">
        </div>

        <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="3" placeholder="وصف تفصيلي للمصروف">{{ $expense->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">المبلغ (ج.م) <span class="text-danger">*</span></label>
            <input type="number" name="amount" class="form-control" value="{{ $expense->amount }}" step="0.01" required placeholder="0.00">
        </div>

        <div class="mb-3">
            <label class="form-label">التاريخ <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control" value="{{ $expense->date ? $expense->date->format('Y-m-d') : '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">نوع التوزيع <span class="text-danger">*</span></label>
            <select name="distribution_type" id="distribution_type" class="form-select" required>
                <option value="all" {{ $expense->distribution_type == 'all' ? 'selected' : '' }}>توزيع على جميع السكان بالتساوي</option>
                <option value="specific" {{ $expense->distribution_type == 'specific' ? 'selected' : '' }}>شقة محددة</option>
            </select>
        </div>

        <div class="mb-3" id="apartment_field" style="display: {{ $expense->distribution_type == 'specific' ? 'block' : 'none' }};">
            <label class="form-label">الشقة <span class="text-danger">*</span></label>
            <select name="apartment_id" class="form-select">
                <option value="">اختر الشقة</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}" {{ $expense->apartment_id == $apartment->id ? 'selected' : '' }}>
                        {{ $apartment->number }} - {{ $apartment->resident ? $apartment->resident->name : 'بدون ساكن' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">نوع التكرار <span class="text-danger">*</span></label>
            <select name="recurrence_type" id="recurrence_type" class="form-select" required>
                <option value="one_time" {{ $expense->recurrence_type == 'one_time' ? 'selected' : '' }}>مصروف لمرة واحدة</option>
                <option value="monthly" {{ $expense->recurrence_type == 'monthly' ? 'selected' : '' }}>مصروف شهري متكرر</option>
                <option value="yearly" {{ $expense->recurrence_type == 'yearly' ? 'selected' : '' }}>مصروف سنوي متكرر</option>
            </select>
        </div>

        <div class="mb-3" id="subscription_type_field" style="display: {{ $expense->is_recurring ? 'block' : 'none' }};">
            <label class="form-label">نوع الاشتراك (اختياري)</label>
            <select name="subscription_type_id" id="subscription_type_id" class="form-select">
                <option value="">-- اختر اشتراك موجود أو اترك فارغاً --</option>
                @foreach($subscriptionTypes as $type)
                    <option value="{{ $type->id }}" data-amount="{{ $type->amount }}" {{ $expense->subscription_type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }} ({{ number_format($type->amount, 2) }} ج.م)
                    </option>
                @endforeach
            </select>
            <small class="text-muted">إذا اخترت اشتراك موجود، سيتم استخدام مبلغه تلقائياً</small>
        </div>

        <div class="mb-3" id="next_occurrence_field" style="display: {{ $expense->is_recurring ? 'block' : 'none' }};">
            <label class="form-label">تاريخ التكرار التالي</label>
            <input type="date" name="next_occurrence_date" class="form-control" value="{{ $expense->next_occurrence_date ? $expense->next_occurrence_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 month')) }}">
            <small class="text-muted">متى يجب تطبيق هذا المصروف مرة أخرى؟</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#distribution_type').change(function() {
        if ($(this).val() === 'specific') {
            $('#apartment_field').show();
            $('select[name="apartment_id"]').attr('required', true);
        } else {
            $('#apartment_field').hide();
            $('select[name="apartment_id"]').attr('required', false);
        }
    });

    $('#recurrence_type').change(function() {
        const value = $(this).val();

        if (value === 'monthly' || value === 'yearly') {
            $('#subscription_type_field').show();
            $('#next_occurrence_field').show();
        } else {
            $('#subscription_type_field').hide();
            $('#next_occurrence_field').hide();
        }
    });

    // عند اختيار subscription type، املأ المبلغ تلقائياً
    $('#subscription_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const amount = selectedOption.data('amount');

        if (amount) {
            $('input[name="amount"]').val(amount);
        }
    });
});
</script>
