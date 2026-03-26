<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\Employee;

class LinkPayrollManual extends Command
{
    protected $signature = 'payroll:link-manual {name} {employee_id}';
    protected $description = 'ربط اسم موظف بـ employee_id يدويًا';

    public function handle()
    {
        $name = trim($this->argument('name'));
        $employeeId = trim($this->argument('employee_id'));

        // التحقق من وجود الموظف
        $employee = Employee::find($employeeId);

        if (!$employee) {
            $this->error("❌ لا يوجد موظف برقم: $employeeId");
            return 1;
        }

        // تحديث جميع الكشوفات بهذا الاسم
        $count = Payroll::where('name', $name)
            ->whereNull('employee_id')
            ->update(['employee_id' => $employeeId]);

        $this->info("✅ تم ربط $count كشف باسم: $name");
        $this->info("✓ الموظف: {$employee->employee_id} - {$employee->name}");

        return 0;
    }
}
