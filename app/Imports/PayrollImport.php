<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class PayrollImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // تحديد مواقع الأعمدة حسب الرأس
        $headers = $rows->first()->keys();
        $sectionColumn = null;
        $destinationColumn = null;

        foreach ($headers as $header) {
            if (stripos($header, 'قسم') !== false) {
                $sectionColumn = $header;
            }
            if (stripos($header, 'وجهة') !== false) {
                $destinationColumn = $header;
            }
        }

        // جلب القسم من أول صف بعد الرأس
        $sectionValue = null;
        if ($sectionColumn) {
            $sectionValue = $rows->first()[$sectionColumn];
        }

        foreach ($rows as $row) {
            $employeeName = $row['اسم المنتسب'] ?? $row['name'] ?? '';
            $orderNumber = $row['رقم الأمر'] ?? $row['order_number'] ?? '';
            $period = $row['الفترة'] ?? $row['period'] ?? '';
            $destination = $destinationColumn ? ($row[$destinationColumn] ?? '') : '';

            // إذا وجد عمود القسم، استخدم القيمة الموحدة
            $section = $sectionValue ?? '';

            // هنا تضع شروط التكرار والتحقق
            // مثال: منع تكرار رقم الأمر أو الفترة

            // مثال تخزين البيانات
            // Payroll::create([
            //     'name' => $employeeName,
            //     'order_number' => $orderNumber,
            //     'period' => $period,
            //     'section' => $section,
            //     'destination' => $destination,
            // ]);

            // ... باقي الكود حسب الحاجة ...
        }
    }
}
