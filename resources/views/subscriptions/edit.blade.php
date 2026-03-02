<div class="modal-header">
    <h5 class="modal-title">تعديل الاشتراك</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="subscriptionForm" action="{{ route('subscriptions.update', $subscription->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">الشقة *</label>
            <select name="apartment_id" class="form-select" required>
                <option value="">اختر الشقة</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}" {{ $subscription->apartment_id == $apartment->id ? 'selected' : '' }}>
                        {{ $apartment->number }} - {{ $apartment->owner_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">نوع الاشتراك *</label>
            <select name="subscription_type_id" class="form-select" required>
                <option value="">اختر نوع الاشتراك</option>
                @foreach($subscriptionTypes as $type)
                    <option value="{{ $type->id }}" {{ $subscription->subscription_type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }} - {{ number_format($type->amount, 2) }} ج.م
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">السنة *</label>
            <input type="number" name="year" class="form-control" value="{{ $subscription->year }}" required min="2020" max="2100">
        </div>

        <div class="mb-3">
            <label class="form-label">الشهر *</label>
            <select name="month" class="form-select" required>
                <option value="1" {{ $subscription->month == 1 ? 'selected' : '' }}>يناير</option>
                <option value="2" {{ $subscription->month == 2 ? 'selected' : '' }}>فبراير</option>
                <option value="3" {{ $subscription->month == 3 ? 'selected' : '' }}>مارس</option>
                <option value="4" {{ $subscription->month == 4 ? 'selected' : '' }}>أبريل</option>
                <option value="5" {{ $subscription->month == 5 ? 'selected' : '' }}>مايو</option>
                <option value="6" {{ $subscription->month == 6 ? 'selected' : '' }}>يونيو</option>
                <option value="7" {{ $subscription->month == 7 ? 'selected' : '' }}>يوليو</option>
                <option value="8" {{ $subscription->month == 8 ? 'selected' : '' }}>أغسطس</option>
                <option value="9" {{ $subscription->month == 9 ? 'selected' : '' }}>سبتمبر</option>
                <option value="10" {{ $subscription->month == 10 ? 'selected' : '' }}>أكتوبر</option>
                <option value="11" {{ $subscription->month == 11 ? 'selected' : '' }}>نوفمبر</option>
                <option value="12" {{ $subscription->month == 12 ? 'selected' : '' }}>ديسمبر</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">المبلغ المطلوب *</label>
            <input type="number" name="amount" class="form-control" value="{{ $subscription->amount }}" step="0.01" required>
        </div>

        <div class="mb-3">
            <label class="form-label">المبلغ المدفوع</label>
            <input type="number" name="paid_amount" class="form-control" value="{{ $subscription->paid_amount }}" step="0.01">
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة *</label>
            <select name="status" class="form-select" required>
                <option value="pending" {{ $subscription->status == 'pending' ? 'selected' : '' }}>معلق</option>
                <option value="paid" {{ $subscription->status == 'paid' ? 'selected' : '' }}>مدفوع</option>
                <option value="partial" {{ $subscription->status == 'partial' ? 'selected' : '' }}>مدفوع جزئياً</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>
