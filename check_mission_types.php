<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MissionType;

echo "═══════════════════════════════════════════════════════════\n";
echo "عدد السجلات الكلي: " . MissionType::count() . " سجل\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "أول 10 سجلات:\n";
echo str_repeat("─", 80) . "\n";
MissionType::limit(10)->get()->each(function($item) {
    echo sprintf("ID: %2d | %25s | %15s | %7d IQD\n", 
        $item->id, 
        mb_substr($item->name, 0, 25, 'UTF-8'),
        $item->responsibility_level, 
        $item->daily_rate
    );
});

echo "\n" . str_repeat("─", 80) . "\n";
echo "آخر 10 سجلات:\n";
echo str_repeat("─", 80) . "\n";
MissionType::orderBy('id', 'desc')->limit(10)->orderBy('id')->get()->each(function($item) {
    echo sprintf("ID: %2d | %25s | %15s | %7d IQD\n", 
        $item->id, 
        mb_substr($item->name, 0, 25, 'UTF-8'),
        $item->responsibility_level, 
        $item->daily_rate
    );
});

echo "\n═══════════════════════════════════════════════════════════\n";
echo "✅ تم تحديث البيانات بنجاح!\n";
echo "═══════════════════════════════════════════════════════════\n";
