# المرجع التشغيلي للصلاحيات والأدوار

تاريخ التحديث: 2026-03-11

هذا الملف هو المرجع العربي الرسمي لتوزيع الصلاحيات في النظام بعد آخر تحديث.
المصدر المعتمد تقنيًا: `config/permissions.php` + تقرير التحقق `PERMISSIONS_MATRIX_REPORT.md`.

## 1) قائمة الصلاحيات المعتمدة

> ملاحظة مهمة: تحديد صلاحيات الصفحات (الأقسام، المحافظات، إيفاد خارج البلد) يتم من مكان مركزي في
> `config/permissions.php` ضمن المفتاح `page_permissions`.

### صلاحيات الوصول
- `access-dashboard`: الوصول إلى لوحة التحكم والإحصائيات.
- `access-admin-dashboard`: الوصول إلى لوحة الأدمن فقط.

### صلاحيات الكشوفات
- `create-payrolls`: إنشاء الكشوفات.
- `view-payrolls`: عرض الكشوفات.
- `edit-payrolls`: تعديل الكشوفات.
- `delete-payrolls`: حذف الكشوفات.
- `print-payrolls`: طباعة الكشوفات.
- `export-payrolls`: تصدير الكشوفات.

### صلاحيات الموظفين
- `create-employees`: إضافة موظفين.
- `view-employees`: عرض الموظفين.
- `edit-employees`: تعديل الموظفين.
- `delete-employees`: حذف الموظفين.
- `import-employees`: استيراد موظفين.

### صلاحيات التواقيع
- `manage-signatures`: إدارة التواقيع.

### صلاحيات النظام والإعدادات
- `manage-settings`: إدارة الإعدادات العامة.
- `manage-users`: إدارة المستخدمين.
- `manage-roles`: إدارة الأدوار.
- `manage-backups`: إدارة النسخ الاحتياطية.
- `manage-mission-types`: إدارة أنواع الإيفاد.

### صلاحيات الأقسام والمحافظات والمدن
- `manage-departments`: إدارة الأقسام.
- `manage-governorates`: إدارة المحافظات.
- `manage-cities`: إدارة المدن.

## 2) توزيع الصلاحيات على الأدوار

### `admin`
- يمتلك جميع الصلاحيات (`*`).

### `payroll-manager`
- `access-dashboard`
- `create-payrolls`, `view-payrolls`, `edit-payrolls`, `delete-payrolls`, `print-payrolls`, `export-payrolls`
- `view-employees`
- `manage-signatures`
- `manage-users`
- `manage-mission-types`

### `employee-manager`
- `access-dashboard`
- `create-employees`, `view-employees`, `edit-employees`, `delete-employees`, `import-employees`
- `manage-departments`, `manage-governorates`, `manage-cities`
- `manage-users`

### `data-entry`
- `access-dashboard`
- `create-payrolls`, `view-payrolls`
- `view-employees`

### `viewer`
- `access-dashboard`
- `view-payrolls`, `print-payrolls`
- `view-employees`

### `رئيس قسم`
- `access-dashboard`
- `view-payrolls`, `print-payrolls`
- `view-employees`

### `مسؤول شعبة`
- `access-dashboard`
- `view-payrolls`
- `view-employees`

### `مسؤول وحدة`
- `access-dashboard`
- `view-payrolls`
- `view-employees`

## 3) سياسة الوصول للمسارات الحرجة

- لوحة التحكم: تتطلب `access-dashboard`.
- لوحة الأدمن: تتطلب `access-admin-dashboard`.
- إدارة المستخدمين: تتطلب `manage-users`.
- إدارة الأدوار: تتطلب `manage-roles`.
- النسخ الاحتياطية: تتطلب `manage-backups`.
- أنواع الإيفاد: تتطلب `manage-settings` أو `manage-mission-types`.
- الأقسام: تتطلب `manage-settings` أو `manage-departments`.
- المحافظات: تتطلب `manage-settings` أو `manage-governorates`.
- المدن: تتطلب `manage-settings` أو `manage-governorates` أو `manage-cities`.

## 4) ملاحظات تشغيلية

- أي تعديل على الصلاحيات أو الأدوار يجب أن يُتبع بالأوامر:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
```

- للتحقق الدوري من التغطية، استخدم:

```bash
php check_permissions_matrix.php
```

- تقرير الفحص الناتج:
  - `PERMISSIONS_MATRIX_REPORT.md`

## 5) قاعدة الحوكمة المعتمدة

- لا يتم منح `access-admin-dashboard` تلقائيًا مع `manage-users`.
- الفصل بين الصلاحيتين مقصود لتقليل الصلاحيات الزائدة (Least Privilege).
- أي دور يحتاج لوحة الأدمن يجب منحه `access-admin-dashboard` بشكل صريح.
