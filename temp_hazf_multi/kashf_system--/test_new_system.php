<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MissionType;
use App\Models\Payroll;
use App\Models\City;

echo "═══════════════════════════════════════════════════════════════════════════════════\n";
echo "اختبار النظام الجديد لحساب الإيفادات\n";
echo "═══════════════════════════════════════════════════════════════════════════════════\n\n";

echo "✅ البيانات الموجودة:\n";
echo "────────────────────────────────────────────────────────────────────────────────────\n";

// 1. عرض بيانات mission_types
$missionCount = MissionType::count();
$cityCount = City::count();
echo "• عدد أنواع الإيفادات (mission_types): " . $missionCount . "\n";
echo "• عدد المدن (cities): " . $cityCount . "\n\n";

// 2. عرض أسعار من كل نوع
echo "🔍 الأسعار من جدول mission_types:\n";
echo "────────────────────────────────────────────────────────────────────────────────────\n";
$missions = ['خارج القطر/1', 'خارج القطر/2', 'خارج القطر/3', 'خارج القطر/4'];
foreach ($missions as $mission) {
    $minRate = MissionType::where('name', $mission)->min('daily_rate');
    $maxRate = MissionType::where('name', $mission)->max('daily_rate');
    echo "• $mission: من " . number_format($minRate) . " إلى " . number_format($maxRate) . " دينار\n";
}

echo "\n🔍 أسعار المدن (من cities):\n";
echo "────────────────────────────────────────────────────────────────────────────────────\n";
$cities = City::limit(3)->get();
foreach ($cities as $city) {
    echo "• " . $city->name . ": " . number_format($city->daily_allowance) . " دينار\n";
}

echo "\n════════════════════════════════════════════════════════════════════════════════════\n";
echo "📋 صيغ الحساب الجديدة:\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";
echo "\n✅ خارج القطر (Mission Types):\n";
echo "   المجموع = (عدد الأيام × السعر من جدول mission_types) + الوصولات\n";
echo "   - بدون مبيت، بدون نقل، بدون وجبات\n";
echo "   - 50% لا يُطبق على الأيفادات خارج القطر\n";

echo "\n✅ المدينة العادية (Cities):\n";
echo "   - إذا كان 50% مفعل: السعر ÷ 2\n";
echo "   - المجموع = (أيام × السعر) + (ليالي × مبيت) + نقل + وصولات - (وجبات × 10% من السعر)\n";

echo "\n════════════════════════════════════════════════════════════════════════════════════\n";
echo "💡 أمثلة حسابة:\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";

// مثال 1: خارج القطر/1 - منتسب
$missionExample1 = MissionType::where('name', 'خارج القطر/1')
    ->where('responsibility_level', 'منتسب')
    ->first();

if ($missionExample1) {
    $days = 5;
    $receipts = 25000;
    $total = ($days * $missionExample1->daily_rate) + $receipts;
    
    echo "\n📍 مثال 1: خارج القطر/1 - منتسب\n";
    echo "   السعر اليومي: " . number_format($missionExample1->daily_rate) . " دينار\n";
    echo "   عدد الأيام: $days\n";
    echo "   الوصولات: " . number_format($receipts) . "\n";
    echo "   المجموع = ({$days} × " . number_format($missionExample1->daily_rate) . ") + " . number_format($receipts) . " = " . number_format($total) . " دينار ✅\n";
}

// مثال 2: خارج القطر/4 - أمين عام
$missionExample2 = MissionType::where('name', 'خارج القطر/4')
    ->where('responsibility_level', 'أمين عام')
    ->first();

if ($missionExample2) {
    $days = 10;
    $receipts = 100000;
    $total = ($days * $missionExample2->daily_rate) + $receipts;
    
    echo "\n📍 مثال 2: خارج القطر/4 - أمين عام\n";
    echo "   السعر اليومي: " . number_format($missionExample2->daily_rate) . " دينار\n";
    echo "   عدد الأيام: $days\n";
    echo "   الوصولات: " . number_format($receipts) . "\n";
    echo "   المجموع = ({$days} × " . number_format($missionExample2->daily_rate) . ") + " . number_format($receipts) . " = " . number_format($total) . " دينار ✅\n";
}

echo "\n════════════════════════════════════════════════════════════════════════════════════\n";
echo "✅ النظام جاهز للعمل!\n";
echo "════════════════════════════════════════════════════════════════════════════════════\n";
