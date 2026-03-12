# 📖 دليل استرجاع النسخة الاحتياطية

## ✅ ضمان الاستبعادات الآمنة

جميع المجلدات المستبعدة من النسخة الاحتياطية **آمنة 100%** ولا تؤثر على استرجاع البرنامج!

---

## 📊 المجلدات المستبعدة وكيفية استعادتها

| المجلد المستبعد | الحجم | كيف يتم استعادته؟ | آمن؟ |
|-----------------|-------|-------------------|------|
| `vendor/` | ~200 MB | `composer install` | ✅ نعم |
| `node_modules/` | ~300 MB | `npm install` | ✅ نعم |
| `storage/framework/cache/` | ~530 MB | يُنشأ تلقائياً بواسطة Laravel | ✅ نعم |
| `storage/framework/sessions/` | صغير | يُنشأ تلقائياً بواسطة Laravel | ✅ نعم |
| `storage/framework/testing/` | صغير | يُنشأ تلقائياً بواسطة Laravel | ✅ نعم |
| `storage/logs/` | ~5 MB | يُنشأ تلقائياً بواسطة Laravel | ✅ نعم |
| `storage/backups/` | متغير | غير مطلوب (النسخ القديمة) | ✅ نعم |
| `bootstrap/cache/` | صغير | يُنشأ تلقائياً بواسطة Laravel | ✅ نعم |
| `.git/` | متغير | غير مطلوب للتشغيل | ✅ نعم |

---

## 🔄 عملية الاستعادة الكاملة

### الطريقة الأولى: استخدام restore.bat (موصى بها)

```bash
# تشغيل ملف الاستعادة
restore.bat
```

الملف يقوم **تلقائياً** بـ:

1. ✅ استخراج الكود
2. ✅ استعادة قاعدة البيانات
3. ✅ إنشاء المجلدات المطلوبة
4. ✅ تثبيت مكتبات PHP (composer install)
5. ✅ تثبيت مكتبات Node (npm install)
6. ✅ بناء الأصول (npm run build)
7. ✅ تحسين Laravel (cache, routes, views)
8. ✅ إنشاء الرابط الرمزي للملفات

---

### الطريقة الثانية: يدوياً (للتحكم الكامل)

#### 1️⃣ استخراج الكود
```bash
# فك ضغط ملف code_*.zip إلى مجلد المشروع
Expand-Archive -Path "D:\Backups\backup_2026_02_24\code_*.zip" -DestinationPath "D:\laragon\www"
```

#### 2️⃣ استعادة قاعدة البيانات
```bash
# حذف القاعدة القديمة وإنشاء جديدة
mysql -u root -e "DROP DATABASE IF EXISTS kashf_system; CREATE DATABASE kashf_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# استيراد البيانات
mysql -u root kashf_system < "D:\Backups\backup_2026_02_24\database_*.sql"
```

#### 3️⃣ إنشاء المجلدات المطلوبة
```bash
cd D:\laragon\www\kashf_system

# إنشاء مجلدات storage
New-Item -ItemType Directory -Force -Path "storage\logs"
New-Item -ItemType Directory -Force -Path "storage\framework\cache"
New-Item -ItemType Directory -Force -Path "storage\framework\sessions"
New-Item -ItemType Directory -Force -Path "storage\framework\testing"
New-Item -ItemType Directory -Force -Path "storage\framework\views"
New-Item -ItemType Directory -Force -Path "bootstrap\cache"

# منح الصلاحيات (على Linux/Mac)
# chmod -R 775 storage bootstrap/cache
```

#### 4️⃣ تثبيت المكتبات
```bash
# تثبيت مكتبات PHP
composer install --no-dev --optimize-autoloader

# تثبيت مكتبات Node.js
npm install --production

# بناء الأصول
npm run build
```

#### 5️⃣ تحسين Laravel
```bash
# إنشاء الرابط الرمزي للملفات
php artisan storage:link

# تنظيف وإنشاء الكاش
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 6️⃣ التحقق من الإعدادات
```bash
# التأكد من ملف .env
# تحديث APP_KEY إذا لزم الأمر
php artisan key:generate

# اختبار الاتصال بقاعدة البيانات
php artisan migrate:status
```

---

## ⚠️ ملاحظات مهمة

### 1. ملف .env
ملف `.env` **موجود** في النسخة الاحتياطية، لكن قد تحتاج لتعديل:
- `DB_HOST` - إذا كان الخادم مختلف
- `DB_DATABASE` - اسم قاعدة البيانات
- `DB_USERNAME` و `DB_PASSWORD` - بيانات الاتصال
- `APP_URL` - عنوان الموقع الجديد

### 2. الملفات المرفوعة
جميع الملفات في `storage/app/public` و `storage/xls` **محفوظة** في النسخة!

### 3. هيكل المجلدات
Laravel يتطلب **وجود المجلدات** حتى لو كانت فارغة. ملف `restore.bat` يُنشئها تلقائياً.

---

## 🧪 اختبار النسخة المستعادة

بعد الاستعادة، اختبر البرنامج:

```bash
# تشغيل السيرفر
php artisan serve

# فتح المتصفح
# http://localhost:8000

# تسجيل الدخول باستخدام:
# البريد: admin@kashf.com
# كلمة المرور: password
```

---

## 📦 حجم النسخة الاحتياطية

### قبل الاستبعاد:
- المشروع الكامل: ~1000 MB
- وقت النسخ: ~5-10 دقائق
- وقت الاستعادة: ~10-15 دقيقة

### بعد الاستبعاد:
- النسخة الاحتياطية: **~60-120 MB فقط!**
- وقت النسخ: **~30-60 ثانية**
- وقت الاستعادة: **~2-3 دقائق**

**التوفير: 85% من المساحة والوقت!** 🚀

---

## ✅ الخلاصة

### هل الاستبعادات آمنة؟
**نعم 100%!** 

**السبب:**
1. ✅ `vendor/` و `node_modules/` يتم تثبيتهم من `composer.json` و `package.json`
2. ✅ مجلدات `storage/framework/cache` وغيرها تُنشأ تلقائياً
3. ✅ الملفات **المهمة فقط** محفوظة (الكود، قاعدة البيانات، الملفات المرفوعة)
4. ✅ ملف `restore.bat` يُعيد بناء كل شيء تلقائياً

### الضمانات:
- ✅ جميع الأكواد البرمجية محفوظة
- ✅ قاعدة البيانات كاملة محفوظة
- ✅ ملفات Excel المرفوعة محفوظة
- ✅ ملفات المستخدمين في `storage/app` محفوظة
- ✅ ملف `.env` وجميع الإعدادات محفوظة

**لا يوجد أي تأثير سلبي على استرجاع البرنامج!** 

البرنامج سيعمل **بنفس الطريقة تماماً** كما كان قبل النسخ الاحتياطي! ✅

---

## 🆘 استكشاف الأخطاء

### المشكلة: "Class not found"
**الحل:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### المشكلة: "No application encryption key"
**الحل:**
```bash
php artisan key:generate
```

### المشكلة: "SQLSTATE connection refused"
**الحل:**
- تأكد من تشغيل MySQL
- تحقق من إعدادات `.env`

### المشكلة: "Permission denied" على storage
**الحل (Windows):**
```powershell
icacls "storage" /grant Everyone:F /T
icacls "bootstrap\cache" /grant Everyone:F /T
```

**الحل (Linux/Mac):**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📞 دعم إضافي

إذا واجهت أي مشكلة أثناء الاستعادة:
1. راجع ملف `storage/logs/laravel.log`
2. تأكد من تشغيل جميع الخطوات بالترتيب
3. استخدم `restore.bat` للاستعادة التلقائية

---

**آخر تحديث: 24 فبراير 2026**
