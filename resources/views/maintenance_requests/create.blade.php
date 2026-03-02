<div class="modal-header bg-primary text-white">
    <h6 class="modal-title fw-bold">
        <i class="fas fa-plus-circle me-2"></i>إضافة طلب صيانة جديد
    </h6>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form id="maintenanceForm" action="{{ route('maintenance.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="apartment_id" class="form-label">رقم الشقة <span class="text-danger">*</span></label>
                <select class="form-select" id="apartment_id" name="apartment_id" required>
                    <option value="">اختر الشقة</option>
                    @foreach($apartments as $apartment)
                        <option value="{{ $apartment->id }}">{{ $apartment->number }} - {{ $apartment->owner_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="priority" class="form-label">الأولوية <span class="text-danger">*</span></label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="low">منخفض</option>
                    <option value="medium" selected>متوسط</option>
                    <option value="high">عالي</option>
                    <option value="urgent">عاجل</option>
                </select>
            </div>

            <div class="col-12 mb-3">
                <label for="title" class="form-label">عنوان الطلب <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" placeholder="مثال: تسريب في الحمام" required>
            </div>

            <div class="col-12 mb-3">
                <label for="description" class="form-label">الوصف</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="وصف تفصيلي للمشكلة..."></textarea>
            </div>

            <div class="col-12 mb-3">
                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                <div class="status-options">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_pending" value="pending" checked>
                        <label class="form-check-label" for="status_pending">
                            <i class="fas fa-clock text-warning"></i> قيد الانتظار
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_in_progress" value="in_progress">
                        <label class="form-check-label" for="status_in_progress">
                            <i class="fas fa-spinner text-info"></i> قيد التنفيذ
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed">
                        <label class="form-check-label" for="status_completed">
                            <i class="fas fa-check-circle text-success"></i> مكتمل
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_cancelled" value="cancelled">
                        <label class="form-check-label" for="status_cancelled">
                            <i class="fas fa-times-circle text-danger"></i> ملغي
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> حفظ
        </button>
    </div>
</form>

<style>
    .status-options {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .form-check-inline {
        margin: 0;
    }

    .form-check-label {
        cursor: pointer;
        padding: 8px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-check-input:checked + .form-check-label {
        background-color: #f8f9fa;
        border-color: #667eea;
        font-weight: 600;
    }
</style>
