<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\City;
use App\Models\Employee;
use App\Models\MissionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Signature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PayrollController extends Controller
{
    // تحويل رقم Excel serial date إلى نص تاريخ ميلادي
    private function excelSerialToDate($serial)
    {
        if (is_numeric($serial)) {
            $unix = ($serial - 25569) * 86400;
            return gmdate('Y-m-d', $unix);
        }
        return $serial;
    }
    private const MAX_DAYS_COUNT = 365;
    private const MAX_DAILY_ALLOWANCE = 2000000;
    private const MAX_TOTAL_AMOUNT = 100000000;

    // عرض صفحة الإدخال
    public function create()
        {
            $departments = \App\Models\Department::all();
            $governorates = \App\Models\Governorate::all();
            $missionTypes = MissionType::getMissionTypeNames();
            $responsibilityLevels = MissionType::getResponsibilityLevels();
            return view('payrolls.create', compact('departments', 'governorates', 'missionTypes', 'responsibilityLevels'));
        }

    /**
     * Get mission rate for given type and responsibility level
     */
    public function getMissionRate(Request $request)
    {
        $missionType = $request->input('mission_type');
        $responsibilityLevel = $request->input('responsibility_level');

        $rate = MissionType::getRate($missionType, $responsibilityLevel);

        return response()->json([
            'success' => true,
            'rate' => $rate
        ]);
    }

    private function normalizeOutsideDestinationName(?string $destination): string
    {
        $value = trim((string) $destination);
        if ($value === '') {
            return '';
        }

        $missionOnly = trim(explode(' - ', $value)[0] ?? '');
        if (preg_match('/^خارج\s+القطر\s*[\/\s]?\s*(\d+)$/u', $missionOnly, $matches)) {
            return 'خارج القطر/' . $matches[1];
        }

        return $missionOnly;
    }

    private function findMissionTypeRecord(string $destination, string $responsibilityLevel): ?MissionType
    {
        $normalizedDestination = $this->normalizeOutsideDestinationName($destination);

        return MissionType::query()
            ->whereIn('name', [
                $normalizedDestination,
                str_replace('/', ' ', $normalizedDestination),
            ])
            ->where('responsibility_level', $responsibilityLevel)
            ->first();
    }

    private function assertDepartmentScope(Payroll $payroll): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403, 'غير مصرح.');
        }

        if ($this->hasGlobalPayrollAccess($user) || $this->hasCrossDepartmentEditAccess($user)) {
            return;
        }

        if (empty($user->department_id) || empty($payroll->created_by_department_id)) {
            return;
        }

        if ((int) $user->department_id !== (int) $payroll->created_by_department_id) {
            abort(403, 'لا تملك صلاحية تعديل/حذف كشوفات قسم آخر.');
        }
    }

    private function hasCrossDepartmentEditAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return method_exists($user, 'can') ? (bool) $user->can('edit-payrolls') : false;
    }

    private function hasGlobalPayrollAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $canManageSettings = method_exists($user, 'can') ? (bool) $user->can('manage-settings') : false;
        $isAdmin = method_exists($user, 'hasRole') ? (bool) $user->hasRole('admin') : false;

        return $canManageSettings || $isAdmin;
    }

    private function canEditPayroll(Payroll $payroll): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Admin or manage-settings permission can edit any payroll
        if ($this->hasGlobalPayrollAccess($user) || $this->hasCrossDepartmentEditAccess($user)) {
            return true;
        }

        // Check department scope
        if (empty($user->department_id) || empty($payroll->created_by_department_id)) {
            return true; // سماح إذا كان أحد الـ department_id فارغ
        }

        return (int) $user->department_id === (int) $payroll->created_by_department_id;
    }

    private function canDeletePayroll(Payroll $payroll): bool
    {
        // نفس صلاحيات التعديل للحذف
        return $this->canEditPayroll($payroll);
    }

    private function hasStatusColumn(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $cached = Schema::hasColumn('payrolls', 'status');
        }

        return $cached;
    }

    private function ensureReasonablePayrollValues(float $dailyAllowance, float $totalAmount, int $daysCount): ?string
    {
        if ($daysCount < 1 || $daysCount > self::MAX_DAYS_COUNT) {
            return 'عدد أيام الإيفاد غير منطقي. الحد الأقصى المسموح هو ' . self::MAX_DAYS_COUNT . ' يوم.';
        }

        if ($dailyAllowance < 0 || $dailyAllowance > self::MAX_DAILY_ALLOWANCE) {
            return 'المبلغ اليومي خارج الحدود المسموحة.';
        }

        if ($totalAmount < 0 || $totalAmount > self::MAX_TOTAL_AMOUNT) {
            return 'المبلغ الكلي خارج الحدود المسموحة.';
        }

        return null;
    }

    private function logPayrollAudit(
        string $action,
        ?Payroll $payroll,
        ?array $oldValues,
        ?array $newValues,
        ?string $description = null
    ): void {
        try {
            $user = Auth::user();

            PayrollAuditLog::create([
                'payroll_id' => $payroll?->id,
                'kashf_no' => $payroll?->kashf_no,
                'action' => $action,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('تعذر تسجيل audit log', [
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function auditFieldLabels(): array
    {
        return [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'employee_id' => 'الرقم الوظيفي',
            'department' => 'القسم',
            'destination' => 'الوجهة',
            'governorate_id' => 'المحافظة',
            'job_title' => 'العنوان الوظيفي',
            'admin_order_no' => 'رقم الأمر الإداري',
            'receipt_no' => 'رقم الوصل',
            'admin_order_date' => 'تاريخ الأمر الإداري',
            'start_date' => 'تاريخ بداية الإيفاد',
            'end_date' => 'تاريخ نهاية الإيفاد',
            'days_count' => 'عدد الأيام',
            'daily_allowance' => 'مبلغ اليومية',
            'accommodation_fee' => 'مبلغ المبيت',
            'is_half_allowance' => 'نسبة الإيفاد',
            'mission_type_id' => 'نوع الإيفاد',
            'city_id' => 'المدينة',
            'transportation_fee' => 'أجور النقل',
            'meals_count' => 'عدد الوجبات',
            'receipts_amount' => 'مبالغ الوصولات',
            'total_amount' => 'المبلغ الكلي',
            'kashf_no' => 'رقم الكشف',
            'order_year' => 'سنة الأمر',
            'group_no' => 'رقم المجموعة',
            'status' => 'الحالة',
            'is_archived' => 'الأرشفة',
            'notes' => 'الملاحظات',
            'user_id' => 'المستخدم',
            'created_by_department_id' => 'قسم الإنشاء',
        ];
    }

    private function formatAuditValueForDescription(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (in_array($key, ['is_archived'], true)) {
            return (int) $value === 1 ? 'نعم' : 'لا';
        }

        if ($key === 'is_half_allowance') {
            return (int) $value === 1 ? '50%' : '100%';
        }

        if ($key === 'status') {
            $labels = Payroll::statusLabels();
            return $labels[(string) $value] ?? (string) $value;
        }

        if (in_array($key, ['admin_order_date', 'start_date', 'end_date'], true)) {
            try {
                return \Carbon\Carbon::parse((string) $value)->format('Y/m/d');
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        }

        if (in_array($key, ['daily_allowance', 'accommodation_fee', 'transportation_fee', 'receipts_amount', 'total_amount'], true) && is_numeric($value)) {
            return number_format((float) $value);
        }

        return (string) $value;
    }

    private function buildAuditDescription(string $prefix, array $values, array $orderedKeys): string
    {
        $fieldLabels = $this->auditFieldLabels();
        $parts = [];

        foreach ($orderedKeys as $key) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            $formattedValue = $this->formatAuditValueForDescription($key, $values[$key]);
            if ($formattedValue === '-') {
                continue;
            }

            $parts[] = ($fieldLabels[$key] ?? $key) . ': ' . $formattedValue;
        }

        if (empty($parts)) {
            return $prefix;
        }

        return $prefix . ' - ' . implode('، ', $parts);
    }

    // حفظ البيانات (بديل زر الترحيل في VBA)
    public function store(Request $request) {
    // التحقق من البيانات (Validation) لضمان عدم إدخال قيم نصية في الحقول الرقمية
    $request->validate([
    'name' => 'required',
    'destination' => 'required',
    'start_date' => 'required|date',
    'end_date' => 'required|date',
]);

// حساب الأيام من التواريخ
    try {
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $days_count = $startDate->diffInDays($endDate) + 1;
    } catch (\Exception $e) {
        $days_count = 1;
    }

    $destination = $this->normalizeOutsideDestinationName($request->input('destination'));
    $receipts_amount = (float)$request->input('receipts_amount', 0);
    $is_half_allowance = (string)$request->input('is_half_allowance', '0') === '1';

    $data = $request->all();
    $data['days_count'] = $days_count;
    $data['is_half_allowance'] = $is_half_allowance;

    // التحقق من الوجهة: خارج القطر أم مدينة عادية
    $isOutsideCountry = strpos($destination, 'خارج القطر') === 0;

    // حساب daily_allowance = city_rate + mission_rate
    $cityRate = 0;
    $missionRate = 0;

    if ($isOutsideCountry) {
        // خارج القطر: city_rate = 0، mission_rate = السعر من جدول mission_types
        $responsibilityLevel = $request->input('responsibility_level', '');

        if (!$responsibilityLevel) {
            return redirect()->back()->withErrors([
                'responsibility_level' => 'يجب اختيار المستوى الوظيفي عند اختيار خارج القطر'
            ])->withInput();
        }

        $missionType = $this->findMissionTypeRecord($destination, $responsibilityLevel);
        if (!$missionType) {
            return redirect()->back()->withErrors([
                'destination' => 'لم يتم العثور على تسعيرة مطابقة لخارج القطر والمستوى الوظيفي المختار'
            ])->withInput();
        }

        $data['mission_type_id'] = $missionType->id;
        $missionRate = (float)$missionType->daily_rate;
        if ($is_half_allowance) {
            $missionRate = $missionRate / 2;
        }
        $cityRate = 0; // لا يوجد سعر مدينة
        $data['destination'] = $destination . ' - ' . $responsibilityLevel;
    } else {
        // مدينة عادية: mission_rate = 0، city_rate = السعر من جدول cities
        $city = City::where('name', $destination)->first();
        if ($city) {
            $cityRate = (float)$city->daily_allowance;

            // تطبيق خصم 50% على اليومية عند اختيار الحالة
            if ($is_half_allowance) {
                $cityRate = $cityRate / 2;
            }
        }
        $missionRate = 0; // لا يوجد سعر خارج القطر
    }

    // السعر اليومي الكلي = سعر المدينة + سعر خارج القطر
    $dailyAllowance = $cityRate + $missionRate;
    $data['daily_allowance'] = $dailyAllowance;

    // حساب المجموع الكلي
    $nights = $days_count > 1 ? $days_count - 1 : 0;

    // المبيت: ثابت 10,000 للمدن العادية، صفر للخارج
    if ($isOutsideCountry) {
        $accommodation_fee = 0;
    } else {
        $accommodation_fee = 10000;
    }

    $transportation_fee = (float)$request->input('transportation_fee', 0);
    $meals_count = (int)$request->input('meals_count', 0);

    if ($isOutsideCountry) {
        // خارج القطر: (أيام × السعر اليومي) + وصولات فقط
        $data['accommodation_fee'] = 0;
        $data['transportation_fee'] = 0;
        $data['meals_count'] = 0;
        $data['total_amount'] = ($days_count * $dailyAllowance) + $receipts_amount;
    } else {
        // مدينة عادية: (أيام × السعر) + (ليالي × مبيت ثابت 10,000) + نقل + وصولات - (وجبات × 10%)
        $data['accommodation_fee'] = 10000;
        $meals_deduction = $meals_count * ($dailyAllowance * 0.10);
        $data['total_amount'] = ($days_count * $dailyAllowance)
                              + ($nights * $accommodation_fee)
                              + $transportation_fee
                              + $receipts_amount
                              - $meals_deduction;
    }

    $sanityError = $this->ensureReasonablePayrollValues(
        (float) $data['daily_allowance'],
        (float) $data['total_amount'],
        (int) $days_count
    );
    if ($sanityError) {
        return redirect()->back()->withErrors(['amounts' => $sanityError])->withInput();
    }

    // إذا لم يختار المستخدم مدينة، نضع اسم المحافظة المختارة في حقل الوجهة
    if (!$request->filled('destination')) {
        $governorate = \App\Models\Governorate::find($request->input('governorate_id'));
        $data['destination'] = $governorate ? $governorate->name : 'غير محدد';
    }

    // تحقق من عدم تكرار رقم الأمر الإداري في نفس السنة
    if (!empty($request->admin_order_no) && $request->admin_order_no !== 'بدون') {
        // استخراج السنة من تاريخ الأمر الإداري
        try {
            $orderYear = \Carbon\Carbon::parse($request->admin_order_date ?? now())->year;
        } catch (\Exception $e) {
            $orderYear = date('Y');
        }

        $existingPayroll = Payroll::where('admin_order_no', $request->admin_order_no)
            ->where('order_year', $orderYear)
            ->first();

        if ($existingPayroll) {
            Log::warning('تم العثور على تكرار لرقم الأمر الإداري في نفس السنة.', [
                'admin_order_no' => $request->admin_order_no,
                'order_year' => $orderYear
            ]);
            return redirect()->back()->withErrors(['admin_order_no' => 'رقم الأمر الإداري ' . $request->admin_order_no . ' موجود بالفعل في السنة ' . $orderYear . '!']);
        }
    }

    // ===== التحقق من التداخل في فترات الإيفاد =====
    // منع نفس الموظف من أن يكون لديه فترات إيفاد متداخلة

    try {
        $newStart = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $newEnd = \Carbon\Carbon::parse($request->end_date)->startOfDay();
    } catch (\Exception $e) {
        return redirect()->back()->withErrors([
            'date_error' => 'خطأ في تحليل التواريخ. يرجى التأكد من صحة التواريخ المدخلة.'
        ])->withInput();
    }

    // التحقق من تداخل الفترات مع إيفادات أخرى لنفس الموظف
    $existingPayrolls = Payroll::where('name', $request->name)
        ->where('is_archived', false)
        ->get();

    foreach ($existingPayrolls as $existing) {
        try {
            $existingStart = \Carbon\Carbon::parse($existing->start_date)->startOfDay();
            $existingEnd = \Carbon\Carbon::parse($existing->end_date)->startOfDay();

            // شرط التداخل: (startA <= endB AND endA >= startB)
            if ($newStart <= $existingEnd && $newEnd >= $existingStart) {
                return redirect()->back()->withErrors([
                    'overlap' => 'يوجد تداخل في فترة الإيفاد! ' . $request->name .
                                ' لديه إيفاد آخر من ' . $existing->start_date . ' إلى ' . $existing->end_date .
                                ' (أمر إداري: ' . $existing->admin_order_no . ')' .
                                ' 📋 الكشف: ' . ($existing->kashf_no ?? $existing->receipt_no ?? 'غير محدد')
                ])->withInput();
            }
        } catch (\Exception $e) {
            continue;
        }
    }

    // إضافة السنة المستخرجة من تاريخ الأمر الإداري
    if (!empty($request->admin_order_no) && $request->admin_order_no !== 'بدون') {
        try {
            $data['order_year'] = \Carbon\Carbon::parse($request->admin_order_date ?? now())->year;
        } catch (\Exception $e) {
            $data['order_year'] = date('Y');
        }
    }

    // إضافة المستخدم الحالي والقسم
    $data['user_id'] = Auth::id();
    $data['created_by_department_id'] = Auth::user()->department_id;
    $data['is_archived'] = false;
    $data['status'] = Payroll::STATUS_READY_FOR_PRINT;

    // محاولة ربط الموظف بـ employee_id إذا كان موجوداً
    if (empty($data['employee_id']) && !empty($data['name'])) {
        // البحث عن موظف بنفس الاسم (كملاذ أخير)
        $employee = \App\Models\Employee::where('name', trim($data['name']))->first();
        if ($employee) {
            $data['employee_id'] = $employee->employee_id;
        }
    }

    $createdPayroll = Payroll::create($data);

    $createdDescription = $this->buildAuditDescription(
        'إنشاء كشف إيفاد جديد',
        $createdPayroll->only(['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']),
        ['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']
    );

    $this->logPayrollAudit(
        'created',
        $createdPayroll,
        null,
        $createdPayroll->only(['name', 'kashf_no', 'admin_order_no', 'status', 'total_amount']),
        $createdDescription
    );

    // نسخ احتياطي تلقائي عند إنشاء أول كشف في اليوم
    \App\Http\Controllers\BackupController::createAutomaticBackup();

    return redirect('/payrolls')->with('success', 'تم حفظ الإيفاد بنجاح');
}

// عرض صفحة التعديل
public function edit($id)
{
    $payroll = Payroll::findOrFail($id);
    $this->assertDepartmentScope($payroll);

    $departments = \App\Models\Department::all();
    $governorates = \App\Models\Governorate::with('cities')->get();
    $cities = \App\Models\City::all();

    // تحديد اليومية المختارة بناءً على نوع الإيفاد
    $selectedDaily = 0;
    if ($payroll->mission_type_id) {
        // خارج القطر: احسب من mission type (السعر الكامل)
        $missionType = MissionType::find($payroll->mission_type_id);
        if ($missionType) {
            $selectedDaily = (float)$missionType->daily_rate;
        } else {
            // احتياطي: إذا فُقد سجل التسعيرة، أعرض القيمة الكاملة اعتماداً على المخزن
            $selectedDaily = (float)$payroll->daily_allowance;
            if ($payroll->is_half_allowance) {
                $selectedDaily = $selectedDaily * 2;
            }
        }
    } else {
        // مدينة عادية: نجلب السعر الكامل من جدول المدن
        $selectedCity = City::where('name', $payroll->destination)->first();
        if ($selectedCity) {
            // السعر الكامل من جدول المدن - الـ JS يطبّق 50% تلقائياً
            $selectedDaily = (float)$selectedCity->daily_allowance;
        } else {
            // احتياطي: القيمة المخزونة في DB قد تكون محسوبة بعد 50%، نعيدها لكاملها
            $selectedDaily = (float)$payroll->daily_allowance;
            if ($payroll->is_half_allowance) {
                $selectedDaily = $selectedDaily * 2;
            }
        }
    }

    return view('payrolls.edit', compact('payroll', 'departments', 'governorates', 'cities', 'selectedDaily'));
}

// 1. تعديل دالة العرض (index) لتعرض فقط غير المؤرشف
public function index(Request $request)
{
    /** @var \App\Models\User|null $currentUser */
    $currentUser = Auth::user();

    $filters = [
        'search' => trim((string) $request->query('search', '')),
        'department' => trim((string) $request->query('department', '')),
        'status' => trim((string) $request->query('status', '')),
        'from_date' => trim((string) $request->query('from_date', '')),
        'to_date' => trim((string) $request->query('to_date', '')),
        'admin_order_no' => trim((string) $request->query('admin_order_no', '')),
        'created_by' => trim((string) $request->query('created_by', '')),
    ];

    $query = Payroll::query()
        ->whereNotNull('kashf_no')
        ->where('kashf_no', '!=', '');

    if ($currentUser && !$this->hasGlobalPayrollAccess($currentUser) && !empty($currentUser->department_id)) {
        $query->where('created_by_department_id', $currentUser->department_id);
    }

    if ($filters['search'] !== '') {
        $search = $filters['search'];
        $query->where(function ($innerQuery) use ($search) {
            $innerQuery->where('name', 'LIKE', "%{$search}%")
                ->orWhere('admin_order_no', 'LIKE', "%{$search}%")
                ->orWhere('kashf_no', 'LIKE', "%{$search}%");
        });
    }

    if ($filters['department'] !== '') {
        $query->where('department', $filters['department']);
    }

    if ($filters['admin_order_no'] !== '') {
        $query->where('admin_order_no', 'LIKE', '%' . $filters['admin_order_no'] . '%');
    }

    if ($filters['created_by'] !== '' && ctype_digit($filters['created_by'])) {
        $query->where('user_id', (int) $filters['created_by']);
    }

    if ($filters['from_date'] !== '') {
        $query->whereDate('created_at', '>=', $filters['from_date']);
    }

    if ($filters['to_date'] !== '') {
        $query->whereDate('created_at', '<=', $filters['to_date']);
    }

    if ($filters['status'] !== '') {
        switch ($filters['status']) {
            case Payroll::STATUS_ARCHIVED:
                $query->where('is_archived', true);
                break;
            case Payroll::STATUS_PRINTED:
                $query->where('status', Payroll::STATUS_PRINTED)->where('is_archived', false);
                break;
            case Payroll::STATUS_DRAFT:
                $query->where('status', Payroll::STATUS_DRAFT)->where('is_archived', false);
                break;
            case Payroll::STATUS_READY_FOR_PRINT:
                $query->where('status', Payroll::STATUS_READY_FOR_PRINT)->where('is_archived', false);
                break;
        }
    }

    $payrollGroups = $query
        ->select('kashf_no')
        ->selectRaw('COUNT(*) as employees_count')
        ->selectRaw('SUM(total_amount) as total_sum')
        ->selectRaw('MIN(start_date) as min_start_date')
        ->selectRaw('MAX(end_date) as max_end_date')
        ->selectRaw('MAX(admin_order_no) as admin_order_no')
        ->selectRaw('MAX(admin_order_date) as admin_order_date')
        ->selectRaw('MAX(created_at) as latest_created_at')
        ->selectRaw('MAX(is_archived) as is_archived')
        ->selectRaw("MAX(CASE
            WHEN is_archived = 1 THEN 4
            WHEN status = 'printed' THEN 3
            WHEN status = 'ready_for_print' THEN 2
            WHEN status = 'draft' THEN 1
            ELSE 0
        END) as status_rank")
        ->groupBy('kashf_no')
        ->orderByDesc('latest_created_at')
        ->paginate(10)
        ->appends($request->query());

    $statusFromRank = [
        1 => Payroll::STATUS_DRAFT,
        2 => Payroll::STATUS_READY_FOR_PRINT,
        3 => Payroll::STATUS_PRINTED,
        4 => Payroll::STATUS_ARCHIVED,
    ];

    $statusLabels = Payroll::statusLabels();
    $payrollGroups->getCollection()->transform(function ($group) use ($statusFromRank, $statusLabels) {
        $resolvedStatus = $statusFromRank[(int) ($group->status_rank ?? 0)] ?? Payroll::STATUS_READY_FOR_PRINT;
        $group->status = $resolvedStatus;
        $group->status_label = $statusLabels[$resolvedStatus] ?? $resolvedStatus;
        return $group;
    });

    $departmentOptions = Payroll::query()
        ->whereNotNull('department')
        ->where('department', '!=', '')
        ->distinct()
        ->orderBy('department')
        ->pluck('department');

    $creatorOptions = User::query()
        ->whereIn('id', Payroll::query()->whereNotNull('user_id')->select('user_id')->distinct())
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('payrolls.index', [
        'payrollGroups' => $payrollGroups,
        'filters' => $filters,
        'departmentOptions' => $departmentOptions,
        'creatorOptions' => $creatorOptions,
        'statusOptions' => $statusLabels,
    ]);
}

public function nameSuggestions(Request $request)
{
    $term = trim((string) $request->query('term', ''));
    if ($term === '') {
        return response()->json([]);
    }

    $names = Payroll::where('name', 'LIKE', "%{$term}%")
        ->select('name')
        ->distinct()
        ->orderBy('name')
        ->limit(20)
        ->pluck('name');

    $results = $names->map(function ($name) {
        return [
            'id' => $name,
            'text' => $name,
        ];
    })->values();

    return response()->json($results);
}

public function statsByName(Request $request)
{
    $name = trim((string) $request->query('name', ''));
    if ($name === '') {
        return redirect()->route('payrolls.index')->with('error', 'يرجى اختيار الاسم الكامل من الاقتراحات.');
    }

    $records = Payroll::where('name', $name)
        ->orderBy('start_date', 'desc')
        ->get();

    if ($records->isEmpty()) {
        return redirect()->route('payrolls.index')->with('error', 'لا توجد نتائج مطابقة لهذا الاسم.');
    }

    $stats = [
        'count' => $records->count(),
        'total' => $records->sum('total_amount'),
        'last_end_date' => $records->max('end_date'),
        'first_start_date' => $records->min('start_date'),
    ];

    return view('payrolls.stats', compact('name', 'records', 'stats'));
}

// عرض تفاصيل كشف معين (جميع الأسماء في المجموعة)
public function show($kashf_no)
{
    // تنظيف رقم الكشف من المسافات
    $kashf_no = trim($kashf_no);

    Log::info('show: محاولة عرض كشف رقم: ' . $kashf_no);

    $payrolls = Payroll::where('kashf_no', $kashf_no)
        ->orderBy('created_at', 'asc')
        ->get();

    Log::info('show: عدد السجلات الموجودة: ' . $payrolls->count());

    if ($payrolls->isEmpty()) {
        Log::warning('show: لم يتم العثور على سجلات للكشف: ' . $kashf_no);

        // التحقق من وجود الكشف في قاعدة البيانات بأي حال
        $allKashfNos = Payroll::select('kashf_no')->distinct()->pluck('kashf_no')->toArray();
        Log::info('show: جميع أرقام الكشوفات في القاعدة: ' . implode(', ', $allKashfNos));

        return redirect()->route('payrolls.index')->with('error', 'الكشف غير موجود - رقم الكشف: ' . $kashf_no);
    }

    $this->assertDepartmentScope($payrolls->first());

    // معلومات عامة عن الكشف
    $kashfInfo = $payrolls->first();

    // تحديد صلاحيات التعديل والحذف لكل صف
    $canEditPayrolls = [];
    foreach ($payrolls as $p) {
        $canEditPayrolls[$p->id] = $this->canEditPayroll($p);
    }

    return view('payrolls.show', compact('payrolls', 'kashfInfo', 'kashf_no', 'canEditPayrolls'));
}

public function search(Request $request)
{
    $term = $request->q;

    if (empty($term)) {
        return response()->json([]);
    }

    $employees = \App\Models\Employee::where('name', 'LIKE', "%{$term}%")
                                    ->orWhere('employee_id', 'LIKE', "%{$term}%")
                                    ->limit(15)
                                    ->get();

    return response()->json($employees->map(function ($emp) {
        return [
            'id'   => $emp->employee_id, // ✅ عدلنا هنا
            'text' => $emp->name . " - " . ($emp->department ?? '')
        ];
    }));
}


// دالة حفظ التعديلات الجديدة
public function update(Request $request, $id) {
    $payroll = Payroll::findOrFail($id);
    $this->assertDepartmentScope($payroll);
    $oldValues = $payroll->only([
        'name', 'department', 'destination', 'admin_order_no', 'admin_order_date',
        'start_date', 'end_date', 'days_count', 'daily_allowance', 'is_half_allowance',
        'mission_type_id', 'city_id', 'transportation_fee', 'meals_count', 'receipts_amount',
        'total_amount', 'status', 'is_archived'
    ]);

    // التحقق من البيانات
    $request->validate([
        'name' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'daily_allowance' => 'required|numeric',
    ], [
        'end_date.after_or_equal' => 'تاريخ نهاية الإيفاد يجب أن يكون بعد أو يساوي تاريخ البداية',
    ]);

    // تحقق من عدم تكرار رقم الأمر الإداري في نفس السنة عند التحديث
    // لا تُعيد الفحص إذا لم يتغير رقم الأمر أو تاريخه
    $adminOrderNoChanged = $request->admin_order_no !== $payroll->admin_order_no;
    $adminOrderDateChanged = $request->admin_order_date !== $payroll->admin_order_date;

    if (!empty($request->admin_order_no) && $request->admin_order_no !== 'بدون' && ($adminOrderNoChanged || $adminOrderDateChanged)) {
        // استخراج السنة من تاريخ الأمر الإداري
        try {
            $orderYear = \Carbon\Carbon::parse($request->admin_order_date ?? now())->year;
        } catch (\Exception $e) {
            $orderYear = date('Y');
        }

        $existingPayroll = Payroll::where('admin_order_no', $request->admin_order_no)
            ->where('order_year', $orderYear)
            ->where('id', '!=', $id)
            ->first();

        if ($existingPayroll) {
            Log::warning('تم العثور على تكرار لرقم الأمر الإداري عند التحديث.', [
                'admin_order_no' => $request->admin_order_no,
                'order_year' => $orderYear
            ]);
            return redirect()->back()->withErrors(['admin_order_no' => 'رقم الأمر الإداري ' . $request->admin_order_no . ' موجود بالفعل في السنة ' . $orderYear . '!']);
        }
    }

    // حساب عدد الأيام تلقائياً من التواريخ
    try {
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $days_count = $startDate->diffInDays($endDate) + 1;
    } catch (\Exception $e) {
        $days_count = 1;
    }

    // ===== التحقق من التداخل في فترات الإيفاد =====
    // منع نفس الموظف من أن يكون لديه فترات إيفاد متداخلة

    try {
        $newStart = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $newEnd = \Carbon\Carbon::parse($request->end_date)->startOfDay();
    } catch (\Exception $e) {
        return redirect()->back()->withErrors([
            'date_error' => 'خطأ في تحليل التواريخ. يرجى التأكد من صحة التواريخ المدخلة.'
        ])->withInput();
    }

    // التحقق من تداخل الفترات مع إيفادات أخرى لنفس الموظف
    $existingPayrolls = Payroll::where('name', $request->name)
        ->where('is_archived', false)
        ->where('id', '!=', $id)  // استبعاد السجل الحالي من الفحص
        ->get();

    foreach ($existingPayrolls as $existing) {
        try {
            $existingStart = \Carbon\Carbon::parse($existing->start_date)->startOfDay();
            $existingEnd = \Carbon\Carbon::parse($existing->end_date)->startOfDay();

            // شرط التداخل: (startA <= endB AND endA >= startB)
            if ($newStart <= $existingEnd && $newEnd >= $existingStart) {
                return redirect()->back()->withErrors([
                    'overlap' => 'يوجد تداخل في فترة الإيفاد! ' . $request->name .
                                ' لديه إيفاد آخر من ' . $existing->start_date . ' إلى ' . $existing->end_date .
                                ' (أمر إداري: ' . $existing->admin_order_no . ')' .
                                ' 📋 الكشف: ' . ($existing->kashf_no ?? $existing->receipt_no ?? 'غير محدد')
                ])->withInput();
            }
        } catch (\Exception $e) {
            continue;
        }
    }

    // إذا كانت المدة أكثر من يوم، مبلغ المبيت يكون ثابت 10,000 للمدن (لا يحتاج مدخلات)
    // المبيت تلقائي: 10,000 للمدن، 0 للخارج

    // تحديث البيانات الأساسية
    $payroll->name = $request->name;
    $payroll->department = $request->department;
    $payroll->job_title = $request->job_title;
    $payroll->admin_order_no = $request->admin_order_no;
    $payroll->admin_order_date = $request->admin_order_date;
    $payroll->start_date = $request->start_date;
    $payroll->end_date = $request->end_date;
    $payroll->days_count = $days_count;

    // المبيت: ثابت 10,000 للمدن العادية، صفر للخارج
    $requestDestination = $request->destination;
    $isOutsideCountryUpdate = strpos($requestDestination, 'خارج القطر') === 0;
    if ($isOutsideCountryUpdate) {
        $payroll->accommodation_fee = 0;
    } else {
        $payroll->accommodation_fee = 10000;
    }

    $payroll->transportation_fee = (float)$request->input('transportation_fee', 0);
    $payroll->meals_count = (int)$request->input('meals_count', 0);
    $payroll->receipts_amount = (float)$request->input('receipts_amount', 0);
    $payroll->is_half_allowance = $request->has('is_half_allowance') && $request->is_half_allowance == '1';

    // تحديث السنة المستخرجة من تاريخ الأمر الإداري
    if (!empty($request->admin_order_no) && $request->admin_order_no !== 'بدون') {
        try {
            $payroll->order_year = \Carbon\Carbon::parse($request->admin_order_date ?? now())->year;
        } catch (\Exception $e) {
            $payroll->order_year = date('Y');
        }
    }

    // ===== إعادة حساب المجموع الكلي (منطق الحساب الهجين: city_rate + mission_rate) =====

    $destination = $this->normalizeOutsideDestinationName($request->destination);
    $isOutsideCountry = strpos($destination, 'خارج القطر') === 0;

    // المكون 1: city_rate (يكون 0 للخارج، وسعر المدينة للعادية)
    $cityRate = 0;
    // المكون 2: mission_rate (يكون 0 للعادية، وسعر الإيفاد للخارج)
    $missionRate = 0;

    if ($isOutsideCountry) {
        // خارج القطر: استخدم سعر الإيفاد فقط
        $responsibilityLevel = $request->input('responsibility_level', '');

        if (!$responsibilityLevel) {
            return redirect()->back()->withErrors([
                'responsibility_level' => 'يجب اختيار المستوى الوظيفي عند اختيار خارج القطر'
            ])->withInput();
        }

        $missionType = $this->findMissionTypeRecord($destination, $responsibilityLevel);
        if (!$missionType) {
            return redirect()->back()->withErrors([
                'destination' => 'لم يتم العثور على تسعيرة مطابقة لخارج القطر والمستوى الوظيفي المختار'
            ])->withInput();
        }

        $payroll->mission_type_id = $missionType->id;
        $payroll->destination = $destination . ' - ' . $responsibilityLevel;
        $missionRate = (float)$missionType->daily_rate;
        if ($payroll->is_half_allowance) {
            $missionRate = $missionRate / 2;
        }
        $cityRate = 0;  // لا توجد رسوم مدينة للخارج
    } else {
        // مدينة عادية: استخدم سعر المدينة فقط
        $payroll->mission_type_id = null;
        $city = City::where('name', $destination)->first();

        if ($city) {
            $payroll->destination = $city->name;
            $cityRate = (float)$city->daily_allowance;

            // تطبيق خصم 50% إن وجدت الحالة
            if ($payroll->is_half_allowance) {
                $cityRate = $cityRate / 2;
            }
        } else {
            $cityRate = (float)$request->input('daily_allowance', 0);
            if ($payroll->is_half_allowance) {
                $cityRate = $cityRate / 2;
            }
        }

        $missionRate = 0;  // لا توجد رسوم إيفاد للمدن
    }

    // اليومية النهائية = مجموع المكونين (أحدهما دائماً صفر)
    $dailyAllowance = $cityRate + $missionRate;
    $payroll->daily_allowance = $dailyAllowance;

    // حساب المبلغ الأساسي
    $baseAmount = $days_count * $dailyAllowance;

    // حساب الليالي
    $nights = $days_count > 1 ? $days_count - 1 : 0;

    if ($isOutsideCountry) {
        // خارج القطر: أيام × سعر إيفاد + إيصالات فقط
        $payroll->accommodation_fee = 0;
        $payroll->transportation_fee = 0;
        $payroll->meals_count = 0;
        $payroll->total_amount = $baseAmount + $payroll->receipts_amount;
    } else {
        // مدينة عادية: مع مبيت والمبالغ الأخرى
        $mealsDeduction = $payroll->meals_count * ($dailyAllowance * 0.10);
        $payroll->total_amount = $baseAmount + ($nights * $payroll->accommodation_fee)
                               + $payroll->transportation_fee + $payroll->receipts_amount
                               - $mealsDeduction;
    }

    $sanityError = $this->ensureReasonablePayrollValues(
        (float) $payroll->daily_allowance,
        (float) $payroll->total_amount,
        (int) $days_count
    );
    if ($sanityError) {
        return redirect()->back()->withErrors(['amounts' => $sanityError])->withInput();
    }

    if (!$payroll->is_archived) {
        $payroll->status = Payroll::STATUS_READY_FOR_PRINT;
    }

    $payroll->save();

    $newValues = $payroll->only([
        'name', 'department', 'destination', 'admin_order_no', 'admin_order_date',
        'start_date', 'end_date', 'days_count', 'daily_allowance', 'is_half_allowance',
        'mission_type_id', 'city_id', 'transportation_fee', 'meals_count', 'receipts_amount',
        'total_amount', 'status', 'is_archived'
    ]);

    $fieldLabels = $this->auditFieldLabels();
    $changedLabels = collect($newValues)
        ->filter(function ($value, $key) use ($oldValues) {
            return (string) ($oldValues[$key] ?? '') !== (string) $value;
        })
        ->keys()
        ->map(function ($key) use ($fieldLabels) {
            return $fieldLabels[$key] ?? $key;
        })
        ->values()
        ->all();

    $description = empty($changedLabels)
        ? 'تم فتح التعديل بدون تغييرات فعلية'
        : 'تم تعديل: ' . implode('، ', $changedLabels);

    $this->logPayrollAudit(
        'updated',
        $payroll,
        $oldValues,
        $newValues,
        $description
    );

    return redirect()->route('payrolls.show', $payroll->kashf_no)->with('success', 'تم التحديث بنجاح');
}

public function addEmployee(Request $request, $kashf_no)
{
    $basePayroll = Payroll::where('kashf_no', $kashf_no)->first();
    if ($basePayroll) {
        $this->assertDepartmentScope($basePayroll);
    }

    $validator = Validator::make($request->all(), [
        'employee_id' => 'required',
        'destination' => 'required|string',
        'admin_order_no' => 'required|string',
        'admin_order_date' => 'required|date',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'daily_allowance' => 'required|numeric|min:0',
        'accommodation_fee' => 'nullable|numeric|min:0',
        'receipts_amount' => 'nullable|numeric|min:0',
        'is_half_allowance' => 'nullable|in:0,1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => 'البيانات المدخلة غير صحيحة',
            'details' => $validator->errors()->all()
        ], 422);
    }

    $employee = Employee::where('employee_id', $request->employee_id)->first();
    if (!$employee) {
        return response()->json([
            'error' => 'الموظف المحدد غير موجود'
        ], 404);
    }

    $startDate = \Carbon\Carbon::parse($request->start_date);
    $endDate = \Carbon\Carbon::parse($request->end_date);
    if ($endDate->lt($startDate)) {
        return response()->json([
            'error' => 'تاريخ نهاية الإيفاد يجب أن يكون بعد تاريخ البداية'
        ], 422);
    }

    // ===== التحقق من التداخل في فترات الإيفاد =====
    // منع نفس الموظف من أن يكون لديه فترات إيفاد متداخلة

    try {
        $newStart = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $newEnd = \Carbon\Carbon::parse($request->end_date)->startOfDay();
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'خطأ في تحليل التواريخ. يرجى التأكد من صحة التواريخ المدخلة.'
        ], 422);
    }

    // التحقق من تداخل الفترات مع إيفادات أخرى لنفس الموظف
    $existingPayrolls = Payroll::where('name', $employee->name)
        ->where('is_archived', false)
        ->get();

    foreach ($existingPayrolls as $existing) {
        try {
            $existingStart = \Carbon\Carbon::parse($existing->start_date)->startOfDay();
            $existingEnd = \Carbon\Carbon::parse($existing->end_date)->startOfDay();

            // شرط التداخل: (startA <= endB AND endA >= startB)
            if ($newStart <= $existingEnd && $newEnd >= $existingStart) {
                return response()->json([
                    'error' => 'يوجد تداخل في فترة الإيفاد!',
                    'details' => [
                        'الموظف: ' . $employee->name,
                        'الفترة الموجودة: ' . $existing->start_date . ' إلى ' . $existing->end_date,
                        'الأمر الإداري الموجود: ' . $existing->admin_order_no,
                        '📋 الكشف الموجود: ' . ($existing->kashf_no ?? $existing->receipt_no ?? 'غير محدد'),
                        'الفترة الجديدة: ' . $request->start_date . ' إلى ' . $request->end_date,
                        'لا يمكن إضافة منتسب بفترة متداخلة مع إيفاد آخر'
                    ]
                ], 422);
            }
        } catch (\Exception $e) {
            continue;
        }
    }

    $daysCount = $startDate->diffInDays($endDate) + 1;
    $isHalfAllowance = (string) $request->input('is_half_allowance', '0') === '1';
    $accommodationFee = (float) $request->input('accommodation_fee', 0);
    $receiptsAmount = (float) $request->input('receipts_amount', 0);
    $nights = $daysCount > 1 ? $daysCount - 1 : 0;

    $orderYear = \Carbon\Carbon::parse($request->admin_order_date)->year;

    // تحديد المقصد: إما مدينة عادية أو خارج القطر - حساب هجين (city_rate + mission_rate)
    $destination = $this->normalizeOutsideDestinationName($request->destination);
    $missionTypeId = null;
    $cityId = null;

    // التحقق من أن الوجهة خارج القطر أم مدينة عادية
    $isOutsideCountry = strpos($destination, 'خارج القطر') === 0;

    // المكون 1: city_rate (يكون 0 للخارج، وسعر المدينة للعادية)
    $cityRate = 0;
    // المكون 2: mission_rate (يكون 0 للعادية، وسعر الإيفاد للخارج)
    $missionRate = 0;

    if ($isOutsideCountry) {
        // خارج القطر: استخدم سعر الإيفاد فقط
        $responsibilityLevel = $request->input('responsibility_level', '');

        if (!$responsibilityLevel) {
            return response()->json([
                'error' => 'يجب اختيار المستوى الوظيفي عند اختيار خارج القطر'
            ], 422);
        }

        $missionType = $this->findMissionTypeRecord($destination, $responsibilityLevel);
        if (!$missionType) {
            return response()->json([
                'error' => 'لم يتم العثور على تسعيرة مطابقة لخارج القطر والمستوى الوظيفي المختار'
            ], 422);
        }

        $missionTypeId = $missionType->id;
        $missionRate = (float)$missionType->daily_rate;
        if ($isHalfAllowance) {
            $missionRate = $missionRate / 2;
        }
        $cityRate = 0;  // لا توجد رسوم مدينة للخارج
        $destination = $destination . ' - ' . $responsibilityLevel;

    } else {
        // مدينة عادية: استخدم سعر المدينة فقط
        $city = City::where('name', $destination)->first();
        if ($city) {
            $cityId = $city->id;
            $cityRate = (float)$city->daily_allowance;

            // تطبيق خصم 50% إن وجدت الحالة
            if ($isHalfAllowance) {
                $cityRate = $cityRate / 2;
            }
        }

        $missionRate = 0;  // لا توجد رسوم إيفاد للمدن
    }

    // اليومية النهائية = مجموع المكونين (أحدهما دائماً صفر)
    $dailyAllowance = $cityRate + $missionRate;

    // حساب المبلغ الأساسي
    $baseAmount = $daysCount * $dailyAllowance;

    // المبيت: ثابت 10,000 للمدن العادية، صفر للخارج
    if ($isOutsideCountry) {
        // خارج القطر: (أيام × سعر الإيفاد) + وصولات فقط
        $accommodationFee = 0;
        $totalAmount = $baseAmount + $receiptsAmount;
    } else {
        // مدينة عادية: (أيام × سعر المدينة) + (ليالي × مبيت ثابت 10,000) + وصولات
        $accommodationFee = 10000;
        $totalAmount = $baseAmount + ($nights * $accommodationFee) + $receiptsAmount;
    }

    $sanityError = $this->ensureReasonablePayrollValues(
        (float) $dailyAllowance,
        (float) $totalAmount,
        (int) $daysCount
    );
    if ($sanityError) {
        return response()->json([
            'error' => $sanityError
        ], 422);
    }

    $payroll = Payroll::create([
        'name' => $employee->name,
        'department' => $employee->department,
        'job_title' => $employee->job_title,
        'destination' => $destination,
        'city_id' => $cityId,
        'mission_type_id' => $missionTypeId,
        'admin_order_no' => $request->admin_order_no,
        'admin_order_date' => $request->admin_order_date,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'days_count' => $daysCount,
        'daily_allowance' => $dailyAllowance,
        'accommodation_fee' => $accommodationFee,
        'receipts_amount' => $receiptsAmount,
        'transportation_fee' => 0,
        'meals_count' => 0,
        'is_half_allowance' => $isHalfAllowance,
        'total_amount' => $totalAmount,
        'kashf_no' => $kashf_no,
        'order_year' => $orderYear,
        'is_archived' => false,
        'status' => Payroll::STATUS_READY_FOR_PRINT,
    ]);

    $addEmployeeDescription = $this->buildAuditDescription(
        'إضافة منتسب إلى كشف موجود',
        $payroll->only(['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']),
        ['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']
    );

    $this->logPayrollAudit(
        'created',
        $payroll,
        null,
        $payroll->only(['name', 'kashf_no', 'admin_order_no', 'status', 'total_amount']),
        $addEmployeeDescription
    );

    return response()->json([
        'success' => true,
        'id' => $payroll->id
    ]);
}

// دالة الحذف
public function destroy($id) {
    $payroll = Payroll::findOrFail($id); // ابحث عنه
    $this->assertDepartmentScope($payroll);

    $snapshot = $payroll->only([
        'id', 'kashf_no', 'name', 'admin_order_no', 'status', 'is_archived', 'total_amount'
    ]);

    $kashf_no = $payroll->kashf_no; // احفظ رقم الكشف قبل الحذف

    $deleteDescription = $this->buildAuditDescription(
        'حذف سجل إيفاد',
        $snapshot,
        ['name', 'kashf_no', 'admin_order_no', 'status', 'total_amount']
    );

    $payroll->delete(); // احذفه من القاعدة

    $this->logPayrollAudit(
        'deleted',
        null,
        $snapshot,
        null,
        $deleteDescription
    );

    return redirect()->route('payrolls.show', $kashf_no); // ارجع للكشف
}

// دالة لجلب البيانات وعرضها في صفحة الكشف الرسمي
public function print($id) {
    $payroll = Payroll::findOrFail($id);
    $this->assertDepartmentScope($payroll);

    // جلب بيانات المستخدم المسجل (التوقيع الأول)
    $currentUser = Auth::user();
    $currentUserName = $currentUser ? $currentUser->name : 'غير محدد';

        // جلب التواقيع الأربعة من قاعدة البيانات بناءً على رقم المسؤولية
        $signatureSlots = [
            1 => 'مسؤول وحدة',
            2 => 'مسؤول الشعبة',
            3 => 'قسم التدقيق',
            4 => 'رئيس قسم الشؤون المالية',
        ];

        $activeSignatures = Signature::where('is_active', true)->get();
        $signaturesByCode = $activeSignatures->keyBy('responsibility_code');
        $signaturesByTitle = $activeSignatures->keyBy('title');

        $signatures = collect($signatureSlots)->map(function ($title, $code) use ($signaturesByCode, $signaturesByTitle) {
            $signature = $signaturesByCode->get($code) ?? $signaturesByTitle->get($title);

            return (object) [
                'title' => $title,
                'name' => $signature ? $signature->name : null,
            ];
        })->values()->all();

        Log::info('Print page called', [
            'payroll_id' => $id,
            'current_user' => $currentUserName,
            'signatures_count' => count(array_filter($signatures, function ($signature) {
                return !empty($signature->name);
            }))
        ]);

        return view('payrolls.print', [
            'payroll' => $payroll,
            'currentUserName' => $currentUserName,
            'signatures' => $signatures
        ]);
    }


// عرض الطباعة فقط بدون أرشفة تلقائية
public function printMultiple(Request $request)
{
    $ids = $request->query('ids');
    $kashfNo = $request->query('kashf_no') ?? $request->query('group_no'); // دعم كلا المعاملين

    if (!$ids && !$kashfNo) return "خطأ: لم يتم تحديد سجلات";

    if ($kashfNo) {
        $payrolls = \App\Models\Payroll::where('kashf_no', $kashfNo)
            ->orderBy('id', 'asc')
            ->get();
    } else {
        $idsArray = explode(',', $ids);
        // جلب البيانات مع التأكد من ترتيبها
        $payrolls = \App\Models\Payroll::whereIn('id', $idsArray)->orderBy('id', 'asc')->get();
    }

    if ($payrolls->isEmpty()) return "تنبيه: السجلات غير موجودة";

    $scopeSample = $payrolls->first();
    if ($scopeSample) {
        $this->assertDepartmentScope($scopeSample);
    }

    // استخدم رقم الكشف المتسلسل `kashf_no` إن وُجد، وإلا احتفظ بطريقة القديمة
    $reportNumber = $payrolls->first()->kashf_no ?? (100 + ($payrolls->first()->id ?? 0));

    $currentUser = Auth::user();
    $currentUserName = $currentUser ? $currentUser->name : 'غير محدد';

    $signatureSlots = [
        1 => 'مسؤول وحدة',
        2 => 'مسؤول الشعبة',
        3 => 'قسم التدقيق',
        4 => 'رئيس قسم الشؤون المالية',
    ];

    $activeSignatures = Signature::where('is_active', true)->get();
    $signaturesByCode = $activeSignatures->keyBy('responsibility_code');
    $signaturesByTitle = $activeSignatures->keyBy('title');

    $signatures = collect($signatureSlots)->map(function ($title, $code) use ($signaturesByCode, $signaturesByTitle) {
        $signature = $signaturesByCode->get($code) ?? $signaturesByTitle->get($title);

        return (object) [
            'title' => $title,
            'name' => $signature ? $signature->name : null,
        ];
    })->values()->all();

    return view('payrolls.print_multiple', [
        'payrolls' => $payrolls,
        'reportNumber' => $reportNumber,
        'currentUserName' => $currentUserName,
        'signatures' => $signatures,
        'kashfNo' => $kashfNo,
        'ids' => $ids,
    ]);
}

public function confirmPrint(Request $request)
{
    $kashfNo = trim((string) $request->input('kashf_no', ''));
    $ids = trim((string) $request->input('ids', ''));

    if ($kashfNo === '' && $ids === '') {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم تحديد كشف للتأكيد.'
        ], 422);
    }

    if ($kashfNo !== '') {
        $payrolls = Payroll::where('kashf_no', $kashfNo)->get();
    } else {
        $idsArray = array_filter(array_map('intval', explode(',', $ids)));
        $payrolls = Payroll::whereIn('id', $idsArray)->get();
    }

    if ($payrolls->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'السجلات غير موجودة.'
        ], 404);
    }

    $scopeSample = $payrolls->first();
    if ($scopeSample) {
        $this->assertDepartmentScope($scopeSample);
    }

    if ($kashfNo !== '') {
        $this->incrementPrintTrackingByKashfNo($kashfNo);

        Payroll::where('kashf_no', $kashfNo)->update([
            'is_archived' => true,
            'status' => Payroll::STATUS_ARCHIVED,
        ]);
    } else {
        $idsArray = array_filter(array_map('intval', explode(',', $ids)));
        $this->incrementPrintTrackingByIds($idsArray);

        Payroll::whereIn('id', $idsArray)->update([
            'is_archived' => true,
            'status' => Payroll::STATUS_ARCHIVED,
        ]);
    }

    foreach ($payrolls as $payroll) {
        $statusLabels = Payroll::statusLabels();
        $beforeStatus = $statusLabels[(string) $payroll->status] ?? (string) $payroll->status;
        $afterStatus = $statusLabels[Payroll::STATUS_ARCHIVED] ?? Payroll::STATUS_ARCHIVED;

        $printDescription = $this->buildAuditDescription(
            'تأكيد الطباعة وترحيل الكشف إلى الأرشيف',
            [
                'name' => $payroll->name,
                'kashf_no' => $payroll->kashf_no,
            ],
            ['name', 'kashf_no']
        );
        $printDescription .= '، الحالة: ' . $beforeStatus . ' ← ' . $afterStatus;

        $this->logPayrollAudit(
            'print_confirmed',
            $payroll,
            ['status' => $payroll->status, 'is_archived' => (bool) $payroll->is_archived],
            ['status' => Payroll::STATUS_ARCHIVED, 'is_archived' => true],
            $printDescription
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'تم تأكيد الطباعة وأرشفة الكشف بنجاح.'
    ]);
}



public function archive(Request $request)
{
    $search = $request->query('search');
    /** @var \App\Models\User|null $currentUser */
    $currentUser = Auth::user();

    $archivedPayrolls = \App\Models\Payroll::where(function ($query) {
            $query->where('is_archived', true)
                ->orWhere('status', Payroll::STATUS_ARCHIVED);
        })
        ->when($currentUser && !$this->hasGlobalPayrollAccess($currentUser) && !empty($currentUser->department_id), function ($query) use ($currentUser) {
            return $query->where('created_by_department_id', $currentUser->department_id);
        })
        ->when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('admin_order_no', 'LIKE', "%{$search}%");
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(20);

    return view('payrolls.archive', compact('archivedPayrolls'));
}

private function incrementPrintTrackingByKashfNo($kashfNo): void
{
    if (!Schema::hasColumn('payrolls', 'print_count') || empty($kashfNo)) {
        return;
    }

    Payroll::where('kashf_no', $kashfNo)->update([
        'print_count' => DB::raw('COALESCE(print_count, 0) + 1'),
        'last_printed_at' => now(),
    ]);
}

private function incrementPrintTrackingByIds(array $ids): void
{
    if (!Schema::hasColumn('payrolls', 'print_count') || empty($ids)) {
        return;
    }

    Payroll::whereIn('id', $ids)->update([
        'print_count' => DB::raw('COALESCE(print_count, 0) + 1'),
        'last_printed_at' => now(),
    ]);
}

public function editSignatures() {
    $signatures = \App\Models\Signature::all();
    return view('signatures_index', compact('signatures'));
}

public function updateSignatures(Request $request) {
    foreach($request->signatures as $id => $name) {
        \App\Models\Signature::where('id', $id)->update(['name' => $name]);
    }
    return back()->with('success', 'تم تحديث الأسماء بنجاح');
}

    private function formatExcelDate($date) {
        if (!$date) return null;
        try {
            if (is_numeric($date)) {
                return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }


// أضف هذه الدالة لجلب بيانات موظف واحد عند اختياره من القائمة
public function getEmployeeById($id)
{
    // جدول الموظفين يستخدم employee_id كـ primary key
    $employee = \App\Models\Employee::where('employee_id', $id)->first();

    if (!$employee) {
        return response()->json(['error' => 'Employee not found'], 404);
    }

    // نرسل البيانات بأسماء الحقول اللي يتوقعها الـ JS في صفحة الـ Create
    return response()->json([
        'id'         => $employee->employee_id, // نرجع employee_id كـ id
        'employee_id'=> $employee->employee_id,
        'name'       => $employee->name,
        'department' => $employee->department ?? 'بدون قسم',
        'job_title'  => $employee->job_title ?? 'بدون عنوان',
    ]);
}


















/**
 * التحقق من التكرار قبل الحفظ (بناءً على تداخل الفترات، وليس المساواة التامة)
 *
 * المنطق:
 * - منع: نفس الشخص + فترات متداخلة (تاريخ البداية والنهاية يتقاطع)
 * - سماح: نفس الشخص + فترات منفصلة (إيفادات متعددة في السنة)
 * - سماح: أشخاص مختلفون + نفس الفترة (الكشف الواحد فيه عدة أشخاص)
 */
public function checkDuplicates(Request $request)
{
    // استقبل البيانات من JSON أو form data
    $payrolls = $request->input('payrolls') ?? json_decode($request->getContent(), true)['payrolls'] ?? [];

    if (empty($payrolls)) {
        Log::info('checkDuplicates: لا توجد بيانات، payrolls فارغة');
        return response()->json([]);
    }

    Log::info('checkDuplicates: تحقق من ' . count($payrolls) . ' سجل');

    $duplicates = [];

    foreach ($payrolls as $payroll) {
        if (empty($payroll['name'])) continue;

        try {
            $newStart = \Carbon\Carbon::parse($payroll['start_date'])->startOfDay();
            $newEnd = \Carbon\Carbon::parse($payroll['end_date'])->startOfDay();
        } catch (\Exception $e) {
            Log::warning('checkDuplicates: فشل تحليل التاريخ للموظف ' . $payroll['name']);
            continue;
        }

        // التحقق من تداخل الفترات: نفس الشخص + فترات متقاطعة
        // احصل على جميع الإيفادات (بما فيها المؤرشفة) لنفس الشخص
        // لفحص التداخل الشامل في جميع الفترات
        $existingPayrolls = Payroll::where('name', $payroll['name'])
            ->orderBy('start_date', 'asc')
            ->get();

        Log::info('checkDuplicates: وجدت ' . count($existingPayrolls) . ' إيفاد موجود لـ ' . $payroll['name'] . ' (بما فيها المؤرشفة)');

        // تحقق من التداخل لكل إيفاد موجود
        foreach ($existingPayrolls as $existing) {
            try {
                $existingStart = \Carbon\Carbon::parse($existing->start_date)->startOfDay();
                $existingEnd = \Carbon\Carbon::parse($existing->end_date)->startOfDay();

                // شرط التداخل: (startA <= endB AND endA >= startB)
                if ($newStart <= $existingEnd && $newEnd >= $existingStart) {
                    // وجدنا تداخل فترات - منع إضافة الموظف
                    Log::info('checkDuplicates: تداخل مكتشف لـ ' . $payroll['name']);

                    $duplicates[] = [
                        'name' => $payroll['name'],
                        'existing_period' => $existing->start_date . ' إلى ' . $existing->end_date,
                        'new_period' => $payroll['start_date'] . ' إلى ' . $payroll['end_date'],
                        'kashf_no' => $existing->kashf_no ?? $existing->receipt_no ?? 'غير محدد',
                        'admin_order_no' => $existing->admin_order_no ?? 'بدون',
                        'message' => 'خطأ: ' . $payroll['name'] . ' لديه إيفاد متداخل بالفترة ' . $existing->start_date . ' إلى ' . $existing->end_date . ' (كشف رقم: ' . ($existing->kashf_no ?? $existing->receipt_no ?? 'غير محدد') . ')'
                    ];
                    break; // لا حاجة للتحقق من الإيفادات الأخرى لهذا الشخص
                }
            } catch (\Exception $e) {
                Log::warning('checkDuplicates: خطأ في معالجة الإيفاد: ' . $e->getMessage());
                continue;
            }
        }
    }

    Log::info('checkDuplicates: نتيجة الفحص - ' . count($duplicates) . ' تداخل');

    return response()->json($duplicates);
}

/**
 * حفظ البيانات المتعددة مع التحقق المحسن
 */
public function store_multiple(Request $request)
{
    Log::info('store_multiple: بدء حفظ البيانات');

    $data = json_decode($request->payload, true);

    if (empty($data)) {
        Log::error('store_multiple: لا توجد بيانات مستلمة');
        return response()->json(['error' => 'لا توجد بيانات مستلمة'], 400);
    }

    Log::info('store_multiple: عدد السجلات المستقبلة: ' . count($data));

    $insertedIds = [];
    $errors = [];

    // ===== التحقق من تكرار أرقام الأوامر الإدارية في نفس السنة =====
    $adminOrderNosInBatch = [];

    foreach ($data as $index => $item) {
        if (empty($item['name'])) continue;

        $userProvidedOrder = $item['order_no'] ?: 'بدون';

        // تجاهل "بدون" والقيم الفارغة
        if ($userProvidedOrder !== 'بدون' && !empty($item['order_no'])) {
            // استخراج السنة من تاريخ الأمر الإداري
            try {
                $orderYear = \Carbon\Carbon::parse($item['order_date'] ?? now())->year;
            } catch (\Exception $e) {
                $orderYear = date('Y');
            }

            $adminOrderNosInBatch[] = [
                'order_no' => $userProvidedOrder,
                'order_year' => $orderYear
            ];
        }
    }

    // فحص كل رقم أمر إداري في قاعدة البيانات حسب السنة
    foreach ($adminOrderNosInBatch as $orderInfo) {
        $existingInDB = Payroll::where('admin_order_no', $orderInfo['order_no'])
            ->where('order_year', $orderInfo['order_year'])
            ->first();

        if ($existingInDB) {
            Log::warning('التحقق من الأوامر الإدارية: تم العثور على تكرار للأمر رقم ' . $orderInfo['order_no'] . ' في السنة ' . $orderInfo['order_year']);
            return response()->json([
                'error' => 'رقم الأمر الإداري ' . $orderInfo['order_no'] . ' موجود بالفعل في قاعدة البيانات للسنة ' . $orderInfo['order_year'] . '!',
                'details' => ['رقم الأمر الإداري المكرر: ' . $orderInfo['order_no']]
            ], 422);
        }
    }

    try {
        DB::beginTransaction();

        // توليد رقم كشف متسلسل للدُفعة (kashf_no)
        // توليد رقم كشف متسلسل للدُفعة ضمن السنة الحالية (يُعاد الترقيم كل سنة)
        $currentYear = date('Y');
        $hasKashfColumn = Schema::hasColumn('payrolls', 'kashf_no');
        $hasOrderYearColumn = Schema::hasColumn('payrolls', 'order_year');
        try {
            if ($hasKashfColumn) {
                $maxKashf = Payroll::whereYear('created_at', $currentYear)->max('kashf_no');
                $batchKashfNo = ($maxKashf ? intval($maxKashf) : 0) + 1;
            } else {
                // fallback: use max id within this year
                $maxId = Payroll::whereYear('created_at', $currentYear)->max('id');
                $batchKashfNo = ($maxId ? intval($maxId) : 0) + 1;
            }
        } catch (\Exception $e) {
            // أي خطأ غير متوقع: fallback إلى max id العام
            $maxId = Payroll::max('id');
            $batchKashfNo = ($maxId ? intval($maxId) : 0) + 1;
            $hasKashfColumn = Schema::hasColumn('payrolls', 'kashf_no');
        }

        // توليد رقم الأمر الإداري المتسلسل للسنة الحالية
        // (يجب أن يكون مختلفاً عن kashf_no ويُعاد الترقيم كل سنة)
        $maxAdminOrderNo = 0;
        if ($hasOrderYearColumn) {
            // ابحث عن أكبر رقم أمر إداري في السنة الحالية (استبعد "بدون")
            $maxAdminOrderNo = Payroll::where('order_year', $currentYear)
                ->where('admin_order_no', '!=', 'بدون')
                ->max('admin_order_no');
            $maxAdminOrderNo = $maxAdminOrderNo ? intval($maxAdminOrderNo) : 0;
        }

        // detect if a unique index exists on kashf_no (some DBs/migrations might have created it)
        $hasKashfUniqueIndex = false;
        try {
            $dbName = DB::getDatabaseName();
            $res = DB::select("SELECT COUNT(*) as cnt FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'payrolls' AND INDEX_NAME = 'payrolls_kashf_no_unique'", [$dbName]);
            if (!empty($res) && isset($res[0]->cnt) && intval($res[0]->cnt) > 0) {
                $hasKashfUniqueIndex = true;
            }
        } catch (\Exception $e) {
            // ignore - assume no unique index
            $hasKashfUniqueIndex = false;
        }

        $firstAssignedKashf = false;
        // تتبع السجلات المحفوظة حديثاً لفحص التداخل داخل الدفعة نفسها
        $savedRecordsInBatch = [];

        foreach ($data as $index => $item) {
            $skipCurrentItem = false;  // إعادة تعيين لكل سطر
            if (empty($item['name'])) continue;

            // ===== التحقق من البيانات المطلوبة =====
            if (empty($item['start_date']) || empty($item['end_date'])) {
                $errors[] = "السطر " . ($index + 1) . ": تاريخ البداية أو النهاية فارغ";
                continue;
            }

            // تسجيل البيانات الواردة للتحليل
            Log::info('store_multiple: السطر ' . ($index + 1) . ' - البيانات الواردة', [
                'name' => $item['name'] ?? 'بدون',
                'city_id' => $item['city_id'] ?? 'فارغ',
                'mission_type' => $item['mission_type'] ?? 'فارغ',
                'job_title' => $item['job_title'] ?? 'فارغ',
                'responsibility_level' => $item['responsibility_level'] ?? 'فارغ'
            ]);

            // معالجة الوجهة بشكل صارم: لا نعتمد على job_title كبديل للوجهة
            $cityIdRaw = trim((string)($item['city_id'] ?? ''));
            $missionTypeRaw = trim((string)($item['mission_type'] ?? ''));

            // توافق مع بيانات قديمة: إذا أُرسلت "خارج القطر" داخل city_id انقلها إلى mission_type
            if (!$missionTypeRaw && strpos($cityIdRaw, 'خارج القطر') === 0) {
                $missionTypeRaw = $cityIdRaw;
                $cityIdRaw = '';
            }

            // fallback: في بعض الصفوف يكون المستوى الوظيفي مرسلاً ضمن job_title
            if (empty($item['responsibility_level']) && !empty($item['job_title'])) {
                $possibleLevel = trim((string)$item['job_title']);
                $validLevels = [
                    'منتسب',
                    'مسؤول شعبة',
                    'مسؤول وجبة',
                    'مسؤول وحدة',
                    'معاون',
                    'رئيس',
                    'عضو',
                    'مستشار',
                    'نائب أمين عام',
                    'أمين عام',
                ];

                if (in_array($possibleLevel, $validLevels, true)) {
                    $item['responsibility_level'] = $possibleLevel;
                }
            }

            $hasCity = $cityIdRaw !== '';
            $hasMissionType = $missionTypeRaw !== '';
            $hasResponsibilityLevel = !empty($item['responsibility_level']);
            $isOutsideCountry = $hasMissionType && strpos($missionTypeRaw, 'خارج القطر') === 0;

            if (!$hasCity && !$hasMissionType) {
                Log::warning('store_multiple: السطر ' . ($index + 1) . ' - الوجهة فارغة! تفاصيل:', [
                    'city_id_raw' => $item['city_id'] ?? null,
                    'mission_type_raw' => $item['mission_type'] ?? null,
                    'job_title' => $item['job_title'] ?? null
                ]);

                $errors[] = "السطر " . ($index + 1) . ": يجب اختيار جهة الإيفاد (مدينة أو خارج القطر) - تفاصيل: city_id='" . ($item['city_id'] ?? 'فارغ') . "' mission_type='" . ($item['mission_type'] ?? 'فارغ') . "'";
                continue;
            }

            if ($hasMissionType && !$isOutsideCountry) {
                $errors[] = "السطر " . ($index + 1) . ": نوع الإيفاد غير صالح";
                continue;
            }

            // التحقق من أن الوجهة خارج القطر أم مدينة عادية
            if ($isOutsideCountry && !$hasResponsibilityLevel) {
                $errors[] = "السطر " . ($index + 1) . ": الوجهة خارج القطر تتطلب اختيار المستوى الوظيفي";
                continue;
            }

            $destinationInput = $isOutsideCountry ? $missionTypeRaw : $cityIdRaw;

            // ===== التحقق من التكرار في نفس الدفعة (تداخل الفترات) =====
            Log::info("store_multiple: معالجة السطر " . ($index + 1) . " - اسم: [" . $item['name'] . "] طول: " . strlen($item['name']));
            try {
                $itemStart = \Carbon\Carbon::parse($item['start_date'])->startOfDay();
                $itemEnd = \Carbon\Carbon::parse($item['end_date'])->startOfDay();
            } catch (\Exception $e) {
                $itemStart = null;
                $itemEnd = null;
            }

            // التحقق من ترتيب التواريخ داخل السطر
            if ($itemStart && $itemEnd && $itemEnd < $itemStart) {
                $errors[] = "السطر " . ($index + 1) . ": تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية";
                continue;
            }

            Log::info("store_multiple: فحص السطر " . ($index + 1) . " - " . $item['name'] . " من " . $item['start_date'] . " إلى " . $item['end_date']);
            Log::info("store_multiple: عدد السجلات المحفوظة حالياً: " . count($insertedIds));

            foreach ($insertedIds as $idx => $inserted) {
                // تنظيف الأسماء من المسافات والتحقق من التطابق (مع case insensitive)
                $trimmedInsertedName = trim($inserted['name']);
                $trimmedItemName = trim($item['name']);

                if (strtolower($trimmedInsertedName) !== strtolower($trimmedItemName)) {
                    continue; // اسم مختلف - بلا مشكلة
                }

                Log::info("store_multiple: نفس الموظف! مقارنة: '{$trimmedInsertedName}' == '{$trimmedItemName}'");

                // نفس الشخص - تحقق من تداخل الفترات
                try {
                    $insertedStart = \Carbon\Carbon::parse($inserted['start_date'])->startOfDay();
                    $insertedEnd = \Carbon\Carbon::parse($inserted['end_date'])->startOfDay();
                } catch (\Exception $e) {
                    continue;
                }

                // شرط التداخل: (startA <= endB AND endA >= startB)
                if ($itemStart && $itemEnd && $insertedStart && $insertedEnd) {
                    $hasOverlap = $itemStart <= $insertedEnd && $itemEnd >= $insertedStart;
                    Log::info("store_multiple: مقارنة: [{$itemStart->format('Y-m-d')}] <= [{$insertedEnd->format('Y-m-d')}] && [{$itemEnd->format('Y-m-d')}] >= [{$insertedStart->format('Y-m-d')}] = " . ($hasOverlap ? 'TRUE (تداخل)' : 'FALSE'));

                    if ($hasOverlap) {
                        // تداخل مكتشف - منع
                        $errorMsg = "❌ السطر " . ($index + 1) . ": " . $item['name'] . " - فترة متداخلة مع سجل محفوظ في نفس الدفعة\n"
                            . "الفترة المحفوظة: " . $inserted['start_date'] . " إلى " . $inserted['end_date'] . "\n"
                            . "الفترة الجديدة: " . $item['start_date'] . " إلى " . $item['end_date'];
                        $errors[] = $errorMsg;
                        Log::warning("store_multiple: " . $errorMsg);
                        $skipCurrentItem = true;
                        break;  // خرج من حلقة البحث في insertedIds
                    }
                }
            }

            // إذا كان هناك تداخل، تخطى هذا السطر
            if (!empty($skipCurrentItem)) {
                Log::info("store_multiple: تخطى السطر {$index} - تداخل مكتشف");
                continue;
            }

            // ===== التحقق من التكرار في قاعدة البيانات (تداخل الفترات) =====
            // منع: نفس الاسم + فترات متداخلة
            try {
                $dbStart = \Carbon\Carbon::parse($item['start_date'])->startOfDay();
                $dbEnd = \Carbon\Carbon::parse($item['end_date'])->startOfDay();
            } catch (\Exception $e) {
                $dbStart = null;
                $dbEnd = null;
            }

            // === فحص التداخل مع قاعدة البيانات (بطريقة PHP مباشرة) ===
            $duplicateCheck = null;
            if ($dbStart && $dbEnd) {
                // احصل على جميع الإيفادات (بما فيها المؤرشفة) لنفس الشخص لفحص التداخل الشامل
                $trimmedName = trim($item['name']);
                $existingPayrolls = Payroll::all()
                    ->filter(function($payroll) use ($trimmedName) {
                        return strtolower(trim($payroll->name)) === strtolower($trimmedName);
                    });

                Log::info("store_multiple: فحص قاعدة البيانات - وجدنا " . count($existingPayrolls) . " إيفاد موجود للموظف '{$trimmedName}' (بما فيها المؤرشفة)");

                foreach ($existingPayrolls as $existing) {
                    try {
                        $existingStart = \Carbon\Carbon::parse($existing->start_date)->startOfDay();
                        $existingEnd = \Carbon\Carbon::parse($existing->end_date)->startOfDay();

                        // تحقق من التداخل: (startA <= endB AND endA >= startB)
                        $hasOverlapWithDB = $dbStart <= $existingEnd && $dbEnd >= $existingStart;
                        Log::info("store_multiple: مقارنة مع DB: [{$dbStart->format('Y-m-d')}] <= [{$existingEnd->format('Y-m-d')}] && [{$dbEnd->format('Y-m-d')}] >= [{$existingStart->format('Y-m-d')}] = " . ($hasOverlapWithDB ? 'TRUE (تداخل)' : 'FALSE'));

                        if ($hasOverlapWithDB) {
                            $duplicateCheck = $existing;
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            if ($duplicateCheck) {
                // وجدنا تداخل فترات - منع الإضافة
                $errorMsg = "❌ السطر " . ($index + 1) . ": " . $item['name'] . " - فترة متداخلة مع إيفاد موجود في قاعدة البيانات\n"
                    . "الفترة الموجودة: " . $duplicateCheck->start_date . " إلى " . $duplicateCheck->end_date . "\n"
                    . "الفترة الجديدة: " . $item['start_date'] . " إلى " . $item['end_date'];
                $errors[] = $errorMsg;
                Log::warning("store_multiple: " . $errorMsg);
                continue;
            }

            // ===== التحقق من مبلغ المبيت إذا الأيام > 1 =====
            try {
                $start = \Carbon\Carbon::parse($item['start_date'])->startOfDay();
                $end = \Carbon\Carbon::parse($item['end_date'])->startOfDay();
                $days = $start->diffInDays($end) + 1;
            } catch (\Exception $e) {
                $days = 1;
            }

            // التحقق من المبيت فقط للمدينة العادية، وليس لخارج القطر
            $isOutsideCountryCheck = $destinationInput !== '' && strpos($destinationInput, 'خارج القطر') === 0;
            if (!$isOutsideCountryCheck && $days > 1 && (empty($item['accommodation_fee']) && empty($item['acc_fee']))) {
                $errors[] = "السطر " . ($index + 1) . ": " . $item['name'] . " مدة الإيفاد " . $days . " أيام، مطلوب إدخال مبلغ المبيت";
                continue;
            }

            // ===== إنشاء السجل =====
            $payroll = new Payroll();

            // محاولة ربط الموظف بـ employee_id إذا كان موجوداً
            if (!empty($item['employee_id'])) {
                $payroll->employee_id = trim($item['employee_id']);
            }

            $payroll->name = trim($item['name']);  // تنظيف الاسم من المسافات
            $payroll->department = trim($item['dept'] ?? '');
            $payroll->job_title = trim($item['job_title'] ?? '');

            // معالجة الوجهة والمهمة
            $destinationValue = $destinationInput;
            $responsibilityLevel = trim($item['responsibility_level'] ?? '');

            if ($isOutsideCountry) {
                // خارج القطر: احصل على معدل الإيفاد من جدول mission_types
                $payroll->destination = $destinationValue . ' - ' . $responsibilityLevel;

                $missionTypeRecord = MissionType::where('name', $destinationValue)
                    ->where('responsibility_level', $responsibilityLevel)
                    ->first();

                if (!$missionTypeRecord) {
                    $errors[] = "السطر " . ($index + 1) . ": جهة الإيفاد خارج القطر أو المستوى الوظيفي غير مطابق لجدول الأنواع";
                    continue;
                }

                $payroll->mission_type_id = $missionTypeRecord->id;

                $payroll->city_id = null;
            } else {
                // مدينة عادية
                $payroll->mission_type_id = null;

                // البحث عن المدينة برقم أو اسم
                $city = null;
                if (is_numeric($destinationValue)) {
                    $city = City::find($destinationValue);
                } else {
                    $city = City::where('name', $destinationValue)->first();
                }

                if (!$city) {
                    $errors[] = "السطر " . ($index + 1) . ": جهة الإيفاد (المدينة) غير صالحة أو غير موجودة";
                    continue;
                }

                $payroll->city_id = $city->id;
                $payroll->destination = $city->name;
            }

            // معالجة رقم الأمر الإداري: إذا كان المستخدم أدخل "بدون" أو فارغ، استخدمه كما هو
            // وإلا، استخراج السنة من تاريخ الأمر الإداري
            $userProvidedOrder = $item['order_no'] ?: 'بدون';
            if ($userProvidedOrder === 'بدون' || empty($item['order_no'])) {
                $payroll->admin_order_no = 'بدون';
                $payroll->order_year = null;
            } else {
                // المستخدم أدخل رقماً - استخدمه كما هو وحفظ السنة المستخرجة من تاريخ الأمر الإداري
                $payroll->admin_order_no = $userProvidedOrder;
                if ($hasOrderYearColumn) {
                    try {
                        $payroll->order_year = \Carbon\Carbon::parse($item['order_date'] ?? now())->year;
                    } catch (\Exception $e) {
                        $payroll->order_year = date('Y');
                    }
                }
            }

            $payroll->receipt_no = $item['receipt_no'] ?? $batchKashfNo;
            // only set kashf_no if the column exists
            if (!empty($hasKashfColumn)) {
                if (!empty($hasKashfUniqueIndex)) {
                    // if unique index exists, assign kashf_no only to the first saved record in this batch
                    if (!$firstAssignedKashf) {
                        $payroll->kashf_no = $batchKashfNo;
                        $firstAssignedKashf = true;
                    }
                } else {
                    $payroll->kashf_no = $batchKashfNo;
                }
            }
            $payroll->admin_order_date = !empty($item['order_date']) ? $item['order_date'] : now()->format('Y-m-d');

            $payroll->start_date = $item['start_date'];
            $payroll->end_date = $item['end_date'];

            $payroll->accommodation_fee = (float)($item['accommodation_fee'] ?? $item['acc_fee'] ?? 0);
            $payroll->receipts_amount = (float)($item['receipts'] ?? 0);
            $payroll->transportation_fee = (float)($item['transportation_fee'] ?? 0);
            $payroll->meals_count = (int)($item['meals_count'] ?? 0);
            $payroll->notes = $item['notes'] ?? '';
            $payroll->is_half_allowance = ($item['is_half'] == 1);

            // حساب الأيام
            $payroll->days_count = $days;

            // حساب مبلغ اليومية - حساب هجين (city_rate + mission_rate)

            // المكون 1: city_rate (يكون 0 للخارج، وسعر المدينة للعادية)
            $cityRate = 0;
            // المكون 2: mission_rate (يكون 0 للعادية، وسعر الإيفاد للخارج)
            $missionRate = 0;

            if ($payroll->mission_type_id) {
                // خارج القطر: احصل على السعر من mission_types فقط
                $missionType = MissionType::find($payroll->mission_type_id);
                $missionRate = $missionType ? (float)$missionType->daily_rate : 0;

                // تطبيق خصم 50% على خارج القطر أيضاً
                if ($payroll->is_half_allowance) {
                    $missionRate = $missionRate / 2;
                }

                $cityRate = 0;  // لا توجد رسوم مدينة للخارج
            } elseif ($payroll->city_id) {
                // مدينة عادية: احصل على السعر من cities فقط
                $city = City::find($payroll->city_id);
                $cityRate = $city ? (float)$city->daily_allowance : 0;

                // تطبيق خصم 50% للمدن العادية فقط
                if ($payroll->is_half_allowance) {
                    $cityRate = $cityRate / 2;
                }

                $missionRate = 0;  // لا توجد رسوم إيفاد للمدن
            }

            // اليومية النهائية = مجموع المكونين (أحدهما دائماً صفر)
            $dailyAllowance = $cityRate + $missionRate;
            $payroll->daily_allowance = (float)$dailyAllowance;

            // حساب المبلغ الكلي:
            $nights = $days > 1 ? $days - 1 : 0;
            $baseAmount = $days * $dailyAllowance;

            if ($payroll->mission_type_id) {
                // خارج القطر: (أيام × سعر الإيفاد) + وصولات فقط
                // بدون مبيت ولا رسوم أخرى
                $payroll->accommodation_fee = 0;
                $payroll->transportation_fee = 0;
                $payroll->meals_count = 0;

                $payroll->total_amount = $baseAmount + $payroll->receipts_amount;
            } else {
                // مدينة عادية: (أيام × سعر المدينة) + (ليالي × مبيت) + وصولات - (وجبات)
                $mealsDeduction = $payroll->meals_count * ($dailyAllowance * 0.10);
                $payroll->total_amount = $baseAmount + ($nights * $payroll->accommodation_fee)
                                       + $payroll->receipts_amount + $payroll->transportation_fee
                                       - $mealsDeduction;
            }

            // تعيين القيمة الافتراضية لحالة الأرشفة
            $payroll->is_archived = false;
            $payroll->status = Payroll::STATUS_READY_FOR_PRINT;

            $sanityError = $this->ensureReasonablePayrollValues(
                (float) $payroll->daily_allowance,
                (float) $payroll->total_amount,
                (int) $days
            );
            if ($sanityError) {
                $errors[] = "❌ السطر " . ($index + 1) . ": " . $item['name'] . " - " . $sanityError;
                continue;
            }

            // تعيين المستخدم الحالي والقسم
            $payroll->user_id = Auth::id();
            $payroll->created_by_department_id = Auth::user()->department_id;

            $payroll->save();
            Log::info('store_multiple: ✅ حفظ السجل ID=' . $payroll->id . ' Name=' . $payroll->name . ' Start=' . $payroll->start_date . ' End=' . $payroll->end_date);

            $bulkCreateDescription = $this->buildAuditDescription(
                'إنشاء سجل ضمن الإدخال الجماعي',
                $payroll->only(['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']),
                ['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']
            );

            $this->logPayrollAudit(
                'created',
                $payroll,
                null,
                $payroll->only(['name', 'kashf_no', 'admin_order_no', 'status', 'total_amount']),
                $bulkCreateDescription
            );

            $insertedIds[] = [
                'id' => $payroll->id,
                'name' => $payroll->name,
                'start_date' => $payroll->start_date,
                'end_date' => $payroll->end_date,
                'order_no' => $payroll->admin_order_no
            ];
            Log::info('store_multiple: ✅ إضافة إلى insertedIds - العدد الحالي: ' . count($insertedIds));
        }

        if (!empty($errors)) {
            Log::info('store_multiple: أخطاء في الحفظ - count=' . count($errors));
            Log::warning('store_multiple: تفاصيل الأخطاء', [
                'errors' => $errors,
            ]);
            DB::rollBack();
            return response()->json([
                'error' => 'تم رفض بعض السجلات:',
                'details' => $errors
            ], 422);
        }

        DB::commit();

        // نسخ احتياطي تلقائي عند إضافة كشوفات جديدة
        \App\Http\Controllers\BackupController::createAutomaticBackup();

        Log::info('store_multiple: تم حفظ ' . count($insertedIds) . ' سجل بنجاح');

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ ' . count($insertedIds) . ' سجل بنجاح',
            'ids' => array_column($insertedIds, 'id')
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * استيراد البيانات من Excel مع تحسينات
 */
public function importPreview(Request $request)
{
    $storedPath = null;

    try {
        if (!$request->hasFile('excel_file') && !$request->hasFile('file')) {
            return response()->json(['error' => 'لم يتم رفع أي ملف'], 400);
        }

        $file = $request->file('excel_file') ?? $request->file('file');

        if (!$file || !$file->isValid()) {
            $error = $file ? $file->getError() : UPLOAD_ERR_NO_FILE;
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'حجم الملف أكبر من الحد المسموح به في الخادم',
                UPLOAD_ERR_FORM_SIZE => 'حجم الملف أكبر من الحد المسموح به في النموذج',
                UPLOAD_ERR_PARTIAL => 'تم رفع الملف بشكل جزئي',
                UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
                UPLOAD_ERR_NO_TMP_DIR => 'مجلد الملفات المؤقتة غير موجود على الخادم',
                UPLOAD_ERR_CANT_WRITE => 'تعذر كتابة الملف على القرص',
                UPLOAD_ERR_EXTENSION => 'تم إيقاف رفع الملف بسبب إضافة غير مسموح بها',
            ];

            return response()->json([
                'error' => $errorMessages[$error] ?? 'فشل رفع الملف، الرجاء المحاولة مرة أخرى'
            ], 400);
        }

        if (empty($file->getPathname())) {
            return response()->json([
                'error' => 'تعذر الوصول إلى الملف المؤقت. تأكد من إعدادات رفع الملفات على الخادم'
            ], 400);
        }

        // التحقق من صيغة الملف
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['error' => 'صيغة الملف غير مدعومة. الرجاء رفع ملف Excel (xlsx, xls) أو CSV'], 400);
        }

        // مسار الملف قد يكون فارغاً في بعض بيئات Windows/IIS
        // لذلك نستخدم مسار getRealPath أولاً، ثم fallback إلى نسخ الملف محلياً
        $absolutePath = $file->getRealPath();

        if (empty($absolutePath) || !is_readable($absolutePath)) {
            try {
                $tempDir = storage_path('app/temp_imports');
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $filename = 'import_' . now()->format('Ymd_His') . '_' . uniqid('', true) . '.' . strtolower($extension);
                $absolutePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
                $storedPath = 'temp_imports/' . $filename;

                if (!copy($file->getPathname(), $absolutePath)) {
                    return response()->json([
                        'error' => 'تعذر نسخ الملف مؤقتاً للمعالجة.'
                    ], 500);
                }

            } catch (\Exception $copyEx) {
                return response()->json([
                    'error' => 'خطأ في معالجة الملف: ' . $copyEx->getMessage()
                ], 500);
            }
        }

        if (empty($absolutePath) || !is_readable($absolutePath)) {
            return response()->json([
                'error' => 'تعذر الوصول إلى مسار الملف للمعالجة.'
            ], 500);
        }

        // قراءة الملف مباشرة عبر PhpSpreadsheet لضمان التوافق وتجنب مشكلة path can't be empty
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($absolutePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, false, false, false);

        if (empty($rows)) {
            return response()->json(['error' => 'الملف فارغ'], 400);
        }


        $headers = array_shift($rows) ?? [];
        $normalizedHeaders = array_map(function($h) { return trim($h); }, $headers);
        Log::info('ExcelImportHeaders', ['headers' => $normalizedHeaders, 'rows_count' => count($rows), 'first_row' => $rows[0] ?? null]);

        // فقط أعد البيانات كما هي بدون أي معالجة تخص المدن
        $previewData = [];
        $rowNumber = 1;
        foreach ($rows as $row) {
            $rowNumber++;
            // تخطي الصفوف الفارغة (لا اسم)
            $nameIdx = null;
            foreach ($normalizedHeaders as $idx => $header) {
                if (mb_strpos($header, 'اسم') !== false) {
                    $nameIdx = $idx;
                    break;
                }
            }
            $nameVal = $nameIdx !== null ? ($row[$nameIdx] ?? '') : '';
            if (empty($nameVal)) {
                continue;
            }
            $item = [ 'row_number' => $rowNumber ];
            foreach ($normalizedHeaders as $idx => $header) {
                $value = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                // تحويل التواريخ من serial إلى نص ميلادي فقط للأعمدة الثلاثة
                $headerLower = mb_strtolower($header);
                if (in_array($headerLower, ['order_date', 'تاريخه', 'تاريخ الأمر الإداري', 'start_date', 'تاريخ بدء الإيفاد', 'بدء', 'end_date', 'تاريخ انتهاء الإيفاد', 'نهاية'])) {
                    // إذا كانت القيمة رقمية (serial)
                    if (is_numeric($value) && $value > 1000 && $value < 900000) {
                        $value = $this->excelSerialToDate($value);
                    }
                }
                $item[$header] = $value;
            }
            Log::info('ExcelImportRow', $item);
            $item['is_valid'] = true;
            $previewData[] = $item;
        }

        if (empty($previewData)) {
            Log::warning('ExcelImport: لم يتم العثور على بيانات صالحة', [
                'headers' => $normalizedHeaders,
                'rows_count' => count($rows),
            ]);
            return response()->json(['error' => 'لم يتم العثور على بيانات صالحة في الملف'], 400);
        }

        $stats = [
            'total_rows' => count($previewData),
            'valid_rows' => count($previewData),
        ];

        return response()->json([
            'data' => $previewData,
            'stats' => $stats,
            'headers' => $headers
        ]);

    } catch (\Exception $e) {
        Log::error('Excel import error: ' . $e->getMessage());
        return response()->json([
            'error' => 'خطأ في معالجة الملف: ' . $e->getMessage()
        ], 500);
    } finally {
        if (!empty($storedPath)) {
            try {
                Storage::disk('local')->delete($storedPath);
            } catch (\Exception $cleanupException) {
                Log::warning('Excel import cleanup warning: ' . $cleanupException->getMessage());
            }
        }
    }
}

/**
 * تحميل نموذج Excel فارغ
 */
public function downloadTemplate()
{
    $headers = [
        'الاسم الثلاثي للموظف',
        'القسم التابع له',
        'العنوان الوظيفي',
        'اسم المدينة (كما في قاعدة البيانات)',
        'رقم الأمر الإداري',
        'تاريخه',
        'تاريخ بدء الإيفاد',
        'تاريخ انتهاء الإيفاد',
        'مبلغ المبيت',
        'الوصولات',
        'الملاحظات'
    ];

    $exampleRow = [
        'احمد محمد حسين',
        'مركز الإدارة',
        'مسؤول',
        'التاجي',
        '88',
        '2025-05-25',
        '2025-05-25',
        '2025-05-28',
        '25000',
        '',
        'بلبليسبيس'
    ];

    $data = [$headers, $exampleRow];

    return Excel::download(new \App\Exports\TemplateExport($data), 'نموذج_إدخال_الإيفادات.xlsx');
}
}

