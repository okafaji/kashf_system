<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;

class AssignRandomDepartmentsToUsers extends Command
{
    protected $signature = 'users:assign-random-departments {--force-all : إعادة التوزيع حتى للمستخدمين الذين لديهم department_id}';

    protected $description = 'إسناد department_id عشوائياً للمستخدمين (مفيد لبيانات تجريبية)';

    public function handle(): int
    {
        $forceAll = (bool) $this->option('force-all');

        $departmentIds = Department::query()->pluck('id')->values();

        if ($departmentIds->isEmpty()) {
            $this->error('❌ لا توجد أقسام في جدول departments.');
            return self::FAILURE;
        }

        $query = User::query();

        if (!$forceAll) {
            $query->whereNull('department_id');
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('✅ لا يوجد مستخدمون يحتاجون تحديث.');
            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($users as $user) {
            $user->department_id = $departmentIds->random();
            $user->save();
            $updated++;
        }

        $missing = User::whereNull('department_id')->count();

        $this->info("✅ تم تحديث {$updated} مستخدم.");
        $this->line("📌 المتبقي بدون department_id: {$missing}");

        return self::SUCCESS;
    }
}
