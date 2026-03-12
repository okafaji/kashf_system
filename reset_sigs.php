<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$db = $app->make('Illuminate\Database\Connection');

// حذف البيانات القديمة
$db->statement('DELETE FROM signatures');
echo "تم حذف البيانات القديمة\n";

// تشغيل السيدر
$seeder = new Database\Seeders\SignatureSeeder();
$seeder->run();

echo "تم إضافة البيانات الجديدة بنجاح!\n";
