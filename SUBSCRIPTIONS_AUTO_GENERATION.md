# نظام التوليد التلقائي للاشتراكات الشهرية

## نظرة عامة
تم إنشاء نظام تلقائي لتوليد اشتراكات الصيانة الشهرية لكل الشقق بشكل دوري.

## كيفية العمل

### 1. ربط الشقق بأنواع الاشتراكات
كل شقة يمكن ربطها بواحد أو أكثر من أنواع الاشتراكات (صيانة، كهرباء، ماء، إلخ).

### 2. التوليد التلقائي
- **التوقيت**: كل يوم 1 من الشهر في تمام الساعة 00:01
- **ما يحدث**: يتم إنشاء اشتراك جديد لكل شقة حسب أنواع الاشتراكات المرتبطة بها
- **الإشعارات**: يتم إرسال إشعار تلقائي للمقيم عند إنشاء اشتراك جديد

## الأوامر المتاحة

### توليد الاشتراكات يدوياً
```bash
# للشهر الحالي
php artisan subscriptions:generate-monthly

# لشهر معين
php artisan subscriptions:generate-monthly --month=3 --year=2026

# للشهر القادم
php artisan subscriptions:generate-monthly --month=3 --year=2026
```

### إعداد Cron Job (للتشغيل التلقائي)

أضف السطر التالي إلى crontab:
```bash
* * * * * cd /path/to/building-managment && php artisan schedule:run >> /dev/null 2>&1
```

في Windows (Task Scheduler):
- افتح Task Scheduler
- Create Basic Task
- اختر Trigger: Daily
- Action: Start a program
- Program: `php`
- Arguments: `artisan schedule:run`
- Start in: `C:\xampp\htdocs\building-Managment`

## البنية التقنية

### الجداول
```sql
-- جدول ربط الشقق بأنواع الاشتراكات
apartment_subscription_type
- id
- apartment_id
- subscription_type_id
- is_active (boolean)
- timestamps
```

### الملفات المعدلة
1. **Models**:
   - `app/Models/Apartment.php` - إضافة علاقة subscriptionTypes
   - `app/Models/SubscriptionType.php` - إضافة علاقة apartments

2. **Commands**:
   - `app/Console/Commands/GenerateMonthlySubscriptions.php`

3. **Configuration**:
   - `bootstrap/app.php` - إضافة جدولة المهمة

4. **Migrations**:
   - `2026_02_13_001445_create_apartment_subscription_type_table.php`

## خطوات التفعيل

### الخطوة 1: ربط الشقق بأنواع الاشتراكات
يمكنك ربط الشقق بأنواع الاشتراكات بطريقتين:

#### من خلال الكود:
```php
$apartment = Apartment::find(1);
$subscriptionType = SubscriptionType::find(1);

// ربط الشقة بنوع اشتراك
$apartment->subscriptionTypes()->attach($subscriptionType->id);

// ربط عدة أنواع
$apartment->subscriptionTypes()->attach([1, 2, 3]);

// تحديث حالة النشاط
$apartment->subscriptionTypes()->updateExistingPivot($subscriptionType->id, ['is_active' => false]);
```

#### من خلال واجهة ويب (قريباً):
سيتم إضافة واجهة في صفحة تعديل الشقة لإدارة الاشتراكات.

### الخطوة 2: تفعيل Cron Job
راجع قسم "إعداد Cron Job" أعلاه.

### الخطوة 3: اختبار التوليد
```bash
# توليد تجريبي للشهر الحالي
php artisan subscriptions:generate-monthly

# مشاهدة النتائج
php artisan tinker
> Subscription::whereMonth('created_at', now()->month)->count();
```

## الميزات

✅ **توليد تلقائي شهري**
✅ **منع التكرار** - لا يتم إنشاء نفس الاشتراك مرتين
✅ **إشعارات تلقائية** للمقيمين
✅ **دعم عدة أنواع اشتراكات** لنفس الشقة
✅ **تفعيل/تعطيل** أنواع الاشتراكات لكل شقة
✅ **تقارير مفصلة** أثناء التوليد

## حالات الاستخدام

### 1. شقة بنوع اشتراك واحد (صيانة فقط)
```php
$apartment->subscriptionTypes()->attach($maintenanceType->id);
```

### 2. شقة بعدة أنواع (صيانة + كهرباء + ماء)
```php
$apartment->subscriptionTypes()->attach([
    $maintenanceType->id,
    $electricityType->id,
    $waterType->id
]);
```

### 3. تعطيل نوع اشتراك مؤقتاً
```php
$apartment->subscriptionTypes()->updateExistingPivot($electricityType->id, [
    'is_active' => false
]);
```

## استكشاف الأخطاء

### المشكلة: لم يتم إنشاء اشتراكات
**الحلول:**
1. تحقق من أن الشقق مرتبطة بأنواع اشتراكات:
   ```php
   $apartment->activeSubscriptionTypes()->count();
   ```

2. تحقق من أن أنواع الاشتراكات نشطة:
   ```php
   SubscriptionType::where('is_active', true)->get();
   ```

3. تحقق من السجلات (logs):
   ```bash
   tail -f storage/logs/laravel.log
   ```

### المشكلة: Cron Job لا يعمل
**الحلول:**
1. تحقق من صلاحيات الملفات
2. تأكد من تشغيل `schedule:run` كل دقيقة
3. تحقق من timezone في `config/app.php`

## الدعم الفني
للمزيد من المعلومات، راجع الملفات التالية:
- `app/Console/Commands/GenerateMonthlySubscriptions.php`
- `app/Models/Apartment.php`
- `bootstrap/app.php`
