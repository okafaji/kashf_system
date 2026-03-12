<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

use App\Models\Signature;

// Clear existing signatures
Signature::query()->delete();
echo "تم حذف البيانات القديمة\n";

// Insert new signatures
Signature::create([
    'title' => 'مسؤول وحدة',
    'name' => 'مسؤول الوحدة',
    'is_active' => true,
]);

Signature::create([
    'title' => 'مسؤول الشعبة',
    'name' => 'مسؤول الشعبة',
    'is_active' => true,
]);

Signature::create([
    'title' => 'قسم التدقيق',
    'name' => 'موظف التدقيق المالي',
    'is_active' => true,
]);

Signature::create([
    'title' => 'رئيس قسم الشؤون المالية',
    'name' => 'رئيس قسم الشؤون المالية',
    'is_active' => true,
]);

echo "تم إضافة البيانات الجديدة بنجاح!\n";
echo "الإجمالي: " . Signature::count() . " توقيع\n";

// Display all signatures
$sigs = Signature::all();
foreach($sigs as $sig) {
    echo "- العنوان: {$sig->title}, الاسم: {$sig->name}\n";
}
