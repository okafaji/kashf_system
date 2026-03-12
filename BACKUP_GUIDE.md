# دليل النسخ الاحتياطي والنقل

هذا الدليل يشرح كيفية عمل نسخة احتياطية كاملة للبرنامج والبيانات، ونقلها بسهولة إلى جهاز آخر.

## 1️⃣ النسخ الاحتياطية للكود (Code Backup)

### استخدام Git

البرنامج موجود في git repository. للحفاظ على الكود:

```bash
# إذا كان لديك GitHub/GitLab account
git remote -v  # تحقق من الـ remote الحالي

# إذا لم يكن موجود يمكنك إضافة remote جديد
git remote add origin https://github.com/yourusername/kashf_system.git
git push -u origin main

# أو إذا أردت النسخ المحلي فقط
git clone <local_path> <destination>
```

### النسخ اليدوي (بدون Git)

```bash
# ضغط مجلد المشروع كاملاً
# على Windows: كليك يمين على المجلد → Send to → Compressed (Zipped) folder
# أو استخدام 7-Zip/WinRAR

# اسم الملف: kashf_system_code_YYYY_MM_DD.zip
```

---

## 2️⃣ النسخ الاحتياطية للبيانات (Database Backup)

### من Laravel (الطريقة الموصى بها)

```bash
cd D:\laragon\www\kashf_system

# إنشاء dump للقاعدة
php artisan db:dump

# سيُنشئ ملف في: storage/dumps/
# نسخه إلى مكان آمن
```

### من MySQL مباشرة (Laragon)

```bash
# باستخدام mysqldump
mysqldump -u root -p kashf_system > D:\Backups\kashf_system_DB_YYYY_MM_DD.sql

# بدون كلمة سر (إذا لم تكن موجودة)
mysqldump -u root kashf_system > D:\Backups\kashf_system_DB_YYYY_MM_DD.sql
```

### من phpMyAdmin (الطريقة الأسهل)

1. فتح phpMyAdmin: `http://localhost/phpmyadmin`
2. اختيار قاعدة البيانات `kashf_system`
3. اختيار **Export**
4. اختيار **SQL** كصيغة
5. الضغط على **Go** لتحميل الملف

---

## 3️⃣ ملف الإعدادات الأساسية

تأكد من حفظ هذه الملفات:

```
kashf_system/
├── .env                    ← (IMPORTANT) ملف الإعدادات
├── .env.example           ← نموذج الإعدادات
├── config/
│   ├── database.php        ← إعدادات قاعدة البيانات
│   ├── app.php            ← إعدادات التطبيق
│   └── ...
└── storage/
    ├── .gitignore         ← لا تنسخ الملفات المؤقتة
    └── logs/              ← السجلات (اختياري)
```

---

## 4️⃣ خطوات النقل إلى جهاز جديد

### المتطلبات:
- ✅ Laragon (أو XAMPP)
- ✅ PHP 8.2+
- ✅ MySQL/MariaDB
- ✅ Composer
- ✅ Node.js + npm

### الخطوات:

#### أ) استعادة الكود

```bash
# 1. نسخ المشروع إلى مجلد الويب
# D:\laragon\www\kashf_system

# 2. تثبيت المتطلبات
cd D:\laragon\www\kashf_system
composer install

# 3. تثبيت dependencies JavaScript
npm install

# 4. بناء الـ assets
npm run build
```

#### ب) نسخ الإعدادات

```bash
# 1. نسخ ملف .env من الملف الاحتياطي
# أو نسخه من .env.example وتعديل البيانات

# 2. تعديل المتغيرات الحساسة إذا لزم
# DB_HOST=localhost
# DB_USERNAME=root
# DB_PASSWORD=
# DB_DATABASE=kashf_system
```

#### ج) استعادة البيانات

```bash
# 1. إنشاء قاعدة بيانات جديدة
# باستخدام phpMyAdmin أو:
# mysql -u root -p
# > CREATE DATABASE kashf_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 2. استيراد الـ dump
mysql -u root kashf_system < kashf_system_DB_YYYY_MM_DD.sql

# أو من phpMyAdmin:
# - فتح قاعدة البيانات
# - اختيار Import
# - اختيار ملف SQL الاحتياطي
# - الضغط على Go
```

#### د) توليد مفتاح التطبيق

```bash
# إذا لم تعيّن APP_KEY في .env
php artisan key:generate

# تشغيل الهجرات (إذا لزم الأمر)
php artisan migrate

# زراعة البيانات الافتراضية (اختياري)
php artisan seed:run
```

#### هـ) اختبار التطبيق

```bash
# تشغيل الخادم
php artisan serve
# أو استخدم Laragon's built-in server

# فتح البرنامج
# http://localhost:8000
# أو http://kashf_system.test (إذا كان معرّفاً في Laragon)
```

---

## 5️⃣ جدول النسخ الاحتياطية الموصى به

| العنصر | التكرار | الا طريقة | المكان |
|--------|---------|---------|--------|
| **الكود** | شهري | Git push أو ZIP | GitHub أو Hard Drive backup |
| **قاعدة البيانات** | أسبوعي | mysqldump أو Export | Hard Drive backup |
| **ملف .env** | عند التعديل | نسخ يدوي | مكان آمن منفصل |
| **الملفات المرفوعة** | أسبوعي | ZIP | Hard Drive backup |

---

## 6️⃣ سكريبت أتمتة النسخ (Windows Batch)

أنشئ ملف `backup.bat` في مجلد البرنامج:

```batch
@echo off
setlocal enabledelayedexpansion

REM تحديد التاريخ
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c_%%a_%%b)
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a_%%b)

REM مسار المجلدات
set BACKUP_DIR=D:\Backups\kashf_system
set PROJECT_DIR=D:\laragon\www\kashf_system

REM إنشاء مجلد النسخ
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM نسخ الكود
echo Backing up code...
"C:\Program Files\7-Zip\7z.exe" a "%BACKUP_DIR%\code_%mydate%_%mytime%.zip" "%PROJECT_DIR%" -x!node_modules !vendor !storage\logs

REM نسخ قاعدة البيانات
echo Backing up database...
mysqldump -u root kashf_system > "%BACKUP_DIR%\database_%mydate%_%mytime%.sql"

echo Backup completed at %mydate% %mytime%
pause
```

---

## 7️⃣ ملاحظات مهمة

⚠️ **لا تنسخ هذه المجلدات:**
- `node_modules/` - سيتم إنشاؤه بـ `npm install`
- `vendor/` - سيتم إنشاؤه بـ `composer install`
- `storage/logs/` - السجلات المؤقتة
- `.git/` - إذا كنت تستخدم Git جديد

✅ **تأكد من نسخ:**
- `.env` (أو .env.example)
- `database/migrations/` و `seeders/`
- `config/` مع جميع الملفات
- `resources/` (views, js, css)
- `routes/` و `app/`

---

## 📝 مثال عملي كامل

```bash
# 1. نسخ احتياطي
mkdir D:\Backups\kashf_2026_02
mysqldump -u root kashf_system > D:\Backups\kashf_2026_02\db.sql
7z a D:\Backups\kashf_2026_02\code.zip D:\laragon\www\kashf_system

# 2. على الجهاز الجديد
# a) نسخ الملفات
unzip D:\Backups\kashf_2026_02\code.zip -d D:\laragon\www\

# b) تثبيت المتطلبات
cd D:\laragon\www\kashf_system
composer install
npm install
npm run build

# c) نسخ قاعدة البيانات
mysql -u root kashf_system < D:\Backups\kashf_2026_02\db.sql

# d) تشغيل
php artisan serve
```

---

إذا واجهت أي مشكلة، قل لي وسأساعدك!
