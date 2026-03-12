<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;

echo "=== إصلاح الكشوفات المؤرشفة خطأً ===\n\n";

// الكشوفات المضافة اليوم (أو في آخر 3 أيام) والمؤرشفة
$recentArchived = Payroll::where('is_archived', true)
    ->where('created_at', '>=', now()->subDays(3))
    ->get();

if ($recentArchived->isEmpty()) {
    echo "لا توجد كشوفات مؤرشفة حديثة للإصلاح\n";
} else {
    echo "عدد الكشوفات المؤرشفة خطأً: " . $recentArchived->count() . "\n\n";
    echo "هل تريد إلغاء أرشفة هذه الكشوفات؟ (y/n): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim(strtolower($line));
    
    if ($answer === 'y' || $answer === 'yes' || $answer === 'نعم') {
        $updated = Payroll::where('is_archived', true)
            ->where('created_at', '>=', now()->subDays(3))
            ->update(['is_archived' => false]);
        
        echo "\n✅ تم إصلاح {$updated} كشف بنجاح!\n";
        
        // عرض الكشوفات المصلحة
        echo "\nالكشوفات المصلحة:\n";
        $fixed = Payroll::where('created_at', '>=', now()->subDays(3))
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($fixed as $p) {
            echo "ID: {$p->id} | kashf_no: {$p->kashf_no} | {$p->name} | is_archived: false\n";
        }
    } else {
        echo "\nتم الإلغاء.\n";
    }
    
    fclose($handle);
}

echo "\nانتهى.\n";
