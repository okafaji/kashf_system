<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rows = \App\Models\MissionType::query()
    ->where('name', 'like', 'خارج القطر/%')
    ->orderBy('name')
    ->orderBy('responsibility_level')
    ->get(['name', 'responsibility_level', 'daily_rate']);

echo "Outside mission rows: " . $rows->count() . PHP_EOL;

$grouped = $rows->groupBy('name');
foreach ($grouped as $name => $items) {
    echo PHP_EOL . "=== {$name} ===" . PHP_EOL;
    foreach ($items as $item) {
        echo "- level: [{$item->responsibility_level}] | rate: {$item->daily_rate}" . PHP_EOL;
    }
}

echo PHP_EOL . "Distinct responsibility levels for outside missions:" . PHP_EOL;
$levels = $rows->pluck('responsibility_level')->unique()->values();
foreach ($levels as $level) {
    echo "* [{$level}]" . PHP_EOL;
}
