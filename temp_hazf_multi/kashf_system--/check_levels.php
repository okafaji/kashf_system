<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$levels = \App\Models\MissionType::select('responsibility_level')->distinct()->get()->pluck('responsibility_level')->sort()->values();

echo "=== Unique Responsibility Levels in Database ===\n";
foreach ($levels as $level) {
    echo "- $level\n";
}
echo "\n=== Responsibility Levels in Form ===\n";
$formLevels = [
    'منتسب',
    'مسؤول شعبة',
    'مسؤول وجبة',
    'معاون',
    'رئيس',
    'عضو',
    'مستشار',
    'نائب أمين عام',
    'أمين عام'
];
foreach ($formLevels as $level) {
    echo "- $level\n";
}
