<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class EmployeesImport implements ToCollection, WithChunkReading
{
    public function collection(Collection $rows)
        {
            $data = [];
            foreach ($rows->skip(1) as $row) {

                // الملاحظة الذهبية: إذا كان حقل الـ ID فارغاً، نتجاهل السطر تماماً وننتقل للي بعده
                if (!isset($row[0]) || empty(trim($row[0]))) {
                    continue;
                }

                $data[] = [
                    'employee_id' => (string)$row[0],
                    'name'        => $row[1] ?? 'بدون اسم',
                    'department'  => $row[2] ?? '',
                    'salary'      => $row[3] ?? 0,
                    'job_title'   => $row[4] ?? null,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ];
            }

            if (!empty($data)) {
                // تنفيذ الـ upsert يضمن لنا عدم التكرار وتحديث البيانات إذا تغيرت في الإكسل
                \App\Models\Employee::upsert($data, ['employee_id'], ['name', 'department', 'salary', 'job_title', 'updated_at']);
            }
        }

    public function chunkSize(): int
    {
        return 1000; // يعالج كل 1000 اسم معاً
    }
}
