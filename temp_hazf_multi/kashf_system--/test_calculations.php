<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MissionType;
use App\Models\Payroll;

echo "═══════════════════════════════════════════════════════════════════════════════════\n";
echo "اختبار حسابات الإيفاد الجديدة\n";
echo "═══════════════════════════════════════════════════════════════════════════════════\n\n";

// اختبار 1: خارج القطر/1 - منتسب
$missionType = MissionType::where('name', 'خارج القطر/1')
    ->where('responsibility_level', 'منتسب')
    ->first();

if ($missionType) {
    echo "✓ اختبار 1: خارج القطر/1 - منتسب\n";
    echo "  - ID: " . $missionType->id . "\n";
    echo "  - السعر اليومي: " . $missionType->daily_rate . " دينار\n";
    echo "  - المستوى: " . $missionType->responsibility_level . "\n";
    
    // محاكاة حساب: 5 أيام + 50000 وصولات
    $days = 5;
    $receipts = 50000;
    $total = ($days * $missionType->daily_rate) + $receipts;
    echo "  - الحساب (5 أيام + 50,000 وصولات): " . $total . " دينار\n";
    echo "    = (5 × " . $missionType->daily_rate . ") + 50,000 = " . $total . "\n\n";
} else {
    echo "✗ خطأ: لم يتم العثور على خارج القطر/1 - منتسب\n\n";
}

// اختبار 2: خارج القطر/4 - أمين عام
$missionType2 = MissionType::where('name', 'خارج القطر/4')
    ->where('responsibility_level', 'أمين عام')
    ->first();

if ($missionType2) {
    echo "✓ اختبار 2: خارج القطر/4 - أمين عام\n";
    echo "  - ID: " . $missionType2->id . "\n";
    echo "  - السعر اليومي: " . $missionType2->daily_rate . " دينار\n";
    echo "  - المستوى: " . $missionType2->responsibility_level . "\n";
    
    // محاكاة حساب: 10 أيام + 100000 وصولات
    $days = 10;
    $receipts = 100000;
    $total = ($days * $missionType2->daily_rate) + $receipts;
    echo "  - الحساب (10 أيام + 100,000 وصولات): " . $total . " دينار\n";
    echo "    = (10 × " . $missionType2->daily_rate . ") + 100,000 = " . $total . "\n\n";
} else {
    echo "✗ خطأ: لم يتم العثور على خارج القطر/4 - أمين عام\n\n";
}

// اختبار 3: معلومات ملخصة
echo "════════════════════════════════════════════════════════════════════════════════════\n";
echo "ملخص البيانات في جدول mission_types:\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";

$missions = ['خارج القطر/1', 'خارج القطر/2', 'خارج القطر/3', 'خارج القطر/4'];
foreach ($missions as $mission) {
    $count = MissionType::where('name', $mission)->count();
    $maxRate = MissionType::where('name', $mission)->max('daily_rate');
    $minRate = MissionType::where('name', $mission)->min('daily_rate');
    
    echo sprintf("%-20s | العدد: %2d | الحد الأدنى: %7d | الحد الأقصى: %7d\n",
        $mission, $count, $minRate, $maxRate
    );
}

echo "\n════════════════════════════════════════════════════════════════════════════════════\n";
echo "✅ صيغة الحساب الجديدة:\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";
echo "خارج القطر:\n";
echo "  total_amount = (days_count × daily_rate من mission_types) + receipts_amount\n";
echo "  - بدون إضافات أخرى (بدون مبيت، بدون نقل، بدون وجبات)\n\n";
echo "المدينة العادية:\n";
echo "  total_amount = (days_count × daily_allowance من cities)\n";
echo "              + (nights × accommodation_fee)\n";
echo "              + transportation_fee\n";
echo "              + receipts_amount\n";
echo "              - (meals_count × daily_allowance × 10%)\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";
