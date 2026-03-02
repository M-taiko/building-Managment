<div class="modal-header">
    <h5 class="modal-title"><i class="fas fa-edit"></i> تعديل العمارة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="tenantForm" action="{{ route('tenants.update', $tenant->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tenant_code" class="form-label">رقم العمارة <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tenant_code" name="tenant_code" value="{{ $tenant->tenant_code }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">اسم العمارة <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $tenant->name }}" required>
            </div>

            <div class="col-12 mb-3">
                <label for="address" class="form-label">العنوان</label>
                <textarea class="form-control" id="address" name="address" rows="2">{{ $tenant->address }}</textarea>
            </div>

            <div class="col-md-4 mb-3">
                <label for="subscription_price" class="form-label">سعر الاشتراك الشهري <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-control" id="subscription_price" name="subscription_price" step="0.01" min="0" value="{{ $tenant->subscription_price }}" required>
                    <span class="input-group-text">ج.م</span>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label for="units_count" class="form-label">
                    <i class="fas fa-home text-primary"></i>
                    عدد الوحدات المسموح بها <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="units_count" name="units_count" min="1" value="{{ $tenant->units_count }}" required>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    الحد الأقصى للشقق: <strong>{{ $tenant->apartments()->count() }}/{{ $tenant->units_count }}</strong>
                </small>
            </div>

            <div class="col-md-4 mb-3">
                <label for="subscription_expires_at" class="form-label">تاريخ انتهاء الاشتراك</label>
                <input type="date" class="form-control" id="subscription_expires_at" name="subscription_expires_at" value="{{ $tenant->subscription_expires_at ? $tenant->subscription_expires_at->format('Y-m-d') : '' }}">
            </div>

            <div class="col-12 mb-3">
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fas fa-building"></i>
                            <strong>الشقق المسجلة:</strong> {{ $tenant->apartments()->count() }}
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>الشقق النشطة:</strong> {{ $tenant->apartments()->where('is_active', true)->count() }}
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-users"></i>
                            <strong>المستخدمين:</strong> {{ $tenant->users()->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <hr>
                <h6 class="mb-3"><i class="fas fa-user-tie"></i> بيانات رئيس الاتحاد</h6>
            </div>

            <div class="col-md-6 mb-3">
                <label for="admin_name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ $tenant->admin_name }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="admin_email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" value="{{ $tenant->admin_email }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="admin_phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="admin_phone" name="admin_phone" value="{{ $tenant->admin_phone }}" required>
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
