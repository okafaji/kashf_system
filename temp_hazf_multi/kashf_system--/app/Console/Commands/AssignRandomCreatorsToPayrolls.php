<?php

namespace App\Console\Commands;

use App\Models\Payroll;
use App\Models\User;
use App\Models\Department;
use Illuminate\Console\Command;

class AssignRandomCreatorsToPayrolls extends Command
{
    protected $signature = 'payroll:assign-random-creators {--force-all : إعادة التوزيع حتى للكشوف التي فيها user_id}';

    protected $description = 'إسناد منشئ الكشف (user_id + created_by_department_id) بشكل تلقائي/عشوائي للكشوف القديمة';

    public function handle(): int
    {
        $forceAll = (bool) $this->option('force-all');

        $users = User::query()
            ->select(['id', 'department_id'])
            ->get();

        if ($users->isEmpty()) {
            $this->error('❌ لا يوجد مستخدمون في النظام لإسناد منشئ الكشف.');
            return self::FAILURE;
        }

        $departmentIds = Department::query()->pluck('id')->values();

        if ($departmentIds->isEmpty()) {
            $this->error('❌ لا يوجد أقسام في جدول departments لإسناد created_by_department_id.');
            return self::FAILURE;
        }

        $userPool = $users->values();

        $query = Payroll::query();

        if (!$forceAll) {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereNull('created_by_department_id');
            });
        }

        $targetCount = (clone $query)->count();

        if ($targetCount === 0) {
            $this->info('✅ لا توجد كشوف تحتاج تحديث.');
            return self::SUCCESS;
        }

        $this->info("🔄 سيتم تحديث {$targetCount} كشف...");

        $updated = 0;

        $query->orderBy('id')->chunkById(200, function ($payrolls) use (&$updated, $userPool, $departmentIds, $forceAll) {
            foreach ($payrolls as $payroll) {
                $randomUser = $userPool->random();

                $newUserId = $forceAll || !$payroll->user_id
                    ? $randomUser->id
                    : $payroll->user_id;

                $resolvedDepartmentId = $randomUser->department_id ?: $departmentIds->random();

                $newDepartmentId = $forceAll || !$payroll->created_by_department_id
                    ? $resolvedDepartmentId
                    : $payroll->created_by_department_id;

                if ((int) $payroll->user_id !== (int) $newUserId || (int) $payroll->created_by_department_id !== (int) $newDepartmentId) {
                    $payroll->user_id = $newUserId;
                    $payroll->created_by_department_id = $newDepartmentId;
                    $payroll->save();
                    $updated++;
                }
            }
        });

        $remainingMissingUser = Payroll::whereNull('user_id')->count();
        $remainingMissingDepartment = Payroll::whereNull('created_by_department_id')->count();

        $this->info("✅ تم تحديث {$updated} كشف.");
        $this->line("📌 المتبقي بدون user_id: {$remainingMissingUser}");
        $this->line("📌 المتبقي بدون created_by_department_id: {$remainingMissingDepartment}");

        return self::SUCCESS;
    }
}
