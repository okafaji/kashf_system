<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$missions = \App\Models\MissionType::select('id', 'name', 'responsibility_level', 'daily_rate')->get();

echo "=== Mission Types Data ===\n";
foreach ($missions as $m) {
    echo $m->name . ' | ' . $m->responsibility_level . ' | ' . $m->daily_rate . "\n";
}
echo "\nTotal: " . $missions->count() . " records\n";
