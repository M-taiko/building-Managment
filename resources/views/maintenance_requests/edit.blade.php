<div class="modal-header bg-warning text-dark">
    <h6 class="modal-title fw-bold">
        <i class="fas fa-edit me-2"></i>تعديل طلب الصيانة
    </h6>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="maintenanceForm" action="{{ route('maintenance.update', $maintenanceRequest->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="apartment_id" class="form-label">رقم الشقة <span class="text-danger">*</span></label>
                <select class="form-select" id="apartment_id" name="apartment_id" required>
                    <option value="">اختر الشقة</option>
                    @foreach($apartments as $apartment)
                        <option value="{{ $apartment->id }}" {{ $maintenanceRequest->apartment_id == $apartment->id ? 'selected' : '' }}>
                            {{ $apartment->number }} - {{ $apartment->owner_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="priority" class="form-label">الأولوية <span class="text-danger">*</span></label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="low" {{ $maintenanceRequest->priority == 'low' ? 'selected' : '' }}>منخفض</option>
                    <option value="medium" {{ $maintenanceRequest->priority == 'medium' ? 'selected' : '' }}>متوسط</option>
                    <option value="high" {{ $maintenanceRequest->priority == 'high' ? 'selected' : '' }}>عالي</option>
                    <option value="urgent" {{ $maintenanceRequest->priority == 'urgent' ? 'selected' : '' }}>عاجل</option>
                </select>
            </div>

            <div class="col-12 mb-3">
                <label for="title" class="form-label">عنوان الطلب <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $maintenanceRequest->title }}" required>
            </div>

            <div class="col-12 mb-3">
                <label for="description" class="form-label">الوصف</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ $maintenanceRequest->description }}</textarea>
            </div>

            <div class="col-12 mb-3">
                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                <div class="status-options">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_pending" value="pending" {{ $maintenanceRequest->status == 'pending' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_pending">
                            <i class="fas fa-clock text-warning"></i> قيد الانتظار
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_in_progress" value="in_progress" {{ $maintenanceRequest->status == 'in_progress' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_in_progress">
                            <i class="fas fa-spinner text-info"></i> قيد التنفيذ
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed" {{ $maintenanceRequest->status == 'completed' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_completed">
                            <i class="fas fa-check-circle text-success"></i> مكتمل
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_cancelled" value="cancelled" {{ $maintenanceRequest->status == 'cancelled' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_cancelled">
                            <i class="fas fa-times-circle text-danger"></i> ملغي
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <i class="fas fa-user"></i>
                            <strong>منشئ الطلب:</strong> {{ $maintenanceRequest->creator->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <i class="fas fa-calendar"></i>
                            <strong>تاريخ الإنشاء:</strong> {{ $maintenanceRequest->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> تحديث
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
