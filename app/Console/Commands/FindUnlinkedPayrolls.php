<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\Employee;

class FindUnlinkedPayrolls extends Command
{
    protected $signature = 'payroll:find-unlinked {--similar}';
    protected $description = 'عرض الكشوفات التي لم يتم ربطها بالموظفين';

    public function handle()
    {
        $unlinkedPayrolls = Payroll::whereNull('employee_id')
            ->pluck('name')
            ->unique()
            ->values();

        $this->info("\n📋 الأسماء التي لم يتم العثور على موظفين لها:");
        $this->info(str_repeat('=', 80));

        foreach ($unlinkedPayrolls as $i => $name) {
            $count = Payroll::where('name', $name)->whereNull('employee_id')->count();
            $this->line(($i + 1) . ". $name (" . $count . " كشف)");
        }

        $this->info(str_repeat('=', 80));
        $this->info("الإجمالي: " . $unlinkedPayrolls->count() . " أسماء فريدة لم يتم ربطها");

        // إذا كان هناك الخيار --similar، ابحث عن أسماء متشابهة
        if ($this->option('similar')) {
            $this->info("\n\n🔍 البحث عن أسماء متشابهة في جدول الموظفين:");
            $this->info(str_repeat('=', 80));

            foreach ($unlinkedPayrolls as $payrollName) {
                $firstWord = trim(explode(' ', $payrollName)[0]);

                $similarEmployees = Employee::where('name', 'LIKE', '%' . $firstWord . '%')
                    ->limit(3)
                    ->get(['employee_id', 'name']);

                if ($similarEmployees->count() > 0) {
                    $this->warn("\n📝 " . $payrollName);
                    foreach ($similarEmployees as $emp) {
                        $this->line("   → {$emp->employee_id} - {$emp->name}");
                    }
                }
            }
        }
    }
}
