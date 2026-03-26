<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء permission إذا لم يكن موجوداً
        $permission = Permission::firstOrCreate(
            ['name' => 'manage-backups'],
            ['guard_name' => 'web']
        );

        // إسناد الـ permission إلى role admin (مع دعم الاسم القديم Admin)
        $adminRole = Role::whereIn('name', ['admin', 'Admin'])->first();
        if ($adminRole && !$adminRole->hasPermissionTo('manage-backups')) {
            $adminRole->givePermissionTo('manage-backups');
        }

        $this->command->info('✅ Backup permission created and assigned to Admin role');
    }
}
