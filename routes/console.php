<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Department;
use App\Models\Employee;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('departments:sync-from-employees', function () {
    $normalize = function (?string $value): string {
        if ($value === null) {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($value));
        return $normalized ?? '';
    };

    $employeeDepartments = Employee::query()
        ->whereNotNull('department')
        ->pluck('department');

    $existingMap = Department::query()
        ->pluck('name')
        ->mapWithKeys(function ($name) use ($normalize) {
            $key = $normalize($name);
            return $key === '' ? [] : [$key => true];
        });

    $toInsert = [];

    foreach ($employeeDepartments as $departmentName) {
        $cleanName = $normalize($departmentName);

        if ($cleanName === '' || isset($existingMap[$cleanName]) || isset($toInsert[$cleanName])) {
            continue;
        }

        $toInsert[$cleanName] = [
            'name' => $cleanName,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    if (empty($toInsert)) {
        $this->info('لا توجد أقسام جديدة لإضافتها.');
        return;
    }

    Department::query()->insert(array_values($toInsert));

    $this->info('تمت إضافة ' . count($toInsert) . ' قسم(أقسام) جديد بنجاح.');
})->purpose('Sync unique department names from employees table into departments table');
