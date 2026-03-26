<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignAdminToAllUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
                $this->command->info("تم منح دور المدير العام للمستخدم: {$user->email}");
            }
        }

        $this->command->info('تم منح دور المدير العام لجميع المستخدمين الموجودين');
    }
}
