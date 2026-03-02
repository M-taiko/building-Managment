<form id="subscriptionTypeForm" action="{{ route('subscription-types.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">إضافة نوع اشتراك جديد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label for="name" class="form-label">اسم الاشتراك <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" required>
            <small class="text-muted">مثال: مصعد، حدائق، نظافة، قمامة</small>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ الشهري (ج.م) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>

        <div class="mb-3">
            <label for="is_active" class="form-label">الحالة <span class="text-danger">*</span></label>
            <select class="form-select" id="is_active" name="is_active" required>
                <option value="1">نشط</option>
                <option value="0">غير نشط</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </div>
</form>
