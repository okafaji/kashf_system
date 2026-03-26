<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;

echo "=== الكشوفات المضافة اليوم ===\n\n";

$today = \Carbon\Carbon::today();
$payrolls = Payroll::where('created_at', '>=', $today)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($payrolls->isEmpty()) {
    echo "لا توجد كشوفات مضافة اليوم\n";
} else {
    echo "عدد الكشوفات: " . $payrolls->count() . "\n\n";
    
    foreach ($payrolls as $p) {
        echo "ID: {$p->id} | ";
        echo "kashf_no: {$p->kashf_no} | ";
        echo "name: {$p->name} | ";
        echo "is_archived: " . ($p->is_archived ? 'true' : 'false') . " | ";
        echo "created_at: {$p->created_at}\n";
    }
}

echo "\n=== إحصائيات الكشوفات ===\n\n";

$total = Payroll::count();
$archived = Payroll::where('is_archived', true)->count();
$notArchived = Payroll::where(function($q) {
    $q->where('is_archived', false)->orWhereNull('is_archived');
})->count();

echo "إجمالي الكشوفات: {$total}\n";
echo "المؤرشفة: {$archived}\n";
echo "غير المؤرشفة: {$notArchived}\n";

echo "\n=== آخر 5 kashf_no ===\n\n";

$groups = Payroll::where(function($q) {
        $q->where('is_archived', false)->orWhereNull('is_archived');
    })
    ->select('kashf_no')
    ->selectRaw('COUNT(*) as count')
    ->selectRaw('MAX(created_at) as last_created')
    ->groupBy('kashf_no')
    ->orderBy('kashf_no', 'desc')
    ->limit(5)
    ->get();

foreach ($groups as $g) {
    echo "kashf_no: {$g->kashf_no} | عدد السجلات: {$g->count} | آخر إضافة: {$g->last_created}\n";
}
