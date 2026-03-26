<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$outside = \App\Models\MissionType::where('name', 'خارج القطر/1')->where('responsibility_level', 'منتسب')->first();

if ($outside) {
    echo 'Found: ' . $outside->name . ' | ' . $outside->responsibility_level . ' | ' . $outside->daily_rate . PHP_EOL;
} else {
    echo 'Not found!' . PHP_EOL;
}

$all = \App\Models\MissionType::where('name', 'خارج القطر/1')->get();
echo 'Total for خارج القطر/1: ' . $all->count() . PHP_EOL;
foreach ($all as $m) {
    echo '  - ' . $m->responsibility_level . ': ' . $m->daily_rate . PHP_EOL;
}

echo "\n=== Testing JSON serialization ===\n";
$missions = \App\Models\MissionType::select('name', 'responsibility_level', 'daily_rate')->limit(5)->get();
echo json_encode($missions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
