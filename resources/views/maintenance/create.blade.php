<div class="modal-header">
    <h5 class="modal-title">إضافة طلب صيانة جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="maintenanceForm" action="{{ route('maintenance.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">الشقة *</label>
            <select name="apartment_id" class="form-select" required>
                <option value="">اختر الشقة</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}">{{ $apartment->number }} - {{ $apartment->owner_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">الوصف *</label>
            <textarea name="description" class="form-control" rows="4" required placeholder="وصف تفصيلي لمشكلة الصيانة"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة *</label>
            <select name="status" class="form-select" required>
                <option value="pending">قيد الانتظار</option>
                <option value="in_progress">قيد التنفيذ</option>
                <option value="completed">مكتمل</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الطلب *</label>
            <input type="date" name="request_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الإنجاز</label>
            <input type="date" name="completion_date" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">التكلفة</label>
            <input type="number" name="cost" class="form-control" step="0.01" placeholder="0.00">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </div>
</form>
