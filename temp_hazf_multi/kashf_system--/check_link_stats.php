<?php
require 'bootstrap/app.php';

$app = new \Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    \Illuminate\Contracts\Http\Kernel::class,
    \App\Http\Kernel::class,
);

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\Payroll::count();
$linked = \App\Models\Payroll::whereNotNull('employee_id')->count();
$unlinked = $total - $linked;

echo "\n========================================\n";
echo "📊 **تقرير ربط الكشوفات**\n";
echo "========================================\n";
echo "✅ الإجمالي: $total\n";
echo "🔗 المربوطة: $linked\n";
echo "❌ غير المربوطة: $unlinked\n";
echo "📈 النسبة المئوية: " . number_format(($linked/$total)*100, 2) . "%\n";
echo "========================================\n\n";
