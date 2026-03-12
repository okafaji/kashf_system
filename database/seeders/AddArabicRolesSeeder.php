<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AddArabicRolesSeeder extends Seeder
{
    public function run()
    {
        // التحقق من وجود الأدوار العربية وإضافتها إن لم تكن موجودة
        $roles = [
            'رئيس قسم',
            'مسؤول شعبة',
            'مسؤول وحدة',
        ];

        foreach ($roles as $roleName) {
            // البحث عن الدور أو إنشاء واحد جديد
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        $this->command->info('تم إضافة الأدوار العربية بنجاح! ✓');
    }
}
