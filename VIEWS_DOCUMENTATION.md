# وثائق Views العمارات والمستخدمين

## نظرة عامة

تم إنشاء مجموعة شاملة من Views لإدارة العمارات والمستخدمين مع تصميم Modern UI/UX احترافي.

## قائمة الملفات المُنشأة

### 1. العمارات (Tenants)

#### المسار: `resources/views/tenants/`

- **index.blade.php** - صفحة قائمة العمارات
  - عرض DataTable للعمارات مع server-side processing
  - بطاقات إحصائية (إجمالي، نشطة، معلقة، معطلة)
  - نموذج Modal لإضافة وتعديل العمارات
  - أزرار تفاعلية (عرض، تعديل، حذف)
  - بحث فوري
  - حالات مختلفة للعمارات مع Badges ملونة

- **create.blade.php** - صفحة إضافة عمارة جديدة
  - نموذج شامل بـ form validation
  - حقول: الاسم، المدينة، العنوان، رئيس الاتحاد، عدد الوحدات، الحالة، الشعار
  - رسائل خطأ واضحة
  - أيقونات Font Awesome لكل حقل

- **edit.blade.php** - صفحة تعديل العمارة
  - نموذج مع البيانات الحالية
  - عرض الشعار الحالي مع إمكانية تغييره
  - تحديث server-side مع form validation

### 2. المستخدمين (Users)

#### المسار: `resources/views/users/`

- **index.blade.php** - صفحة قائمة المستخدمين
  - عرض DataTable للمستخدمين مع server-side processing
  - بطاقات إحصائية (إجمالي، نشطين، معلقين، معطلين)
  - نموذج Modal لإضافة وتعديل المستخدمين
  - أزرار تفاعلية (عرض، تعديل، حذف)
  - بحث فوري
  - حالات مختلفة للمستخدمين

- **create.blade.php** - صفحة إضافة مقيم جديد
  - نموذج شامل للبيانات الشخصية والسكنية
  - حقول: الاسم، البريد الإلكتروني، الهاتف، العمارة، رقم الوحدة، نوع المستخدم، كلمة المرور، الحالة
  - خيار منح صلاحيات إدارية
  - validation server-side

- **edit.blade.php** - صفحة تعديل بيانات المستخدم
  - نموذج مع البيانات الحالية
  - كلمة المرور اختياري عند التعديل
  - تحديث جميع البيانات

### 3. ملفات CSS و JavaScript

#### `public/css/modern-ui.css`
- متغيرات CSS للألوان والحجوم والظلال
- أنماط الأزرار والـ Cards والـ Badges
- تصميم الـ Forms والـ Modal
- تنسيق الـ DataTables
- تأثيرات التمرير والـ Animations
- Responsive design للأجهزة الصغيرة

#### `public/js/datatables-init.js`
- فئات مساعدة (Helpers) لـ JavaScript
- `DataTablesManager` - لإدارة DataTables
- `FormHandler` - لمعالجة النماذج
- `ModalManager` - لإدارة الـ Modals
- `ApiManager` - لعمليات AJAX
- دوال Utility مشتركة

### 4. تحديث Layout

#### `resources/views/layouts/admin.blade.php`
- إضافة رابط "العمارات" (للسوبر أدمن فقط)
- إضافة رابط "المستخدمين" (لرئيس الاتحاد والسوبر أدمن)
- دعم الشروط والصلاحيات
- تحميل ملف CSS الحديث

## متطلبات الـ Routes

يجب إضافة الـ Routes التالية في `routes/web.php`:

```php
// Tenants Routes (للسوبر أدمن فقط)
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::resource('tenants', 'TenantController');
    Route::get('api/tenants', 'TenantController@apiIndex')->name('api.tenants');
    Route::get('api/presidents', 'TenantController@getPresidents')->name('api.presidents');
});

// Users Routes (لرئيس الاتحاد والسوبر أدمن)
Route::middleware(['auth', 'union_president'])->group(function () {
    Route::resource('users', 'UserController');
    Route::get('api/users', 'UserController@apiIndex')->name('api.users');
    Route::get('api/tenants/list', 'UserController@getTenants')->name('api.tenants.list');
});
```

## متطلبات الـ Controllers

### TenantController
يجب أن يحتوي على:
- `index()` - عرض صفحة القائمة
- `create()` - عرض صفحة الإضافة
- `store()` - حفظ عمارة جديدة
- `edit()` - عرض صفحة التعديل
- `update()` - تحديث العمارة
- `destroy()` - حذف العمارة
- `apiIndex()` - إرجاع DataTable JSON
- `getPresidents()` - إرجاع قائمة رؤساء الاتحادات

### UserController
يجب أن يحتوي على:
- `index()` - عرض صفحة القائمة
- `create()` - عرض صفحة الإضافة
- `store()` - حفظ مستخدم جديد
- `edit()` - عرض صفحة التعديل
- `update()` - تحديث المستخدم
- `destroy()` - حذف المستخدم
- `apiIndex()` - إرجاع DataTable JSON
- `getTenants()` - إرجاع قائمة العمارات

## الميزات المتضمنة

### 1. التصميم الحديث (Modern UI/UX)
- Gradient backgrounds للأزرار والـ Header
- Cards بـ shadow effects
- Hover animations و transitions
- Icons من Font Awesome
- Colors محددة:
  - Primary: #667eea إلى #764ba2 (Gradient)
  - Success: #10b981
  - Warning: #f59e0b
  - Danger: #ef4444

### 2. البطاقات الإحصائية (Stat Cards)
- عرض أرقام مهمة في البداية
- تحديث فوري عند تحديث الجدول
- أيقونات ملونة مختلفة

### 3. DataTables المحسّن
- Server-side processing
- البحث الفوري
- Pagination مخصص
- Responsive design
- Sorting و Filtering

### 4. Modals الحديثة
- Modal للإضافة والتعديل
- Modal لعرض التفاصيل
- Blur backdrop effect
- Smooth animations
- Form validation

### 5. النماذج (Forms)
- Floating labels (اختياري)
- Icons داخل الحقول
- Focus effects مخصصة
- Validation feedback واضح
- Support للملفات (File upload)

### 6. الإشعارات
- Toastr notifications
- Success, Error, Warning, Info
- Smooth animations
- Progress bar

### 7. البحث الفوري
- Search bar مصمم بشكل جميل
- Debounced search
- Icon search مدمج

## كيفية الاستخدام

### 1. تسجيل الـ Routes

أضف الـ Routes في `routes/web.php`:

```php
Route::middleware(['auth', 'super_admin'])->prefix('admin')->group(function () {
    Route::resource('tenants', TenantController::class);
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('users', UserController::class);
});
```

### 2. إنشاء Controllers

تأكد من وجود Controllers مع الـ Actions المذكورة أعلاه.

### 3. إضافة الـ API Endpoints

تأكد من إضافة الـ API endpoints للـ DataTables.

### 4. اختبار الصفحات

- زر على "/tenants" للعمارات (سوبر أدمن فقط)
- زر على "/users" للمستخدمين (رئيس اتحاد وأعلى)

## Responsive Design

جميع الصفحات تدعم:
- الشاشات الكبيرة (Desktop)
- الشاشات الوسطة (Tablet)
- الشاشات الصغيرة (Mobile)

مع تعديلات تلقائية:
- حجم الأزرار
- حجم الخطوط
- عرض الجداول
- ترتيب العناصر

## أيقونات Font Awesome المستخدمة

- `fa-building` - العمارات
- `fa-users` - المستخدمين
- `fa-user` - المستخدم
- `fa-user-plus` - إضافة مستخدم
- `fa-plus-circle` - إضافة
- `fa-edit` - تعديل
- `fa-trash` - حذف
- `fa-eye` - عرض
- `fa-door-open` - الوحدات
- `fa-phone` - الهاتف
- `fa-envelope` - البريد
- `fa-map-marker-alt` - الموقع
- `fa-home` - الوحدات
- `fa-lock` - كلمة المرور
- `fa-search` - بحث
- `fa-check-circle` - نشط
- `fa-times-circle` - معطل
- `fa-exclamation-circle` - معلق
- `fa-hourglass-half` - قيد الانتظار
- `fa-save` - حفظ
- `fa-arrow-right` - عودة

## ملاحظات مهمة

1. تأكد من تثبيت جميع الـ dependencies:
   - Bootstrap 5
   - DataTables
   - Font Awesome
   - jQuery
   - Axios
   - Toastr

2. تأكد من إعداد CSRF token في الـ Layout

3. استخدم Middleware للتحقق من الصلاحيات

4. تأكد من validation البيانات في الـ Backend

5. استخدم API endpoints للـ DataTable processing

## دعم RTL (اللغة العربية)

جميع الصفحات مدعومة بـ:
- RTL direction
- Gradient من اليمين إلى اليسار
- Font Cairo
- Messages عربي 100%

## التحديثات المستقبلية

يمكن إضافة:
- Multi-language support
- Dark mode
- Advanced filters
- Export to PDF/Excel
- Print functionality
- Bulk actions
- Image optimization
