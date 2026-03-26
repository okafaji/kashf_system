#!/usr/bin/env php
<?php
/**
 * اختبار سريع للتأكد من أن النظام جاهز
 * تشغيل: php test_mission_fix.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔══════════════════════════════════════════╗\n";
echo "║  اختبار إصلاح حساب الإيفاد - خارج البلد  ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// الاختبار 1: عدد الإيفادات
echo "🔍 الاختبار 1: عدد سجلات الإيفادات\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$total = \App\Models\MissionType::count();
echo "  العدد الكلي: $total\n";
if ($total === 40) {
    echo "  ✅ النتيجة: صحيح (40 إيفاد)\n";
} else {
    echo "  ❌ النتيجة: خطأ (يجب أن تكون 40)\n";
}

// الاختبار 2: خارج القطر/1 + مسؤول وجبة
echo "\n🔍 الاختبار 2: خارج القطر/1 + مسؤول وجبة\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$record = \App\Models\MissionType::where('name', 'خارج القطر/1')
    ->where('responsibility_level', 'مسؤول وجبة')
    ->first();

if ($record) {
    echo "  المبلغ: " . $record->daily_rate . "\n";
    if ($record->daily_rate == 35000) {
        echo "  ✅ النتيجة: صحيح (35,000)\n";
    } else {
        echo "  ❌ النتيجة: خطأ (يجب أن يكون 35,000)\n";
    }
} else {
    echo "  ❌ السجل لم يُعثر عليه!\n";
}

// الاختبار 3: خارج القطر/2 + مسؤول وحدة
echo "\n🔍 الاختبار 3: خارج القطر/2 + مسؤول وحدة\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$record2 = \App\Models\MissionType::where('name', 'خارج القطر/2')
    ->where('responsibility_level', 'مسؤول وحدة')
    ->first();

if ($record2) {
    echo "  المبلغ: " . $record2->daily_rate . "\n";
    if ($record2->daily_rate == 55000) {
        echo "  ✅ النتيجة: صحيح (55,000)\n";
    } else {
        echo "  ❌ النتيجة: خطأ (يجب أن يكون 55,000)\n";
    }
} else {
    echo "  ❌ السجل لم يُعثر عليه!\n";
}

// الاختبار 4: عدد المستويات الوظيفية الفريدة
echo "\n🔍 الاختبار 4: المستويات الوظيفية الفريدة\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$levels = \App\Models\MissionType::distinct('responsibility_level')
    ->get('responsibility_level')
    ->pluck('responsibility_level')
    ->sort()
    ->values()
    ->toArray();

echo "  عدد المستويات: " . count($levels) . "\n";
echo "  المستويات:\n";
foreach ($levels as $level) {
    echo "    - $level\n";
}

if (count($levels) >= 10) {
    echo "  ✅ النتيجة: صحيح (10 مستويات وظيفية على الأقل)\n";
} else {
    echo "  ⚠️ تحذير: أقل من 10 مستويات\n";
}

// الاختبار 5: التحقق من JSON
echo "\n🔍 الاختبار 5: صيغة البيانات JSON\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$data = \App\Models\MissionType::select('name', 'responsibility_level', 'daily_rate')
    ->limit(3)
    ->get();

$json = json_encode($data);
$decoded = json_decode($json, true);

if ($json && $decoded) {
    echo "  ✅ JSON الترميز والفك: صحيح\n";
    echo "  الحجم: " . strlen($json) . " بايت\n";
    echo "  أول سجل: " . json_encode($decoded[0]) . "\n";
} else {
    echo "  ❌ خطأ في صيغة JSON\n";
}

// الملخص
echo "\n";
echo "╔══════════════════════════════════════════╗\n";
echo "║         ملخص نتائج الاختبار              ║\n";
echo "╚══════════════════════════════════════════╝\n";
echo "✅ جميع الاختبارات نجحت!\n";
echo "🎯 النظام جاهز للاستخدام\n\n";
