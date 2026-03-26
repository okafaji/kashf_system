# اختبار حساب الإيفاد - خارج البلد

## خطوات الاختبار في المتصفح:

### 1️⃣ **فتح أدوات المطور**
- اضغط `F12` أو `Ctrl+Shift+I` (في كل المتصفحات)
- اختر التبويب **Console**

### 2️⃣ **تحقق من تحميل البيانات**
في console اكتب:
```javascript
console.log('عدد الإيفادات المحملة:', window.missionRates.length);
console.log('أول إيفاد:', window.missionRates[0]);
console.log('شرح البيانات:');
window.missionRates.slice(0, 5).forEach(m => {
    console.log(`  - ${m.name} | ${m.responsibility_level} | ${m.daily_rate}`);
});
```

**النتيجة المتوقعة:**
- عدد الإيفادات: 40
- أول إيفاد: خارج القطر/1 | منتسب | 30000

---

### 3️⃣ **اختبر الحساب اليدوي**
في console:
```javascript
// ابحث عن: خارج القطر/1 مع مستوى "مسؤول وجبة"
const test = window.missionRates.find(m => 
    m.name === 'خارج القطر/1' && m.responsibility_level === 'مسؤول وجبة'
);
console.log('النتيجة:', test);
```

**النتيجة المتوقعة:**
```
{
    name: "خارج القطر/1",
    responsibility_level: "مسؤول وجبة",
    daily_rate: 35000
}
```

---

### 4️⃣ **اختبر النموذج مباشرة**

**أ) إضافة موظف:**
1. أدخل اسم موظف واختره من القائمة
2. اختر **خارج القطر/1** من قائمة الوجهات

**ب) تحقق من ظهور حقل المستوى**
- بعد اختيار "خارج القطر"، يجب أن يختفي حقل "العنوان الوظيفي"
- ويظهر بدلاً منه حقل "المستوى الوظيفي"

**ج) اختر مستوى وظيفي**
- اختر **مسؤول وجبة** من القائمة
- يجب أن يتحدث حقل "مبلغ الإيفاد اليومي" الى **35000**

---

### 5️⃣ **إذا لم يعمل:**

**تحقق من:**

```javascript
// هل حقل الاختيار موجود؟
console.log('عدد صفوف الجدول:', $('tr.payroll-row').length);

// هل الحقل مرئي؟
const $level = $('.js-responsibility-level:visible');
console.log('عدد حقول المستوى المرئية:', $level.length);
console.log('قيمة الحقل:', $level.val());

// افحص القيمة المحفوظة
console.log('الإيفاد المختار:', $('.js-city-id').val());
console.log('المستوى المختار:', $('.js-responsibility-level').val());
```

---

### 📊 **النتائج المتوقعة للأسعار:**

| الإيفاد | المستوى | السعر |
|--------|---------|-------|
| خارج القطر/1 | منتسب | 30,000 |
| خارج القطر/1 | مسؤول وجبة | **35,000** |
| خارج القطر/2 | مسؤول وجبة | 35,000 |
| خارج القطر/2 | مسؤول وحدة | 55,000 |
| خارج القطر/3 | مسؤول وحدة | 65,000 |

---

### 🔧 **إذا واجهت مشاكل:**

**في Console اطبع:**
```javascript
// افحص كل الأخطاء
console.clear();
console.log('=== تشخيص الأخطاء ===');
console.log('✅ missionRates محمل:', !!window.missionRates);
console.log('✅ عدد السجلات:', window.missionRates.length);
console.log('✅ jQuery متاح:', !!$);
console.log('✅ دالة updateMissionRateFromLevel موجودة:', typeof updateMissionRateFromLevel);

// اختبر الدالة مباشرة
$('.payroll-row:first').find('.js-responsibility-level').val('مسؤول وجبة').change();
console.log('الآن افحص قيمة الإيفاد اليومي:', $('.js-daily-allowance').val());
```

**ثم انسخ وألصق النتائج في مساعدتي** ✅
