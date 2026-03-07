# دليل تثبيت وتكوين Views العمارات والمستخدمين

## ملخص الملفات المُنشأة

### 1. Views - صفحات الويب

#### العمارات (Tenants):
```
resources/views/tenants/
├── index.blade.php       # قائمة العمارات مع DataTable
├── create.blade.php      # صفحة إضافة عمارة جديدة
└── edit.blade.php        # صفحة تعديل العمارة
```

#### المستخدمين (Users):
```
resources/views/users/
├── index.blade.php       # قائمة المستخدمين مع DataTable
├── create.blade.php      # صفحة إضافة مقيم جديد
└── edit.blade.php        # صفحة تعديل بيانات المقيم
```

### 2. Assets - الملفات الثابتة

#### CSS:
```
public/css/
└── modern-ui.css         # تصميم Modern UI/UX موحد
```

#### JavaScript:
```
public/js/
└── datatables-init.js    # مساعدات JavaScript مشتركة
```

### 3. التوثيق والأمثلة

```
├── VIEWS_DOCUMENTATION.md  # توثيق شاملة للـ Views
├── ROUTES_EXAMPLE.php      # أمثلة للـ Routes
├── CONTROLLER_EXAMPLE.php  # أمثلة للـ Controllers
└── INSTALLATION_GUIDE.md   # هذا الملف
```

### 4. التحديثات على الملفات الموجودة

```
resources/views/layouts/admin.blade.php  # تم إضافة الروابط والـ CSS
```

## خطوات التثبيت

### خطوة 1: تثبيت Dependencies

تأكد من وجود جميع المكتبات المطلوبة:

```bash
composer require yajra/laravel-datatables

npm install
npm run dev
```

### خطوة 2: إضافة الـ Routes

أضف الـ Routes التالية في `routes/web.php`:

```php
<?php

use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

// Tenant Routes - Super Admin Only
Route::middleware(['auth', 'super_admin'])->prefix('admin')->group(function () {
    // Web Routes
    Route::resource('tenants', TenantController::class);

    // API Routes for DataTables
    Route::get('api/tenants', [TenantController::class, 'apiIndex'])->name('api.tenants');
    Route::get('api/tenants/{id}', [TenantController::class, 'show']);
    Route::post('api/tenants', [TenantController::class, 'store']);
    Route::put('api/tenants/{id}', [TenantController::class, 'update']);
    Route::delete('api/tenants/{id}', [TenantController::class, 'destroy']);
    Route::get('api/presidents', [TenantController::class, 'getPresidents'])->name('api.presidents');
});

// User Routes - Union President and above
Route::middleware(['auth', 'union_president'])->prefix('admin')->group(function () {
    // Web Routes
    Route::resource('users', UserController::class);

    // API Routes for DataTables
    Route::get('api/users', [UserController::class, 'apiIndex'])->name('api.users');
    Route::get('api/users/{id}', [UserController::class, 'show']);
    Route::post('api/users', [UserController::class, 'store']);
    Route::put('api/users/{id}', [UserController::class, 'update']);
    Route::delete('api/users/{id}', [UserController::class, 'destroy']);
    Route::get('api/tenants/list', [UserController::class, 'getTenants'])->name('api.tenants.list');
});
```

### خطوة 3: إنشاء Controllers

انسخ الكود من `CONTROLLER_EXAMPLE.php` وأنشئ:

**File: `app/Http/Controllers/TenantController.php`**
```php
// استخدم الكود من CONTROLLER_EXAMPLE.php - TenantController section
```

**File: `app/Http/Controllers/UserController.php`**
```php
// استخدم الكود من CONTROLLER_EXAMPLE.php - UserController section
```

### خطوة 4: إنشاء Models (إذا لم تكن موجودة)

```bash
php artisan make:model Tenant -m
php artisan make:model User -m
```

#### Tenant Model - `app/Models/Tenant.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'president_id',
        'units_count',
        'status',
        'logo'
    ];

    public function president()
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
```

#### User Model - `app/Models/User.php`:
أضف هذه العلاقات:
```php
public function tenant()
{
    return $this->belongsTo(Tenant::class);
}
```

### خطوة 5: إنشاء Migrations (إذا لم تكن موجودة)

#### Migration for Tenants:
```bash
php artisan make:migration create_tenants_table
```

**File: `database/migrations/xxxx_xx_xx_xxxxxx_create_tenants_table.php`**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('city');
            $table->text('address')->nullable();
            $table->foreignId('president_id')->constrained('users')->onDelete('restrict');
            $table->integer('units_count');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->string('logo')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('president_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

#### Migration for Users (إضافة حقول):
```php
// في الـ migration الموجودة users table، أضف:
$table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
$table->string('unit_number')->nullable();
$table->enum('user_type', ['resident', 'owner', 'tenant'])->default('resident');
$table->enum('status', ['active', 'inactive', 'pending'])->default('active');
$table->boolean('is_admin')->default(false);
```

### خطوة 6: تشغيل Migrations

```bash
php artisan migrate
```

### خطوة 7: التحقق من Middleware

تأكد من وجود Middleware التالية:

**`app/Http/Middleware/SuperAdmin.php`**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'super_admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }
}
```

**`app/Http/Middleware/UnionPresident.php`**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UnionPresident
{
    public function handle(Request $request, Closure $next)
    {
        $allowedRoles = ['super_admin', 'union_president'];

        if (auth()->check() && in_array(auth()->user()->role, $allowedRoles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }
}
```

سجل الـ Middleware في `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ... الـ middleware الموجودة
    'super_admin' => \App\Http\Middleware\SuperAdmin::class,
    'union_president' => \App\Http\Middleware\UnionPresident::class,
];
```

### خطوة 8: إضافة Form Requests (اختياري ولكن مفيد)

```bash
php artisan make:request TenantRequest
php artisan make:request UserRequest
```

**`app/Http/Requests/TenantRequest.php`**:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role === 'super_admin';
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:tenants,name,' . $this->route('tenant')?->id,
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'president_id' => 'required|exists:users,id',
            'units_count' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,pending',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم العمارة مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'president_id.required' => 'رئيس الاتحاد مطلوب',
            'units_count.required' => 'عدد الوحدات مطلوب',
        ];
    }
}
```

## اختبار التثبيت

### 1. الدخول إلى الصفحات:

```
Super Admin:
- http://yoursite/admin/tenants (قائمة العمارات)
- http://yoursite/admin/tenants/create (إضافة عمارة)

Union President:
- http://yoursite/admin/users (قائمة المستخدمين)
- http://yoursite/admin/users/create (إضافة مستخدم)
```

### 2. التحقق من الـ DataTable:
- يجب أن يتم تحميل البيانات من الـ API
- البحث يجب أن يعمل
- الترقيم يجب أن يعمل

### 3. اختبار العمليات:
- إضافة سجل جديد
- تعديل سجل موجود
- حذف سجل
- عرض التفاصيل

## ملاحظات مهمة

### 1. الصلاحيات:
- تأكد من أن `super_admin` يمكنه الوصول فقط إلى العمارات
- تأكد من أن `union_president` يمكنه الوصول فقط إلى المستخدمين

### 2. التحقق من البيانات:
- تحقق من جميع validations
- تأكد من رسائل الخطأ بالعربية

### 3. تخزين الملفات:
- استخدم `php artisan storage:link` لتخزين الشعارات
- تأكد من أن مجلد `storage/app/public` قابل للكتابة

### 4. Performance:
- استخدم eager loading للعلاقات
- أضف indexes للـ database
- استخدم caching إذا لزم الأمر

## استكشاف الأخطاء الشائعة

### خطأ: "Route not found"
- تأكد من تسجيل الـ Routes في `routes/web.php`
- شغل `php artisan route:clear` و `php artisan route:cache`

### خطأ: "Table not found"
- تأكد من تشغيل الـ migrations
- تحقق من اسم الجدول في النموذج

### DataTable لا يتحمل:
- افتح Browser Console وتحقق من الأخطاء
- تأكد من أن الـ API endpoint موجود
- تحقق من CORS headers

### المشاكل مع الـ CSS/JS:
- شغل `npm run dev`
- امسح Browser cache
- تحقق من مسارات الملفات في HTML

## التخصيص والتطوير

### تغيير الألوان:
عدّل متغيرات CSS في `public/css/modern-ui.css`:
```css
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-color: #10b981;
    /* الخ... */
}
```

### إضافة حقول جديدة:
1. أضف الحقل في Database migration
2. أضف الحقل في Model
3. أضف HTML في View
4. أضف validation في Controller/Request

### تغيير الأيقونات:
عدّل أيقونات Font Awesome في الـ Views حسب احتياجاتك.

## دعم إضافي

للحصول على مساعدة إضافية:
1. راجع `VIEWS_DOCUMENTATION.md` لتفاصيل أكثر
2. راجع `CONTROLLER_EXAMPLE.php` للأمثلة الكاملة
3. تحقق من `ROUTES_EXAMPLE.php` لتعريفات الـ Routes

---

تم إنشاء جميع الملفات بواسطة Claude Code
التاريخ: 2026-02-12
