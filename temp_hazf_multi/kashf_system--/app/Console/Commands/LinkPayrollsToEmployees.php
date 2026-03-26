<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\Employee;

class LinkPayrollsToEmployees extends Command
{
    protected $signature = 'payroll:link-employees {--skip-existing}';
    protected $description = 'ربط الكشوفات القديمة بالموظفين بناءً على تطابق الأسماء';

    public function handle()
    {
        $this->info('🔄 بدء ربط الكشوفات بالموظفين...');

        // الحصول على جميع الكشوف
        $query = Payroll::query();

        // إذا كان الخيار --skip-existing موجود، تخطى الكشوف التي لها employee_id بالفعل
        if ($this->option('skip-existing')) {
            $query->whereNull('employee_id');
        }

        $payrolls = $query->get();
        $totalPayrolls = $payrolls->count();
        $linkedCount = 0;
        $notFoundCount = 0;

        $this->info("📊 إجمالي الكشوفات للمعالجة: $totalPayrolls");

        foreach ($payrolls as $payroll) {
            // البحث عن موظف بنفس الاسم (مطابقة تامة)
            $employee = Employee::where('name', trim($payroll->name))->first();

            if ($employee) {
                $payroll->update(['employee_id' => $employee->employee_id]);
                $linkedCount++;
                $this->line("✅ {$payroll->name} -> {$employee->employee_id}");
            } else {
                $notFoundCount++;
                $this->warn("❌ لم يتم العثور على موظف باسم: {$payroll->name}");
            }
        }

        // النتائج النهائية
        $this->info("\n" . str_repeat('=', 60));
        $this->info("📈 النتائج النهائية:");
        $this->info("✅ عدد الكشوفات المربوطة: $linkedCount");
        $this->info("❌ عدد الكشوفات غير المربوطة: $notFoundCount");
        $this->info("📊 النسبة: " . round(($linkedCount / $totalPayrolls) * 100, 2) . "%");
        $this->info(str_repeat('=', 60));
    }
}
