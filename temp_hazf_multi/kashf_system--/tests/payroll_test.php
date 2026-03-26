<?php

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Payroll;

require_once __DIR__ . '/../vendor/autoload.php';

// إعداد الاتصال بقاعدة البيانات (Laravel أو Eloquent)
// ... إعداد الاتصال حسب مشروعك ...

// تجربة إدخال سجل برقم إداري 1 وسنة 2026 وكشف موحد 100
$payload = [
    'name' => 'اختبار',
    'department' => 'تجربة',
    'admin_order_no' => 1,
    'order_year' => 2026,
    'receipt_no' => 100,
    'admin_order_date' => '2026-02-16',
    'destination' => 'بغداد',
    'city_id' => 1,
    'governorate_id' => 1,
    'job_title' => 'موظف',
    'start_date' => '2026-02-16',
    'end_date' => '2026-02-17',
    'days_count' => 1,
    'daily_allowance' => 1000,
    'accommodation_fee' => 0,
    'transportation_fee' => 0,
    'meals_count' => 0,
    'receipts_amount' => 0,
    'is_half_allowance' => false,
    'total_amount' => 1000,
    'kashf_no' => 1,
    'is_archived' => false,
    'notes' => '',
];

// تحقق من وجود سجل بنفس رقم الأمر الإداري وسنة الأمر وكشف موحد مختلف
$existingPayroll = Payroll::where('admin_order_no', $payload['admin_order_no'])
    ->where('order_year', $payload['order_year'])
    ->where('receipt_no', '!=', $payload['receipt_no'])
    ->first();

if ($existingPayroll) {
    echo "يوجد سجل بنفس رقم الأمر الإداري في كشف آخر بنفس السنة!";
} else {
    Payroll::create($payload);
    echo "تمت الإضافة بنجاح";
}
