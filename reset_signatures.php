<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

// تنظيف الجدول
$kernel->call('migrate:refresh', ['--seeder' => 'SignatureSeeder']);

echo "تم تحديث البيانات بنجاح!\n";
