<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // الإحصائيات الأساسية
        $today = Carbon::now()->format('Y-m-d');
        $hasStatusColumn = Schema::hasColumn('payrolls', 'status');

        $statsData = [
            // الكشوفات
            'total_payrolls' => Payroll::distinct('kashf_no')->count('kashf_no'),
            'this_year_payrolls' => Payroll::whereYear('created_at', Carbon::now()->year)
                                          ->distinct('kashf_no')
                                          ->count('kashf_no'),
            'this_month_payrolls' => Payroll::whereMonth('created_at', Carbon::now()->month)
                                           ->whereYear('created_at', Carbon::now()->year)
                                           ->distinct('kashf_no')
                                           ->count('kashf_no'),
            'today_payrolls' => Payroll::whereDate('created_at', $today)
                                       ->distinct('kashf_no')
                                       ->count('kashf_no'),
            'archived_payrolls' => Payroll::where('is_archived', true)
                ->distinct('kashf_no')
                ->count('kashf_no'),
            'printed_payrolls' => Payroll::where('is_archived', false)
                ->where(function ($query) use ($hasStatusColumn) {
                    $query->where('print_count', '>', 0);
                    if ($hasStatusColumn) {
                        $query->orWhere('status', Payroll::STATUS_PRINTED);
                    }
                })
                ->distinct('kashf_no')
                ->count('kashf_no'),
            'ready_for_print_payrolls' => Payroll::where('is_archived', false)
                ->where(function ($query) {
                    $query->whereNull('print_count')->orWhere('print_count', 0);
                })
                ->when($hasStatusColumn, function ($query) {
                    $query->where(function ($statusQuery) {
                        $statusQuery->whereNull('status')->orWhere('status', Payroll::STATUS_READY_FOR_PRINT);
                    });
                })
                ->distinct('kashf_no')
                ->count('kashf_no'),

            // المبالغ
            'total_amount' => Payroll::sum('total_amount'),
            'this_year_amount' => Payroll::whereYear('created_at', Carbon::now()->year)
                                        ->sum('total_amount'),
            'this_month_amount' => Payroll::whereMonth('created_at', Carbon::now()->month)
                                         ->whereYear('created_at', Carbon::now()->year)
                                         ->sum('total_amount'),
            'today_amount' => Payroll::whereDate('created_at', $today)
                                     ->sum('total_amount'),

            // الموظفين
            'total_employees' => Employee::count(),
        ];

        // آخر الكشوفات (آخر 10)
        $recentPayrolls = Payroll::orderBy('created_at', 'desc')
                                  ->take(10)
                                  ->get()
                                  ->map(function($p) {
                                      return [
                                          'name' => $p->name,
                                          'department' => $p->department,
                                          'destination' => $p->destination,
                                          'total_amount' => $p->total_amount,
                                          'created_at' => $p->created_at->format('Y/m/d'),
                                          'id' => $p->id,
                                      ];
                                  });

        // آخر الموظفين المضافين (آخر 5)
        $recentEmployees = Employee::orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get()
                                    ->map(function($e) {
                                        return [
                                            'name' => $e->name,
                                            'employee_id' => $e->employee_id,
                                            'department' => $e->department,
                                            'job_title' => $e->job_title,
                                        ];
                                    });

        // إحصائيات حسب الجهات (Top 5)
        $topDestinations = Payroll::select('destination', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                                   ->groupBy('destination')
                                   ->orderByDesc('count')
                                   ->take(5)
                                   ->get();

        // الكشوفات في هذا الشهر
        $monthlyStats = Payroll::select(
                            DB::raw('DATE(created_at) as date'),
                            DB::raw('COUNT(*) as count'),
                            DB::raw('SUM(total_amount) as total')
                        )
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();

        $departmentMonthlyStats = Payroll::select(
                'department',
                DB::raw('COUNT(DISTINCT kashf_no) as payroll_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->groupBy('department')
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'statsData',
            'recentPayrolls',
            'recentEmployees',
            'topDestinations',
            'monthlyStats',
            'departmentMonthlyStats'
        ));
    }

    public function admin()
    {
        $stats = [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
        ];

        $roles = Role::withCount('users')->orderBy('name')->get();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'roles', 'recentUsers'));
    }

    /**
     * جلب الإحصائيات الديناميكية حسب الفيلترات
     */
    public function getStatsByFilter(Request $request)
    {
        $year = $request->get('year') ? (int)$request->get('year') : Carbon::now()->year;
        $month = $request->get('month') ? (int)$request->get('month') : null;
        $day = $request->get('day') ? (int)$request->get('day') : null;

        // بناء الـ Query للكشوفات
        $payrollsQuery = Payroll::whereYear('created_at', $year);
        $amountQuery = Payroll::whereYear('created_at', $year);

        // إذا تم اختيار شهر معين
        if ($month) {
            $payrollsQuery->whereMonth('created_at', $month);
            $amountQuery->whereMonth('created_at', $month);

            // إذا تم اختيار يوم معين
            if ($day) {
                $payrollsQuery->whereDay('created_at', $day);
                $amountQuery->whereDay('created_at', $day);
            }
        }

        $totalPayrolls = $payrollsQuery->distinct('kashf_no')->count('kashf_no');
        $totalAmount = $amountQuery->sum('total_amount') ?? 0;

        return response()->json([
            'total_payrolls' => $totalPayrolls,
            'total_amount' => $totalAmount,
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ]);
    }

    /**
     * جلب قائمة السنوات المتاحة
     */
    public function getAvailableYears()
    {
        $years = Payroll::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        return response()->json($years);
    }

    /**
     * جلب قائمة الأشهر المتاحة للسنة المحددة
     */
    public function getAvailableMonths(Request $request)
    {
        $year = (int)$request->get('year', Carbon::now()->year);

        $months = Payroll::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        $monthNames = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $monthsWithNames = [];
        foreach ($months as $month) {
            $monthsWithNames[] = [
                'value' => $month,
                'label' => $monthNames[$month] ?? "شهر $month"
            ];
        }

        return response()->json($monthsWithNames);
    }

    /**
     * جلب قائمة الأيام المتاحة للشهر المحدد
     */
    public function getAvailableDays(Request $request)
    {
        $year = (int)$request->get('year', Carbon::now()->year);
        $month = (int)$request->get('month');

        if (!$month) {
            return response()->json([]);
        }

        $days = Payroll::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DAY(created_at) as day')
            ->distinct()
            ->orderBy('day')
            ->pluck('day')
            ->toArray();

        return response()->json($days);
    }
}
