<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Payroll;
use Illuminate\Database\Seeder;

class CreateTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('جاري إنشاء بيانات الاختبار...');

        // إنشاء الأقسام إذا لم تكن موجودة
        $mainDept = Department::firstOrCreate(
            ['name' => 'الديوان العام'],
            ['name' => 'الديوان العام', 'parent_id' => null]
        );

        $dept1 = Department::firstOrCreate(
            ['name' => 'شعبة الموارد البشرية'],
            ['name' => 'شعبة الموارد البشرية', 'parent_id' => $mainDept->id]
        );

        $unit1 = Department::firstOrCreate(
            ['name' => 'وحدة التوظيف'],
            ['name' => 'وحدة التوظيف', 'parent_id' => $dept1->id]
        );

        // إنشاء مستخدمين للاختبار
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'رئيس القسم التجريبي',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'department_id' => $mainDept->id,
            ]
        );
        $admin->syncRoles('رئيس قسم');

        $manager = User::firstOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'مسؤول الشعبة',
                'email' => 'manager@test.com',
                'password' => bcrypt('password'),
                'department_id' => $dept1->id,
            ]
        );
        $manager->syncRoles('مسؤول شعبة');

        $unitManager = User::firstOrCreate(
            ['email' => 'unitmanager@test.com'],
            [
                'name' => 'مسؤول الوحدة',
                'email' => 'unitmanager@test.com',
                'password' => bcrypt('password'),
                'department_id' => $unit1->id,
            ]
        );
        $unitManager->syncRoles('مسؤول وحدة');

        $employee = User::firstOrCreate(
            ['email' => 'employee@test.com'],
            [
                'name' => 'الموظف العادي',
                'email' => 'employee@test.com',
                'password' => bcrypt('password'),
                'department_id' => $unit1->id,
            ]
        );
        $employee->syncRoles('data-entry');

        // إنشاء بعض الكشوفات للاختبار
        for ($i = 1; $i <= 5; $i++) {
            Payroll::create([
                'user_id' => $employee->id,
                'created_by_department_id' => $unit1->id,
                'name' => "الموظف التجريبي $i",
                'department' => 'وحدة التوظيف',
                'destination' => 'مصر',
                'admin_order_no' => "AO-$i",
                'receipt_no' => "RCP-$i",
                'kashf_no' => 100 + $i,
                'daily_allowance' => 100,
                'total_amount' => 5000 + ($i * 1000),
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }

        $this->command->info('✅ تم إنشاء بيانات الاختبار بنجاح!');
        $this->command->info('');
        $this->command->info('بيانات الدخول للاختبار:');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('رئيس قسم: admin@test.com / password');
        $this->command->info('مسؤول شعبة: manager@test.com / password');
        $this->command->info('مسؤول وحدة: unitmanager@test.com / password');
        $this->command->info('موظف عادي: employee@test.com / password');
        $this->command->info('═══════════════════════════════════════════');
    }
}
