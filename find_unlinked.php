<?php

// تشغيل في PHP Artisan Tinker
// php artisan tinker
// ثم انسخ والصق الكود:

// 1. الحصول على الأسماء التي لم يتم ربطها
$unlinkedPayrolls = App\Models\Payroll::whereNull('employee_id')->pluck('name')->unique()->values();

echo "📋 الأسماء التي لم يتم العثور على موظفين لها:\n";
echo str_repeat('=', 60) . "\n";
foreach ($unlinkedPayrolls as $i => $name) {
    echo ($i + 1) . ". " . $name . "\n";
}
echo str_repeat('=', 60) . "\n";
echo "الإجمالي: " . $unlinkedPayrolls->count() . " أسماء فريدة\n";

// 2. الحصول على الأسماء المتشابهة في الموظفين للبحث
echo "\n🔍 ابحث عن أسماء متشابهة في جدول الموظفين:\n";
echo str_repeat('=', 60) . "\n";
foreach ($unlinkedPayrolls as $payrollName) {
    $similarEmployees = App\Models\Employee::where('name', 'LIKE', '%' . trim(explode(' ', $payrollName)[0]) . '%')
        ->limit(3)
        ->get(['employee_id', 'name']);

    if ($similarEmployees->count() > 0) {
        echo "📝 $payrollName\n";
        foreach ($similarEmployees as $emp) {
            echo "   → {$emp->employee_id} - {$emp->name}\n";
        }
        echo "\n";
    }
}
