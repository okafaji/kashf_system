<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;

class CheckLinkingStats extends Command
{
    protected $signature = 'payroll:check-stats';
    protected $description = 'Check payroll linking statistics';

    public function handle()
    {
        $total = Payroll::count();
        $linked = Payroll::whereNotNull('employee_id')->count();
        $unlinked = $total - $linked;
        $percentage = $total > 0 ? (($linked / $total) * 100) : 0;

        $this->info("\n========================================");
        $this->info("📊 تقرير ربط الكشوفات");
        $this->info("========================================");
        $this->line("✅ الإجمالي:        $total");
        $this->line("🔗 المربوطة:       $linked");
        $this->line("❌ غير المربوطة:   $unlinked");
        $this->line("📈 النسبة المئوية: " . number_format($percentage, 2) . "%");
        $this->info("========================================\n");

        return 0;
    }
}
