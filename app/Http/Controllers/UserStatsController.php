<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    /**
     * جلب قائمة المنتسبين/المستخدمين المتاحين ضمن صلاحية المستخدم الحالي
     */
    public function getTeamMembers(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = $this->buildQueryByRole($user);

        $memberIds = (clone $query)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->filter()
            ->values();

        // حساب عدد الكشوفات لكل منشئ - مع تجنب العد المكرر
        // نحسب الكشف للمنشئ الأول فقط (أقدم created_at لنفس kashf_no)
        $firstCreators = DB::table('payrolls')
            ->select('kashf_no', DB::raw('MIN(id) as first_record_id'))
            ->whereIn('user_id', $memberIds)
            ->groupBy('kashf_no')
            ->get()
            ->pluck('first_record_id');

        $totalsByUser = Payroll::whereIn('id', $firstCreators)
            ->select('user_id', DB::raw('COUNT(DISTINCT kashf_no) as total_payrolls'))
            ->groupBy('user_id')
            ->pluck('total_payrolls', 'user_id');

        $members = User::query()
            ->whereIn('id', $memberIds)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(function ($member) use ($totalsByUser) {
                return [
                    'id' => (int) $member->id,
                    'name' => $member->name,
                    'total_payrolls' => (int) ($totalsByUser[$member->id] ?? 0),
                ];
            })
            ->values();

        return response()->json($members);
    }

    /**
     * الحصول على إحصائيات المستخدم الحالي
     * بناءً على صلاحياته
     */
    public function getUserStats(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $year = $request->query('year');
        $month = $request->query('month');
        $day = $request->query('day');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');

        // بناء الاستعلام بناءً على الصلاحيات
        $query = $this->buildQueryByRole($user);

        $selectedUserFilter = $this->applySelectedUserFilter($request, $user, $query);
        if ($selectedUserFilter) {
            return $selectedUserFilter;
        }

        // إذا كان البحث بين تاريخين، استخدم whereBetween فقط
        if ($from_date && $to_date) {
            $query->whereBetween('created_at', [$from_date, $to_date]);
        } else {
            // استخدم فلاتر السنة/الشهر/اليوم إذا لم يكن هناك from_date/to_date
            if ($year) {
                $query->whereYear('created_at', $year);
            }

            if ($month) {
                $query->whereMonth('created_at', $month);
            }

            if ($day) {
                $query->whereDay('created_at', $day);
            }
        }

        // حساب عدد الكشوفات الفريدة (distinct kashf_no) والمبالغ
        // نسخ الـ query لحساب عدد الكشوفات
        $countQuery = clone $query;
        $total_payrolls = $countQuery->distinct('kashf_no')->count('kashf_no');

        // حساب المبالغ: جمع total_amount لكل kashf_no بدون تكرار
        // نستخدم groupBy لتجميع المبالغ حسب kashf_no ثم نجمعها
        $amountQuery = clone $query;
        $totalByPayroll = $amountQuery->groupBy('kashf_no')
            ->selectRaw('kashf_no, SUM(total_amount) as payroll_total')
            ->get();

        $total_amount = $totalByPayroll->sum('payroll_total');

        // عدد الكشوفات المعدّلة (تم تغييرها بعد الإنشاء)
        $modified_payrolls = (clone $query)
            ->whereColumn('updated_at', '>', 'created_at')
            ->distinct('kashf_no')
            ->count('kashf_no');

        // عدد الكشوفات المطبوعة وعدد مرات الطباعة (إن توفرت أعمدة التتبع)
        $printed_payrolls = 0;
        $print_actions_count = 0;

        if (Schema::hasColumn('payrolls', 'print_count')) {
            $printed_payrolls = (clone $query)
                ->where('print_count', '>', 0)
                ->distinct('kashf_no')
                ->count('kashf_no');

            $print_actions_count = (clone $query)->sum('print_count');
        }

        return response()->json([
            'total_payrolls' => $total_payrolls,
            'total_amount' => $total_amount,
            'created_payrolls' => $total_payrolls,
            'modified_payrolls' => $modified_payrolls,
            'printed_payrolls' => $printed_payrolls,
            'print_actions_count' => (int) $print_actions_count,
        ]);
    }

    /**
     * الحصول على السنوات المتاحة للمستخدم الحالي
     */
    public function getUserYears(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = $this->buildQueryByRole($user);

        $selectedUserFilter = $this->applySelectedUserFilter($request, $user, $query);
        if ($selectedUserFilter) {
            return $selectedUserFilter;
        }

        $years = $query->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->unique()
            ->values();

        return response()->json($years);
    }

    /**
     * الحصول على الأشهر المتاحة للسنة المحددة (حسب صلاحيات المستخدم)
     */
    public function getUserMonths(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $year = $request->query('year', now()->year);
        $query = $this->buildQueryByRole($user);

        $selectedUserFilter = $this->applySelectedUserFilter($request, $user, $query);
        if ($selectedUserFilter) {
            return $selectedUserFilter;
        }

        $months = $query->whereYear('created_at', $year)
            ->selectRaw("MONTH(created_at) as month, DATE_FORMAT(created_at, '%m') as value, DATE_FORMAT(created_at, 'الشهر %c من سنة " . $year . "') as label")
            ->distinct()
            ->orderBy('month')
            ->get()
            ->map(fn($m) => [
                'value' => (int)$m->value,
                'label' => $this->getMonthName((int)$m->value) . ' ' . $year
            ]);

        return response()->json($months->values());
    }

    /**
     * الحصول على الأيام المتاحة للشهر المحدد (حسب صلاحيات المستخدم)
     */
    public function getUserDays(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $year = $request->query('year', now()->year);
        $month = $request->query('month');

        if (!$month) {
            return response()->json([]);
        }

        $query = $this->buildQueryByRole($user);

        $selectedUserFilter = $this->applySelectedUserFilter($request, $user, $query);
        if ($selectedUserFilter) {
            return $selectedUserFilter;
        }

        $days = $query->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DAY(created_at) as day')
            ->distinct()
            ->orderBy('day')
            ->pluck('day')
            ->map(fn($d) => (int)$d)
            ->values();

        return response()->json($days);
    }

    /**
     * بناء الاستعلام بناءً على صلاحيات المستخدم
     *
     * - موظف عادي: يشوف كشوفاته بس
     * - مسؤول وحدة: يشوف كشوفات وحدته
     * - مسؤول شعبة: يشوف كشوفات شعبته ووحداتها
     * - رئيس قسم: يشوف كل الكشوفات
     */
    private function buildQueryByRole($user)
    {
        // إذا كان رئيس قسم - يشوف الكل
        if ($user->hasRole('رئيس قسم') || $user->hasRole('admin')) {
            return Payroll::query();
        }

        // إذا كان مسؤول شعبة - يشوف شعبته ووحداتها
        if ($user->hasRole('مسؤول شعبة')) {
            return Payroll::where(function ($query) use ($user) {
                $query->where('created_by_department_id', $user->department_id)
                    ->orWhereIn('created_by_department_id', function ($subQuery) use ($user) {
                        $subQuery->select('id')
                            ->from('departments')
                            ->where('parent_id', $user->department_id);
                    });
            });
        }

        // إذا كان مسؤول وحدة - يشوف وحدته بس
        if ($user->hasRole('مسؤول وحدة')) {
            return Payroll::where('created_by_department_id', $user->department_id);
        }

        // موظف عادي - يشوف الكشوفات اللي أنشاها هو بس
        return Payroll::where('user_id', $user->id);
    }

    /**
     * تطبيق فلتر المنتسب المختار مع التحقق من الصلاحية
     * ملاحظة: يجب استدعاء هذه الدالة قبل تطبيق فلاتر التاريخ
     */
    private function applySelectedUserFilter(Request $request, $currentUser, $query)
    {
        $selectedUserId = (int) $request->query('selected_user_id', 0);

        if ($selectedUserId <= 0) {
            return null;
        }

        $isAllowed = (clone $this->buildQueryByRole($currentUser))
            ->where('user_id', $selectedUserId)
            ->exists();

        if (!$isAllowed) {
            return response()->json(['error' => 'Unauthorized selected user'], 403);
        }

        // ✨ نفلتر فقط السجل الأول (المنشئ) من كل kashf_no للمستخدم المختار
        // هذا يضمن أن فلاتر التاريخ اللاحقة ستطبق فقط على تاريخ الإنشاء الأصلي
        $query->whereRaw('id = (SELECT MIN(p2.id) FROM payrolls p2 WHERE p2.kashf_no = payrolls.kashf_no)')
            ->where('user_id', $selectedUserId);

        return null;
    }

    /**
     * تحويل رقم الشهر إلى اسمه بالعربية
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'يناير',
            2 => 'فبراير',
            3 => 'مارس',
            4 => 'إبريل',
            5 => 'مايو',
            6 => 'يونيو',
            7 => 'يوليو',
            8 => 'أغسطس',
            9 => 'سبتمبر',
            10 => 'أكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];

        return $months[$month] ?? '';
    }
}
