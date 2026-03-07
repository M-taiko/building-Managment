# خطة تطوير نظام إدارة العمارات - النظام المحدث

## 1. إدارة السكان ✅
**الحالة**: موجود بالفعل عبر UserManagementController
- رئيس الاتحاد يضيف سكان جدد
- كل ساكن له: اسم، إيميل، هاتف، كلمة مرور
- السكان يستطيعون تسجيل الدخول

## 2. نظام الاشتراكات الشهرية 🔄

### 2.1 أنواع الاشتراكات (subscription_types)
- جدول جديد لتخزين أنواع الاشتراكات
- الحقول:
  - اسم الاشتراك (مصعد، حدائق، نظافة، قمامة...)
  - الوصف
  - المبلغ الشهري
  - نشط/غير نشط

### 2.2 الاشتراكات (subscriptions) - تحديث
- إضافة حقل subscription_type_id
- ربط كل اشتراك بنوع معين
- الحقول الحالية + الجديدة:
  - tenant_id
  - apartment_id
  - subscription_type_id (جديد)
  - year
  - month
  - amount
  - paid_amount
  - status (unpaid, partial, paid)
  - paid_at (جديد - تاريخ السداد)

### 2.3 الجدولة التلقائية
- Command يعمل شهرياً (Cron Job)
- ينشئ اشتراكات جديدة لكل سكان
- لكل نوع اشتراك نشط

### 2.4 صفحة تسجيل السداد
- عرض كل الاشتراكات (مدفوع/غير مدفوع)
- تسجيل السداد لساكن معين
- إرسال إشعار عند السداد

### 2.5 الإشعارات
- إشعار تلقائي عند السداد
- إشعار تلقائي عند التأخر (3 أيام مثلاً)
- زر لإرسال تذكير يدوي لكل المتأخرين

## 3. المصروفات/الصيانة لمرة واحدة 🔄

### 3.1 تحديث جدول المصروفات (expenses)
- إضافة حقول جديدة:
  - distribution_type: (all/specific) - عام أو خاص
  - apartment_id (nullable) - إذا كان خاص بشقة معينة
  - is_one_time (boolean) - صيانة لمرة واحدة

### 3.2 توزيع التكاليف (expense_shares)
- جدول جديد لتوزيع المصروف
- الحقول:
  - expense_id
  - apartment_id (أو user_id)
  - share_amount (نصيب كل ساكن)
  - paid (boolean)
  - paid_at

### 3.3 الوظائف
- عند إنشاء مصروف جديد:
  - إذا كان عام: توزيع على كل السكان (بما فيهم رئيس الاتحاد)
  - إذا كان خاص: فقط على الساكن المحدد
- حساب تلقائي للنصيب
- إرسال إشعار لكل من عليه نصيب

## 4. الصفحات الجديدة المطلوبة

### 4.1 إدارة أنواع الاشتراكات
- CRUD لأنواع الاشتراكات
- تفعيل/تعطيل نوع اشتراك

### 4.2 صفحة السداد
- قائمة بكل الاشتراكات
- فلترة حسب: الحالة، الشهر، السنة، نوع الاشتراك
- تسجيل سداد
- زر "إرسال تذكير للمتأخرين"

### 4.3 صفحة المصروفات المحدثة
- اختيار نوع التوزيع عند الإنشاء
- عرض توزيع التكلفة
- تسجيل من دفع نصيبه

## 5. الإشعارات المطلوبة

### 5.1 إشعارات الاشتراكات
- ✅ عند السداد: "تم استلام دفعة اشتراك [النوع] بمبلغ [X] ريال"
- ⏰ عند التأخر: "تذكير: لديك اشتراك [النوع] متأخر بمبلغ [X] ريال"
- 📢 تذكير يدوي من رئيس الاتحاد

### 5.2 إشعارات المصروفات
- 💰 عند إنشاء مصروف جديد: "مصروف جديد: [العنوان] - نصيبك [X] ريال"
- ✅ عند السداد: "تم استلام نصيبك من مصروف [العنوان]"

## 6. خطوات التنفيذ

### المرحلة 1: قاعدة البيانات ✅
1. ✅ Create subscription_types table
2. ⏳ Add subscription_type_id to subscriptions
3. ⏳ Create expense_shares table
4. ⏳ Add distribution_type to expenses

### المرحلة 2: Models & Relationships
1. SubscriptionType model
2. Update Subscription model
3. ExpenseShare model
4. Update Expense model

### المرحلة 3: Controllers
1. SubscriptionTypeController
2. Update SubscriptionController (payment recording)
3. Update ExpenseController (distribution logic)

### المرحلة 4: Views
1. Subscription types management
2. Payment recording page
3. Updated expenses page with distribution

### المرحلة 5: Scheduled Tasks
1. Monthly subscription generation command
2. Late payment notification command

### المرحلة 6: Notifications
1. Payment received notification
2. Late payment notification
3. Expense share notification
