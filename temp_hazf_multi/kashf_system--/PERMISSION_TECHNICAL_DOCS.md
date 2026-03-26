# 🔐 دليل تقني لنظام الصلاحيات المحسّن

## 📍 موقع الملفات

```
kashf_system/
├── app/Http/Controllers/
│   ├── PayrollController.php        [MODIFIED] - فحص الصلاحيات
│   └── PermissionController.php     [NEW] ✅ - إدارة الصلاحيات
├── resources/views/
│   ├── payrolls/show.blade.php      [MODIFIED] - عرض الأزرار المشروطة
│   └── admin/permissions/           [NEW] ✅
│       ├── index.blade.php          - عرض المستخدمين
│       └── edit.blade.php           - تعديل الصلاحيات
├── routes/web.php                   [MODIFIED] - إضافة الروتس
└── PERMISSION_MANAGEMENT.md         [NEW] ✅ - دليل الاستخدام
```

## 🔧 التفاصيل التقنية

### 1. PermissionController.php
**المسار**: `app/Http/Controllers/PermissionController.php`

```php
class PermissionController extends Controller
{
    // Middleware: يفحص أن المستخدم لديه صلاحية admin أو manage-settings
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole('admin') && 
                !Auth::user()->can('manage-settings')) {
                abort(403, 'غير مصرح');
            }
            return $next($request);
        });
    }

    // Methods:
    public function index()          // GET /admin/permissions
    public function edit($userId)    // GET /admin/permissions/{user}
    public function update()         // PUT /admin/permissions/{user}
    public function grantPermission()    // POST /admin/permissions/{user}/grant
    public function revokePermission()   // POST /admin/permissions/{user}/revoke
    public function assignRole()         // POST /admin/permissions/{user}/assign-role
    public function removeRole()         // POST /admin/permissions/{user}/remove-role
}
```

### 2. PayrollController.php - فحص الصلاحيات
**المسار**: `app/Http/Controllers/PayrollController.php`  
**الأسطر المعدلة**: 85-127 و 769-777

```php
// Method جديد
private function canEditPayroll(Payroll $payroll): bool
{
    // يسمح بالتعديل إذا:
    // 1. المستخدم من نفس القسم AND لديه صلاحية edit-payrolls
    // أو
    // 2. المستخدم لديه صلاحية manage-settings أو دور admin
    
    if ($payroll->created_by_department_id !== Auth::user()->department_id) {
        return Auth::user()->hasAnyPermission(['manage-settings']) 
            || Auth::user()->hasRole('admin');
    }
    return Auth::user()->can('edit-payrolls');
}

private function canDeletePayroll(Payroll $payroll): bool
{
    // نفس الفحص أعلاه
    return $this->canEditPayroll($payroll);
}

// في method show()
$canEditPayrolls = [];
foreach ($payrolls as $p) {
    $canEditPayrolls[$p->id] = $this->canEditPayroll($p);
}

return view('payrolls.show', compact(..., 'canEditPayrolls'));
```

### 3. show.blade.php - تطبيق الفحص
**المسار**: `resources/views/payrolls/show.blade.php`

```blade
@foreach($payrolls as $p)
    <tr>
        {{-- ... الأعمدة الأخرى ... --}}
        <td class="action-buttons">
            @if(isset($canEditPayrolls[$p->id]) && $canEditPayrolls[$p->id])
                <!-- المستخدم لديه صلاحية -->
                <a href="{{ route('payrolls.edit', $p->id) }}" 
                   class="btn btn-primary btn-sm">
                    ✏️ تعديل
                </a>
                <button form="delete-form-{{ $p->id }}" 
                        type="submit" class="btn btn-danger btn-sm">
                    🗑️ حذف
                </button>
            @else
                <!-- المستخدم لا يملك صلاحية -->
                <button disabled class="btn btn-secondary btn-sm" 
                        title="🔒 صلاحيات محدودة">
                    🔒 محدود
                </button>
            @endif
        </td>
    </tr>
@endforeach
```

### 4. الروتس الجديدة
**المسار**: `routes/web.php`

```php
// Import الـ Controller
use App\Http\Controllers\PermissionController;

// المجموعة الجديدة
Route::middleware(['permission:manage-users|manage-settings'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index');
        
        Route::get('/permissions/{user}', [PermissionController::class, 'edit'])
            ->name('permissions.edit');
        
        Route::put('/permissions/{user}', [PermissionController::class, 'update'])
            ->name('permissions.update');
        
        // AJAX endpoints
        Route::post('/permissions/{user}/grant', 
                    [PermissionController::class, 'grantPermission'])
            ->name('permissions.grant');
        
        Route::post('/permissions/{user}/revoke', 
                    [PermissionController::class, 'revokePermission'])
            ->name('permissions.revoke');
        
        Route::post('/permissions/{user}/assign-role', 
                    [PermissionController::class, 'assignRole'])
            ->name('permissions.assign-role');
        
        Route::post('/permissions/{user}/remove-role', 
                    [PermissionController::class, 'removeRole'])
            ->name('permissions.remove-role');
    });
```

## 🔄 سير العمل (Frontend)

### صفحة index.blade.php
```html
┌─────────────────────────────────────────────┐
│ جدول بجميع المستخدمين                      │
├─────────────────────────────────────────────┤
│ # │ الاسم  │ البريد     │ الأدوار │ الإجراءات│
├─────────────────────────────────────────────┤
│ 1 │ أحمد  │ a@example │ 🔵 مدير │ ✏️ تعديل│
│ 2 │ محمد  │ m@example │ 🔵 مراقب│ ✏️ تعديل│
└─────────────────────────────────────────────┘
```

### صفحة edit.blade.php
```html
┌──────────────────────────────────────┐
│ تعديل صلاحيات: أحمد                  │
├──────────────────────────────────────┤
│ الأدوار:                             │
│ ☐ admin    ☐ user    ☐ manager     │
├──────────────────────────────────────┤
│ الصلاحيات (عرض البيانات):           │
│ ☑ access-dashboard                  │
│ ☑ view-payrolls                     │
├──────────────────────────────────────┤
│ الصلاحيات (التعديل):                │
│ ☑ create-payrolls                   │
│ ☑ edit-payrolls                     │
│ ☐ delete-payrolls                   │
├──────────────────────────────────────┤
│ [حفظ]  [إلغاء]                       │
└──────────────────────────────────────┘
```

## 🗄️ جداول قاعدة البيانات

### الجداول المستخدمة:

#### 1. permissions
```sql
id | name | description
1  | view-payrolls | عرض الكشوفات
2  | create-payrolls | إنشاء كشف
3  | edit-payrolls | تعديل كشف
...
```

#### 2. roles
```sql
id | name | description
1  | admin | مسؤول النظام
2  | user | مستخدم عادي
3  | manager | مدير قسم
```

#### 3. role_has_permissions
```sql
permission_id | role_id
1 | 1  (view-payrolls → admin)
2 | 1  (create-payrolls → admin)
...
```

#### 4. model_has_permissions
```sql
permission_id | model_type | model_id
1 | App\Models\User | 5  (المستخدم 5 له view-payrolls)
2 | App\Models\User | 5  (المستخدم 5 له create-payrolls)
```

#### 5. model_has_roles
```sql
role_id | model_type | model_id
1 | App\Models\User | 3  (المستخدم 3 له دور admin)
2 | App\Models\User | 5  (المستخدم 5 له دور user)
```

## 🧪 اختبار الصلاحيات برمجياً

### في Tinker:
```php
// إعطاء صلاحية لمستخدم
$user = User::find(2)
$user->givePermissionTo('edit-payrolls')

// التحقق من الصلاحية
$user->can('edit-payrolls')  // true

// إزالة صلاحية
$user->revokePermissionTo('edit-payrolls')

// إعطاء دور
$user->assignRole('admin')

// التحقق من الدور
$user->hasRole('admin')  // true
```

### في Blade:
```blade
@can('edit-payrolls')
    هذا امامك يرى فقط المستخدمون بـ edit-payrolls
@endcan

@canany(['edit-payrolls', 'delete-payrolls'])
    هذا امامك يرى من لديه edit أو delete
@endcanany

@if(auth()->user()->hasRole('admin'))
    فقط الـ admins
@endif
```

## 📡 API Endpoints (AJAX)

### 1. إعطاء صلاحية
```
POST /admin/permissions/{user-id}/grant
Content-Type: application/json

{
    "permission": 3  // permission id
}

Response:
{
    "success": true,
    "message": "تم إعطاء صلاحية 'edit-payrolls' للمستخدم 'أحمد' ✅"
}
```

### 2. إزالة صلاحية
```
POST /admin/permissions/{user-id}/revoke
Content-Type: application/json

{
    "permission": 3
}

Response:
{
    "success": true,
    "message": "تم إزالة صلاحية من المستخدم ✅"
}
```

### 3. إعطاء دور
```
POST /admin/permissions/{user-id}/assign-role
Content-Type: application/json

{
    "role": 1  // role id
}
```

### 4. إزالة دور
```
POST /admin/permissions/{user-id}/remove-role
Content-Type: application/json

{
    "role": 1
}
```

## 🔐 الأمان

### Middleware:
```php
// في routes/web.php
Route::middleware(['auth', 'permission:manage-users|manage-settings'])
```

يفحص:
1. المستخدم مسجل دخول ✓
2. المستخدم لديه manage-users أو manage-settings ✓

### في Controller:
```php
public function __construct()
{
    // فحص إضافي في الـ constructor
    $this->middleware(function ($request, $next) {
        if (!Auth::user()->hasRole('admin') && 
            !Auth::user()->can('manage-settings')) {
            abort(403);
        }
        return $next($request);
    });
}
```

### في View:
```blade
@if($canEditPayrolls[$p->id])
    <!-- أزرار مرئية -->
@else
    <!-- الأزرار مخفية -->
@endif
```

## 📊 خريطة التدفق الكاملة

```
┌─────────────────────────────────┐
│ المستخدم يزور /admin/permissions│
└──────────────┬──────────────────┘
               ⬇️
         ┌──────────────┐
         │ Middleware:  │
         │ auth         │ ✓ مسجل دخول؟
         │ manage-users │ ✓ لديه صلاحية؟
         │ manage-settings
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ Constructor: │
         │ فحص إضافي    │ ✓ admin أو manage-settings؟
         │ للـ          │
         │ Permission   │
         │ Controller   │
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ index()      │
         │ method       │ جلب جميع المستخدمين
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ View:        │
         │ index.blade  │ عرض الجدول
         │ .php         │
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ المستخدم:    │
         │ ينقر تعديل   │
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ edit()       │
         │ method       │ جلب البيانات الحالية
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ View:        │
         │ edit.blade   │ عرض checkboxes
         │ .php         │
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ المستخدم:    │
         │ يضع علامات   │
         │ وينقر حفظ    │
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ update()     │
         │ method       │ sync الأدوار والصلاحيات
         │              │ في Database
         └──────────────┘
               ⬇️
         ┌──────────────┐
         │ رد الصفحة    │ مع رسالة نجاح
         │ redirect()   │ ✅
         └──────────────┘
```

## 🎯 الأداء والتحسينات المستقبلية

### التحسينات الحالية:
- ✅ Eager loading: `with('permissions', 'roles')`
- ✅ فحص الصلاحيات في view level (بدون تحميل صفحة)
- ✅ AJAX endpoints للعمليات السريعة

### تحسينات مستقبلية ممكنة:
- [ ] Caching للصلاحيات (يستخدم في Spatie تلقائياً)
- [ ] Bulk permission assignment
- [ ] Permission audit log
- [ ] Role templates (نماذج جاهزة)
- [ ] Two-factor auth للعمليات الحساسة

---

**آخر تحديث**: اليوم  
**المطور**: AI Assistant  
**الحالة**: ✅ مختبر وطبق على الإنتاج
