<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ResetUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->delete();

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->delete();

        DB::table('users')->delete();

        $users = [
            [
                'name' => 'المدير العام',
                'email' => 'admin@kashf.com',
                'role' => 'admin',
            ],
            [
                'name' => 'مدير الكشوف',
                'email' => 'payroll@kashf.com',
                'role' => 'payroll-manager',
            ],
            [
                'name' => 'مدخل بيانات',
                'email' => 'data@kashf.com',
                'role' => 'data-entry',
            ],
            [
                'name' => 'مشاهد',
                'email' => 'viewer@kashf.com',
                'role' => 'viewer',
            ],
            [
                'name' => 'مدير الموظفين',
                'email' => 'hr@kashf.com',
                'role' => 'employee-manager',
            ],
        ];

        foreach ($users as $entry) {
            $user = User::create([
                'name' => $entry['name'],
                'email' => $entry['email'],
                'password' => Hash::make('Password123!'),
            ]);

            if (Role::where('name', $entry['role'])->exists()) {
                $user->assignRole($entry['role']);
            }
        }
    }
}
