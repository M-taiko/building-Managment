# 🏢 نظام إدارة العمارات المتكامل

## ✅ النظام جاهز بالكامل!

تم تطوير نظام متكامل لإدارة العمارات مع Multi-Tenant + Modern UI/UX

---

## 🔐 بيانات الدخول

### مدير النظام (Super Admin)
```
رقم العمارة: (اتركه فارغاً)
البريد: superadmin@masarsoft.io
كلمة المرور: admin123
```

### رئيس اتحاد الملاك (Building Admin)
```
رقم العمارة: E001
البريد: admin@building.com
كلمة المرور: password
```

### المقيم (Resident)
```
رقم العمارة: E001
البريد: resident1@building.com
كلمة المرور: password
```

---

## 📋 الوحدات المتاحة

### للسوبر أدمن (Super Admin):
- ✅ **إدارة العمارات** - إضافة/تعديل/حذف العمارات
- ✅ **إنشاء حسابات رؤساء الاتحاد** - تلقائياً عند إضافة عمارة
- ✅ عرض جميع البيانات

### لرئيس اتحاد الملاك (Building Admin):
- ✅ **لوحة التحكم** - إحصائيات شاملة
- ✅ **الشقق** - إدارة الشقق في العمارة
- ✅ **الاشتراكات** - إدارة اشتراكات الشقق
- ✅ **المصروفات** - تسجيل المصروفات
- ✅ **طلبات الصيانة** - متابعة الطلبات
- ✅ **إدارة المستخدمين** - إنشاء حسابات للمقيمين

### للمقيم (Resident):
- ✅ **لوحة التحكم الخاصة** - مدفوعاته ومتأخراته
- ✅ **طلبات الصيانة** - إنشاء ومتابعة طلباته
- ✅ **سجل المدفوعات** - عرض سجل الدفع

---

## 🎨 التصميم Modern UI/UX

### المميزات:
- 🎨 تصميم عصري بـ Gradients
- 📱 Responsive - يعمل على جميع الأجهزة
- 🌙 ألوان احترافية
- ⚡ Animations سلسة
- 📊 DataTables محسّنة
- 🔔 Toastr Notifications
- 🎯 Icons من Font Awesome
- 📝 Forms جميلة مع Validation
- 🎭 Modals تفاعلية

### الألوان:
- **Primary:** Gradient (#667eea → #764ba2)
- **Success:** #10b981
- **Warning:** #f59e0b
- **Danger:** #ef4444

---

## 🚀 التشغيل

```bash
# 1. شغّل السيرفر
php artisan serve

# 2. افتح المتصفح
http://localhost:8000
```

---

## 📂 هيكل المشروع

```
├── app/
│   ├── Http/Controllers/
│   │   ├── TenantController.php           # إدارة العمارات
│   │   ├── UserManagementController.php   # إدارة المستخدمين
│   │   ├── ApartmentController.php        # إدارة الشقق
│   │   ├── SubscriptionController.php     # إدارة الاشتراكات
│   │   ├── ExpenseController.php          # إدارة المصروفات
│   │   └── MaintenanceRequestController.php
│   ├── Models/
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Apartment.php
│   │   └── ...
│   ├── Traits/
│   │   └── BelongsToTenant.php
│   └── Services/
│       └── DashboardService.php
├── resources/views/
│   ├── layouts/
│   │   └── admin.blade.php               # Layout رئيسي
│   ├── tenants/                          # صفحات العمارات
│   ├── users/                            # صفحات المستخدمين
│   ├── apartments/                       # صفحات الشقق
│   ├── subscriptions/                    # صفحات الاشتراكات
│   ├── expenses/                         # صفحات المصروفات
│   └── maintenance/                      # صفحات الصيانة
└── public/
    ├── css/
    │   └── modern-ui.css                 # تصميم عصري
    └── js/
        └── datatables-init.js            # مساعدات JS
```

---

## 📊 قاعدة البيانات

### الجداول:
1. **tenants** - العمارات (مع tenant_code)
2. **users** - المستخدمين (مع tenant_id)
3. **apartments** - الشقق
4. **subscriptions** - الاشتراكات
5. **expenses** - المصروفات
6. **maintenance_requests** - طلبات الصيانة
7. **permissions & roles** - الصلاحيات (Spatie)

---

## 🔒 الأمان

- ✅ Multi-Tenant Isolation (عزل كامل للبيانات)
- ✅ Role-Based Access Control
- ✅ CSRF Protection
- ✅ XSS Prevention
- ✅ Password Hashing
- ✅ Validation شاملة

---

## 🛠️ التقنيات

| التقنية | الاستخدام |
|---------|-----------|
| Laravel 12 | Backend Framework |
| Bootstrap 5 RTL | UI Framework |
| DataTables | جداول تفاعلية |
| Chart.js | رسوم بيانية |
| Toastr | إشعارات |
| Font Awesome | أيقونات |
| Cairo Font | خط عربي |
| Spatie Permission | إدارة الصلاحيات |

---

## 📝 الوثائق

- **INSTALLATION.md** - دليل التثبيت الكامل
- **QUICK_START.md** - البدء السريع (5 دقائق)
- **VIEWS_DOCUMENTATION.md** - توثيق الـ Views

---

## 🎯 الخطوات التالية

1. ✅ قم بتسجيل الدخول كـ Super Admin
2. ✅ أضف عمارة جديدة (تُنشأ حساب رئيس اتحاد تلقائياً)
3. ✅ سجّل دخول كرئيس اتحاد
4. ✅ أضف شقق
5. ✅ أنشئ حسابات للمقيمين
6. ✅ ابدأ في استخدام النظام!

---

## 💡 نصائح

- استخدم **رقم عمارة فريد** لكل عمارة جديدة (مثل: E002, E003...)
- كلمة المرور يتم تشفيرها تلقائياً
- جميع البيانات معزولة حسب العمارة
- DataTables تدعم البحث والفلترة والتصدير

---

## 📞 الدعم

**تم التطوير بواسطة:** [masarsoft.io](https://masarsoft.io)

**الإصدار:** 1.0.0  
**التاريخ:** 2026-02-12  
**Laravel:** 12.x  
**PHP:** 8.2+

---

## 📄 الترخيص

MIT License

---

**🎉 النظام جاهز للاستخدام الفوري!**
