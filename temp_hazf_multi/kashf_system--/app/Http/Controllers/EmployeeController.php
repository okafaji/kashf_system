<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    // دالة المزامنة من الإكسل (CSV)
    public function syncFromNetwork()
{
    // زيادة وقت التنفيذ والذاكرة مؤقتاً لهذه العملية فقط
    ini_set('max_execution_time', 600); // 10 دقائق
    ini_set('memory_limit', '512M');

    $filePath = "D:\\laragon\\www\\kashf_system\\storage\\xls\\emp_clean.csv";

    if (!file_exists($filePath)) {
        return back()->with('error', 'الملف غير موجود!');
    }

    if (($file = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($file); // تخطي العنوان

        DB::beginTransaction();
        try {
            // مسح الجدول القديم إذا كنت تريد مزامنة كاملة وجديدة (أسرع بمليون مرة)
            // Employee::truncate();

            $dataToInsert = [];
            $count = 0;

            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $dataToInsert[] = [
                    'employee_id' => $data[4],
                    'name'        => $data[0],
                    'department'  => $data[1],
                    'job_title'   => $data[2],
                    'salary'      => $data[3],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                // تنفيذ الإدخال كل 1000 سجل لتوفير الذاكرة
                if (count($dataToInsert) >= 1000) {
                    Employee::upsert($dataToInsert, ['employee_id'], ['name', 'department', 'job_title', 'salary']);
                    $count += count($dataToInsert);
                    $dataToInsert = []; // تفريغ المصفوفة للدفعة القادمة
                }
            }

            // إدخال المتبقي
            if (!empty($dataToInsert)) {
                Employee::upsert($dataToInsert, ['employee_id'], ['name', 'department', 'job_title', 'salary']);
                $count += count($dataToInsert);
            }

            DB::commit();
            fclose($file);
            return back()->with('success', "تمت المزامنة بنجاح! معالجة $count موظف.");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
}

    // عرض جدول الموظفين
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('employee_id', 'LIKE', "%{$search}%")
                    ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        $employees = (clone $query)->latest()->paginate(15)->withQueryString();

        $departmentsCount = (clone $query)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->count('department');

        return view('employees.index', compact('employees', 'departmentsCount'));
    }

    // دالة البحث الفوري AJAX
    public function searchLive(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('q')) {
            $search = trim($request->q);

            // تقسيم البحث إلى كلمات منفصلة
            $keywords = array_filter(explode(' ', $search));

            $query->where(function ($builder) use ($search, $keywords) {
                // البحث في رقم الموظف أو القسم بالعبارة الكاملة
                $builder->where('employee_id', 'LIKE', "%{$search}%")
                    ->orWhere('department', 'LIKE', "%{$search}%");

                // البحث في الاسم: يجب أن يحتوي على كل الكلمات (AND)
                $builder->orWhere(function ($nameBuilder) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $nameBuilder->where('name', 'LIKE', "%{$keyword}%");
                    }
                });
            });

            // عند البحث، نعرض كل النتائج بدون pagination
            $employees = (clone $query)->latest()->get();
        } else {
            // بدون بحث، نعرض pagination عادي
            $employees = (clone $query)->latest()->paginate(15);
        }

        $departmentsCount = (clone $query)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->count('department');

        return response()->json([
            'employees' => $request->filled('q') ? $employees : $employees->items(),
            'total' => $request->filled('q') ? $employees->count() : $employees->total(),
            'departmentsCount' => $departmentsCount,
            'has_search' => $request->filled('q'),
        ]);
    }

    // دالة البحث Ajax لـ Select2
   public function getEmployeesAjax(Request $request)
{
    $q = $request->get('q');

    if (empty($q)) {
        return response()->json([]);
    }

    $employees = Employee::where('name', 'LIKE', "%{$q}%")
                        ->orWhere('employee_id', 'LIKE', "%{$q}%")
                        ->limit(20)
                        ->get();

    return response()->json($employees->map(function ($emp) {
        return [
            'id'       => $emp->employee_id, // ✅ عدلنا هنا
            'text'     => $emp->name . " [" . ($emp->department ?? 'بدون قسم') . "]",
            'dept'     => $emp->department ?? '',
            'job_title'=> $emp->job_title ?? 'موظف',
            'salary'   => $emp->salary ?? 0  // ✅ salary بدل daily_allowance
        ];
    }));
}

        public function checkDuplicate(Request $request) {
                $exists = Payroll::where('employee_name', $request->name)
                    ->where(function($query) use ($request) {
                        $query->whereBetween('start_date', [$request->start, $request->end])
                            ->orWhereBetween('end_date', [$request->start, $request->end]);
                    })->exists();

                if ($exists) {
                    return response()->json(['status' => 'error', 'message' => 'هذا الموظف لديه إيفاد فعلي في هذه الفترة!']);
                }
            }

}
