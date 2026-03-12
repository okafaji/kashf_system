<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    $app = require_once 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "=== Raw Query Results ===\n";
    $results = DB::table('mission_types')
        ->where('name', 'خارج القطر/1')
        ->select('id', 'name', 'responsibility_level', 'daily_rate')
        ->get();

    foreach ($results as $r) {
        echo "ID: {$r->id} | {$r->name} | '{$r->responsibility_level}' | {$r->daily_rate}\n";
    }

    echo "\n=== Check for 'مسؤول وجبة' vs 'مسؤول وحدة' ===\n";
    $count_wajba = DB::table('mission_types')->where('responsibility_level', 'مسؤول وجبة')->count();
    $count_wahda = DB::table('mission_types')->where('responsibility_level', 'مسؤول وحدة')->count();

    echo "مسؤول وجبة: $count_wajba\n";
    echo "مسؤول وحدة: $count_wahda\n";
    
    echo "\n✅ Script completed successfully\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
