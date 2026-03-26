<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مستخدم مدير عام
        $admin = User::create([
            'name' => 'المدير العام',
            'email' => 'admin@kashf.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');

        $this->command->info('تم إنشاء مستخدم المدير العام');
        $this->command->info('البريد: admin@kashf.com');
        $this->command->info('كلمة المرور: password');
    }
}
