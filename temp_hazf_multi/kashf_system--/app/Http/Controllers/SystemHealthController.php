<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemHealthController extends Controller
{
    public function index()
    {
        $dbStatus = $this->getDatabaseStatus();
        $latestManualBackup = $this->getLatestBackupFolder(storage_path('backups'));
        $latestAutoBackup = $this->getLatestBackupFolder(storage_path('auto_backups'));

        $logPath = storage_path('logs/laravel.log');
        $logExists = is_file($logPath);
        $logSize = $logExists ? filesize($logPath) : 0;
        $logModifiedAt = $logExists ? date('Y/m/d H:i:s', filemtime($logPath)) : null;

        $failedJobsCount = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : null;

        $health = [
            'db_ok' => $dbStatus['ok'],
            'db_message' => $dbStatus['message'],
            'last_manual_backup' => $latestManualBackup,
            'last_auto_backup' => $latestAutoBackup,
            'log_exists' => $logExists,
            'log_size' => $logSize,
            'log_modified_at' => $logModifiedAt,
            'failed_jobs_count' => $failedJobsCount,
            'total_payrolls' => Payroll::count(),
            'total_archived' => Payroll::where('is_archived', true)->count(),
            'audit_events_today' => PayrollAuditLog::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('system.health', compact('health'));
    }

    public function runAuditBackfill(): RedirectResponse
    {
        try {
            Artisan::call('payroll:audit-backfill-descriptions');
            $output = trim(Artisan::output());

            return redirect()
                ->route('system.health')
                ->with('success', $output !== '' ? $output : 'تم تشغيل تحديث أوصاف سجل التدقيق بنجاح.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('system.health')
                ->with('error', 'فشل تشغيل تحديث أوصاف سجل التدقيق: ' . $exception->getMessage());
        }
    }

    private function getDatabaseStatus(): array
    {
        try {
            DB::select('SELECT 1');
            return ['ok' => true, 'message' => 'الاتصال بقاعدة البيانات يعمل بشكل طبيعي'];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    private function getLatestBackupFolder(string $rootPath): ?array
    {
        if (!is_dir($rootPath)) {
            return null;
        }

        $folders = glob($rootPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        if (empty($folders)) {
            return null;
        }

        usort($folders, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $latest = $folders[0] ?? null;
        if (!$latest) {
            return null;
        }

        return [
            'name' => basename($latest),
            'modified_at' => date('Y/m/d H:i:s', filemtime($latest)),
            'path' => $latest,
        ];
    }
}
