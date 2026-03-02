<div class="modal-header bg-warning text-dark">
    <h6 class="modal-title fw-bold">
        <i class="fas fa-edit me-2"></i>تعديل الشقة
    </h6>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="apartmentForm" action="{{ route('apartments.update', $apartment->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <!-- بيانات الشقة الأساسية -->
        <h6 class="border-bottom pb-2 mb-3">بيانات الشقة</h6>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">رقم الشقة <span class="text-danger">*</span></label>
                <input type="text" name="number" class="form-control" value="{{ $apartment->number }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">الدور <span class="text-danger">*</span></label>
                <input type="text" name="floor" class="form-control" value="{{ $apartment->floor }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">اسم المالك <span class="text-danger">*</span></label>
            <input type="text" name="owner_name" class="form-control" value="{{ $apartment->owner_name }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">نوع الوحدة <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="residential" {{ $apartment->type == 'residential' ? 'selected' : '' }}>سكنية</option>
                    <option value="commercial" {{ $apartment->type == 'commercial' ? 'selected' : '' }}>تجارية</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">نوع التوزيع <span class="text-danger">*</span></label>
                <select name="share_type" id="share_type_edit" class="form-select" required>
                    <option value="equal" {{ $apartment->share_type == 'equal' ? 'selected' : '' }}>توزيع متساوي</option>
                    <option value="custom" {{ $apartment->share_type == 'custom' ? 'selected' : '' }}>نسبة مخصصة</option>
                </select>
            </div>
        </div>

        <div class="mb-3" id="custom_percentage_div_edit" style="display: {{ $apartment->share_type == 'custom' ? 'block' : 'none' }};">
            <label class="form-label">نسبة المشاركة (%) <span class="text-danger">*</span></label>
            <input type="number" name="custom_share_percentage" class="form-control" value="{{ $apartment->custom_share_percentage }}" min="0" max="100" step="0.01" placeholder="مثال: 12.5">
            <small class="text-muted">النسبة المئوية من إجمالي المصروفات</small>
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_edit" {{ $apartment->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active_edit">
                    الوحدة نشطة
                </label>
            </div>
        </div>

        <!-- إدارة أنواع الاشتراكات -->
        <hr>
        <h6 class="border-bottom pb-2 mb-3">
            <i class="fas fa-tags text-primary"></i>
            أنواع الاشتراكات الشهرية
        </h6>

        @php
            $allSubscriptionTypes = \App\Models\SubscriptionType::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_active', true)
                ->get();
            $apartmentSubscriptionIds = $apartment->subscriptionTypes->pluck('id')->toArray();
        @endphp

        @if($allSubscriptionTypes->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>لا توجد أنواع اشتراكات متاحة</strong>
                <p class="mb-0 small">يرجى إضافة أنواع الاشتراكات من قائمة "أنواع الاشتراكات" أولاً</p>
            </div>
        @else
            <div class="mb-3">
                <p class="text-muted small mb-2">
                    <i class="fas fa-lightbulb"></i>
                    اختر أنواع الاشتراكات التي سيتم تطبيقها على هذه الشقة شهرياً بشكل تلقائي
                </p>

                <div class="subscription-types-grid">
                    @foreach($allSubscriptionTypes as $type)
                        <div class="form-check subscription-type-card">
                            <input
                                class="form-check-input subscription-type-checkbox"
                                type="checkbox"
                                name="subscription_types[]"
                                value="{{ $type->id }}"
                                id="sub_type_{{ $type->id }}"
                                {{ in_array($type->id, $apartmentSubscriptionIds) ? 'checked' : '' }}
                            >
                            <label class="form-check-label w-100" for="sub_type_{{ $type->id }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $type->name }}</strong>
                                        @if($type->description)
                                            <br>
                                            <small class="text-muted">{{ $type->description }}</small>
                                        @endif
                                    </div>
                                    <div class="badge bg-primary">
                                        {{ number_format($type->amount, 2) }} ج.م
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i>
                    عند تفعيل الاشتراك، سيتم إنشاء فاتورة شهرية تلقائياً في بداية كل شهر
                </small>
            </div>
        @endif

        <!-- بيانات المستخدم (إذا كان موجود) -->
        @if($apartment->resident)
        <hr>
        <div class="alert alert-success">
            <i class="fas fa-user-check"></i>
            <strong>الوحدة مرتبطة بحساب:</strong> {{ $apartment->resident->name }} ({{ $apartment->resident->email }})
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="update_user" value="1" id="update_user_checkbox">
            <label class="form-check-label" for="update_user_checkbox">
                <strong>تحديث بيانات حساب المالك</strong>
            </label>
        </div>

        <div id="user_fields_edit" style="display: none;">
            <h6 class="border-bottom pb-2 mb-3">بيانات حساب المالك</h6>

            <div class="mb-3">
                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                <input type="text" name="user_name" class="form-control" value="{{ $apartment->resident->name }}" placeholder="اسم المستخدم الكامل">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                    <input type="email" name="user_email" class="form-control" value="{{ $apartment->resident->email }}" placeholder="example@email.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="user_phone" class="form-control" value="{{ $apartment->resident->phone }}" placeholder="05XXXXXXXX">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">كلمة المرور (اتركه فارغاً إذا لم ترغب بالتغيير)</label>
                <input type="password" name="user_password" class="form-control" placeholder="كلمة مرور جديدة (6 أحرف على الأقل)">
                <small class="text-muted">اترك هذا الحقل فارغاً للحفاظ على كلمة المرور الحالية</small>
            </div>
        </div>
        @else
        <hr>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>لا يوجد حساب مستخدم مرتبط بهذه الوحدة</strong>
            <p class="mb-0 small">يمكنك إنشاء حساب من خلال صفحة إدارة المستخدمين</p>
        </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>

<style>
    .subscription-types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 10px;
    }

    .subscription-type-card {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 15px;
        transition: all 0.3s;
        background: white;
        margin-bottom: 0;
    }

    .subscription-type-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .subscription-type-checkbox:checked + label {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }

    .subscription-type-card .form-check-input:checked ~ .form-check-label {
        border-color: #667eea;
    }
</style>

<script>
$(document).ready(function() {
    // إظهار/إخفاء حقل النسبة المخصصة
    $('#share_type_edit').change(function() {
        if ($(this).val() === 'custom') {
            $('#custom_percentage_div_edit').show();
            $('input[name="custom_share_percentage"]').attr('required', true);
        } else {
            $('#custom_percentage_div_edit').hide();
            $('input[name="custom_share_percentage"]').attr('required', false);
        }
    });

    // إظهار/إخفاء حقول المستخدم
    $('#update_user_checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#user_fields_edit').show();
            $('#user_fields_edit input[name="user_name"], #user_fields_edit input[name="user_email"]').attr('required', true);
        } else {
            $('#user_fields_edit').hide();
            $('#user_fields_edit input').attr('required', false);
        }
    });
});
</script>
