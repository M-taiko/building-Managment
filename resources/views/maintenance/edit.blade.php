<div class="modal-header">
    <h5 class="modal-title">تعديل طلب الصيانة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="maintenanceForm" action="{{ route('maintenance.update', $maintenance->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">الشقة *</label>
            <select name="apartment_id" class="form-select" required>
                <option value="">اختر الشقة</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}" {{ $maintenance->apartment_id == $apartment->id ? 'selected' : '' }}>
                        {{ $apartment->number }} - {{ $apartment->owner_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">الوصف *</label>
            <textarea name="description" class="form-control" rows="4" required>{{ $maintenance->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة *</label>
            <select name="status" class="form-select" required>
                <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="in_progress" {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الطلب *</label>
            <input type="date" name="request_date" class="form-control" value="{{ $maintenance->request_date }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الإنجاز</label>
            <input type="date" name="completion_date" class="form-control" value="{{ $maintenance->completion_date }}">
        </div>

        <div class="mb-3">
            <label class="form-label">التكلفة</label>
            <input type="number" name="cost" class="form-control" value="{{ $maintenance->cost }}" step="0.01">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>
