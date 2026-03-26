# 👨‍💻 مرجع سريع للمطورين - Team Statistics

## 🔍 نقاط مهمة

### Fetch Requests - الطريقة الصحيحة
```javascript
// ✅ الطريقة الصحيحة:
fetch('/api/endpoint', {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin'  // ⭐ يرسل cookies
})
.then(response => {
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
})
.then(data => {
    console.log('✓ بيانات مستلمة:', data);
    // process data
})
.catch(error => {
    console.error('❌ خطأ:', error);
});
```

### Authorization & Permissions
```php
// ✅ الطريقة الصحيحة للتحقق من الأدوار:
@if(auth()->user()->hasRole(['admin', 'رئيس قسم', 'مسؤول شعبة', 'مسؤول وحدة']))
    <!-- رمز يراه المستخدم -->
@endif

// أو في C laravel:
if (Auth::user()->hasRole(['admin', 'رئيس قسم'])) {
    // سماح بالوصول
}
```

### localStorage - Tab Persistence
```javascript
// حفظ الاختيار:
localStorage.setItem('selectedDashboardTab', 'team-stats');

// استرجاع:
const savedTab = localStorage.getItem('selectedDashboardTab') || 'my-stats';

// حذف:
localStorage.removeItem('selectedDashboardTab');
```

---

## ⚠️ الأخطاء الشائعة

| الخطأ | السبب | الحل |
|------|------|------|
| `Cannot read property 'addEventListener' of null` | العنصر غير موجود في DOM | تحقق من `id="element"` |
| `HTTP 401 Unauthorized` | بدون credentials | أضف `credentials: 'same-origin'` |
| `HTTP 403 Forbidden` | بدون صلاحيات | تحقق من الأدوار والـ authorization |
| البيانات تعرض 0 | العنصر HTML غير موجود | تحقق من `document.getElementById()` |

---

## 🐛 Debugging Tips

### في Browser Console:
```javascript
// تحقق من وجود العنصر
document.getElementById('teamStatTotalPayrolls')

// تحقق من قيمة localStorage
localStorage.getItem('selectedDashboardTab')

// اختبر الـ API مباشرة
fetch('/api/user-stats')
    .then(r => r.json())
    .then(d => console.log(d))

// اختبر العنصر يحتوي على القيمة
document.getElementById('teamStatTotalPayrolls').textContent
```

---

## 📋 Checklist قبل الـ Deployment

- [ ] جميع الـ API endpoints تعيد status 200
- [ ] جميع الـ fetch requests لها `credentials: 'same-origin'`
- [ ] جميع الـ fetch requests لها `response.ok` check
- [ ] جميع الـ element selectors موجودة في HTML
- [ ] جميع الـ authorization checks صحيحة
- [ ] localStorage يعمل بشكل صحيح
- [ ] console.log messages مفيدة للتصحيح
- [ ] tested in Chrome, Firefox, Safari

---

## 🔗 الملفات المتعلقة

```
Routes:
  └── routes/api.php (user-stats endpoints)

Controllers:
  └── app/Http/Controllers/UserStatsController.php

Views:
  └── resources/views/dashboard.blade.php
      ├── HTML for team stats
      └── JavaScript logic

Models:
  └── app/Models/Payroll.php
```

---

## 📞 الدعم

للأسئلة أو المشاكل:
1. افتح DevTools (F12)
2. تحقق من Console عن الأخطاء
3. تحقق من Network عن الـ API responses
4. اقرأ الملف `TEAM_STATS_VERIFICATION_STEPS.md`

---

## 🎓 مراجع إضافية

- [MDN - Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Browser localStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage)
