<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage-backups');
    }

    /**
     * صفحة إدارة النسخ الاحتياطية
     */
    public function index()
    {
        return view('backups.index');
    }

    /**
     * إنشاء نسخة احتياطية كاملة (قاعدة بيانات + كود)
     */
    public function createBackup(Request $request)
    {
        try {
            // إنشاء مجلد النسخ الاحتياطية
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // تاريخ ووقت النسخة
            $timestamp = now()->format('Y_m_d_H_i_s');
            $backupFolder = "{$backupDir}/backup_{$timestamp}";

            if (!mkdir($backupFolder, 0755, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء مجلد النسخة'
                ], 500);
            }

            // نسخ قاعدة البيانات
            $dbBackupStatus = $this->backupDatabase($backupFolder, $timestamp);
            if (!$dbBackupStatus['success']) {
                return response()->json($dbBackupStatus, 500);
            }

            // نسخ الكود
            $codeBackupStatus = $this->backupCode($backupFolder, $timestamp);
            if (!$codeBackupStatus['success']) {
                return response()->json($codeBackupStatus, 500);
            }

            // تسجيل النسخة
            Log::info('Backup created successfully', [
                'timestamp' => $timestamp,
                'database' => $dbBackupStatus['file'],
                'code' => $codeBackupStatus['file']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                'timestamp' => $timestamp,
                'backup_folder' => $backupFolder
            ]);

        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء نسخة احتياطية من قاعدة البيانات فقط
     */
    public function backupDatabaseOnly(Request $request)
    {
        try {
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y_m_d_H_i_s');
            $backupFolder = "{$backupDir}/backup_{$timestamp}";

            if (!mkdir($backupFolder, 0755, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء مجلد النسخة'
                ], 500);
            }

            $dbBackupStatus = $this->backupDatabase($backupFolder, $timestamp);
            if (!$dbBackupStatus['success']) {
                return response()->json($dbBackupStatus, 500);
            }

            Log::info('Database backup created successfully', [
                'timestamp' => $timestamp,
                'database' => $dbBackupStatus['file']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء نسخة قاعدة البيانات بنجاح',
                'timestamp' => $timestamp,
                'backup_folder' => $backupFolder
            ]);

        } catch (\Exception $e) {
            Log::error('Database backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء نسخة قاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء نسخة احتياطية من الأكواد فقط
     */
    public function backupCodeOnly(Request $request)
    {
        try {
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y_m_d_H_i_s');
            $backupFolder = "{$backupDir}/backup_{$timestamp}";

            if (!mkdir($backupFolder, 0755, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء مجلد النسخة'
                ], 500);
            }

            $codeBackupStatus = $this->backupCode($backupFolder, $timestamp);
            if (!$codeBackupStatus['success']) {
                return response()->json($codeBackupStatus, 500);
            }

            Log::info('Code backup created successfully', [
                'timestamp' => $timestamp,
                'code' => $codeBackupStatus['file']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء نسخة الأكواد بنجاح',
                'timestamp' => $timestamp,
                'backup_folder' => $backupFolder
            ]);

        } catch (\Exception $e) {
            Log::error('Code backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء نسخة الأكواد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * نسخ احتياطي لقاعدة البيانات فقط
     */
    public function backupDatabase($backupFolder, $timestamp)
    {
        try {
            $backupFile = "{$backupFolder}/database_{$timestamp}.sql";

            // استخدام الاتصال المباشر بقاعدة البيانات عبر PHP
            $connection = DB::connection()->getPdo();

            // الحصول على اسم قاعدة البيانات من الكونفيج
            $dbName = env('DB_DATABASE');

            // بدء محتوى ملف SQL
            $sql = "-- Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$dbName}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            // الحصول على قائمة جميع الجداول
            $tables = $connection->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$dbName}'")->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($tables)) {
                throw new \Exception('لا توجد جداول في قاعدة البيانات');
            }

            // تصدير كل جدول
            foreach ($tables as $table) {
                try {
                    // الحصول على تعريف الجدول (CREATE TABLE)
                    $createTableResult = $connection->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                    if ($createTableResult) {
                        $sql .= "\n\n-- Table: `{$table}`\n";
                        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                        $sql .= $createTableResult['Create Table'] . ";\n\n";
                    }

                    // الحصول على البيانات من الجدول
                    $rows = $connection->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

                    if (!empty($rows)) {
                        $sql .= "\n-- Data for table: `{$table}`\n";
                        foreach ($rows as $row) {
                            // بناء INSERT statement
                            $columns = implode('`, `', array_keys($row));
                            $values = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = "'" . addslashes($value) . "'";
                                }
                            }
                            $sql .= "INSERT INTO `{$table}` (`{$columns}`) VALUES (" . implode(', ', $values) . ");\n";
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to backup table {$table}: " . $e->getMessage());
                    continue;
                }
            }

            $sql .= "\n\nSET FOREIGN_KEY_CHECKS=1;\n";

            // حفظ الملف
            $bytesWritten = file_put_contents($backupFile, $sql);

            if ($bytesWritten === false || filesize($backupFile) === 0) {
                throw new \Exception('فشل حفظ ملف قاعدة البيانات');
            }

            return [
                'success' => true,
                'file' => $backupFile,
                'size' => filesize($backupFile)
            ];

        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'فشل نسخ قاعدة البيانات: ' . $e->getMessage()
            ];
        }
    }

    /**
     * نسخ احتياطي للكود
     */
    public function backupCode($backupFolder, $timestamp)
    {
        try {
            $projectDir = base_path();
            $backupFile = "{$backupFolder}/code_{$timestamp}.zip";

            // نسخ "الكود" فقط: استبعاد المجلدات الديناميكية/الكبيرة لتفادي مشاكل القفل في ويندوز
            $excludeDirs = [
                'node_modules',   // مكتبات Node.js (يتم تثبيتها عبر npm)
                'vendor',         // مكتبات PHP (يتم تثبيتها عبر composer)
                'storage',        // ملفات ديناميكية (logs/cache/sessions/backups...)
                'bootstrap/cache',
                '.git'
            ];

            $this->zipDirectory($projectDir, $backupFile, $excludeDirs);

            if (file_exists($backupFile) && filesize($backupFile) > 0) {
                return [
                    'success' => true,
                    'file' => $backupFile,
                    'size' => filesize($backupFile)
                ];
            }

            throw new \Exception('فشل ضغط الكود');

        } catch (\Exception $e) {
            Log::error('Code backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'فشل نسخ الكود: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ضغط المجلد بشكل ديناميكي
     */
    private function zipDirectory($source, $destination, array $excludeDirs = [])
    {
        $sourceRealPath = realpath($source);
        if ($sourceRealPath === false || !is_dir($sourceRealPath)) {
            throw new \Exception('مجلد المصدر غير صالح للضغط');
        }

        $destinationDir = dirname($destination);
        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
            throw new \Exception('تعذر إنشاء مجلد ملف ZIP');
        }

        $normalizedSource = rtrim(str_replace('\\', '/', $sourceRealPath), '/');
        $excludedPrefixes = array_map(function ($dir) {
            return strtolower(trim(str_replace('\\', '/', $dir), '/')) . '/';
        }, $excludeDirs);

        $zip = new \ZipArchive();
        $openResult = $zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            throw new \Exception('فشل إنشاء ملف ZIP (رمز: ' . $openResult . ')');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceRealPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $filesAdded = 0;
        $filesSkipped = 0;

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getPathname();
            $realPath = realpath($filePath);

            if ($realPath === false || !is_readable($realPath)) {
                $filesSkipped++;
                continue;
            }

            $normalizedFilePath = str_replace('\\', '/', $realPath);
            $sourcePrefix = $normalizedSource . '/';

            // تجاهل أي ملف خارج مجلد المشروع (قد يحصل مع بعض الروابط الرمزية)
            if (!str_starts_with(strtolower($normalizedFilePath), strtolower($sourcePrefix))) {
                $filesSkipped++;
                continue;
            }

            $relativePath = substr($normalizedFilePath, strlen($sourcePrefix));
            if ($relativePath === '') {
                $filesSkipped++;
                continue;
            }

            $relativePathLower = strtolower($relativePath);
            $shouldExclude = false;
            foreach ($excludedPrefixes as $excludedPrefix) {
                if (str_starts_with($relativePathLower, $excludedPrefix)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                $filesSkipped++;
                continue;
            }

            if (!$zip->addFile($realPath, $relativePath)) {
                $filesSkipped++;
                Log::warning('Skipping file during code backup because ZipArchive::addFile failed', [
                    'file' => $realPath,
                ]);
                continue;
            }

            $filesAdded++;
        }

        if (!$zip->close()) {
            throw new \Exception('فشل إغلاق ملف ZIP بعد إضافة الملفات');
        }

        clearstatcache(true, $destination);
        if (!file_exists($destination) || filesize($destination) === 0 || $filesAdded === 0) {
            throw new \Exception('ملف ZIP الناتج غير صالح أو فارغ');
        }

        Log::info('Code backup zip completed', [
            'destination' => $destination,
            'files_added' => $filesAdded,
            'files_skipped' => $filesSkipped,
        ]);
    }

    /**
     * قائمة النسخ الاحتياطية
     */
    public function listBackups()
    {
        try {
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                return response()->json([
                    'success' => true,
                    'backups' => []
                ]);
            }

            $backups = [];
            $folders = array_filter(scandir($backupDir), function($folder) {
                return strpos($folder, 'backup_') === 0 && is_dir(storage_path("backups/{$folder}"));
            });

            rsort($folders); // ترتيب تنازلي (الأحدث أولاً)

            foreach ($folders as $folder) {
                $folderPath = "{$backupDir}/{$folder}";
                $files = array_filter(scandir($folderPath), function($file) {
                    return $file !== '.' && $file !== '..';
                });

                $hasDatabaseBackup = false;
                $hasCodeBackup = false;
                foreach ($files as $file) {
                    if (str_starts_with($file, 'database_') && str_ends_with($file, '.sql')) {
                        $hasDatabaseBackup = true;
                    }
                    if (str_starts_with($file, 'code_') && str_ends_with($file, '.zip')) {
                        $hasCodeBackup = true;
                    }
                }

                $backupTypeLabel = 'غير محدد';
                if ($hasDatabaseBackup && $hasCodeBackup) {
                    $backupTypeLabel = 'كاملة';
                } elseif ($hasDatabaseBackup) {
                    $backupTypeLabel = 'قاعدة بيانات';
                } elseif ($hasCodeBackup) {
                    $backupTypeLabel = 'أكواد';
                }

                $size = 0;
                foreach ($files as $file) {
                    $size += filesize("{$folderPath}/{$file}");
                }

                $timestamp = str_replace('backup_', '', $folder);
                $date = \Carbon\Carbon::createFromFormat('Y_m_d_H_i_s', $timestamp);

                $backups[] = [
                    'folder' => $folder,
                    'timestamp' => $timestamp,
                    'date' => $date->format('Y/m/d H:i:s'),
                    'size' => $this->formatBytes($size),
                    'files' => count($files),
                    'type_label' => $backupTypeLabel
                ];
            }

            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            Log::error('List backups failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب قائمة النسخ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحميل نسخة احتياطية
     */
    public function downloadBackup($timestamp)
    {
        try {
            $backupDir = storage_path('backups');
            $backupFolder = "{$backupDir}/backup_{$timestamp}";

            if (!is_dir($backupFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // إنشاء ملف ZIP يحتوي على جميع ملفات النسخة
            $zipPath = "{$backupDir}/download_{$timestamp}.zip";
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($backupFolder),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($backupFolder) + 1);
                $zip->addFile($filePath, $relativePath);
            }

            $zip->close();

            // تحميل الملف
            return response()->download($zipPath, "kashf_backup_{$timestamp}.zip", [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Download backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل النسخة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف نسخة احتياطية
     */
    public function deleteBackup($timestamp)
    {
        try {
            $backupDir = storage_path('backups');
            $backupFolder = "{$backupDir}/backup_{$timestamp}";

            if (!is_dir($backupFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // حذف المجلد بشكل آمن
            $this->deleteDirectory($backupFolder);

            Log::info('Backup deleted', ['timestamp' => $timestamp]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف النسخة الاحتياطية بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف النسخة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * فتح مجلد النسخة الاحتياطية في File Explorer
     */
    public function openBackupFolder($timestamp)
    {
        try {
            // التحقق من صحة timestamp (يجب أن يكون بصيغة محددة فقط)
            if (!preg_match('/^\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}$/', $timestamp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة التاريخ غير صحيحة'
                ], 400);
            }

            $backupDir = storage_path('backups');
            $backupFolder = "{$backupDir}" . DIRECTORY_SEPARATOR . "backup_{$timestamp}";

            // التحقق من وجود المجلد
            if (!is_dir($backupFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // التأكد من أن المسار داخل مجلد backups (منع Path Traversal)
            $realBackupFolder = realpath($backupFolder);
            $realBackupDir = realpath($backupDir);

            if (!$realBackupFolder || strpos($realBackupFolder, $realBackupDir) !== 0) {
                Log::warning('Attempted path traversal attack', [
                    'timestamp' => $timestamp,
                    'attempted_path' => $backupFolder
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'مسار غير صالح'
                ], 403);
            }

            // فتح المجلد بشكل آمن
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows - استخدام علامات اقتباس مزدوجة للمسارات التي تحتوي على مسافات
                $safePath = '"' . str_replace('"', '', $realBackupFolder) . '"';
                exec("explorer {$safePath}");
            } else {
                // Linux/Mac
                $safePath = escapeshellarg($realBackupFolder);
                $opener = PHP_OS === 'Darwin' ? 'open' : 'xdg-open';
                exec("{$opener} {$safePath} > /dev/null 2>&1 &");
            }

            Log::info('Backup folder opened', [
                'timestamp' => $timestamp,
                'path' => $realBackupFolder,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم فتح المجلد بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Open backup folder failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل فتح المجلد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف مجلد بشكل آمن
     */
    private function deleteDirectory($path)
    {
        if (!is_dir($path)) {
            return unlink($path);
        }

        foreach (scandir($path) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $this->deleteDirectory("{$path}/{$item}");
        }

        return rmdir($path);
    }

    /**
     * نسخ احتياطي تلقائي لقاعدة البيانات
     * يتم استدعاؤه عند إنشاء أول كشف في اليوم
     */
    public static function createAutomaticBackup()
    {
        try {
            // التحقق من وجود نسخة احتياطية لليوم
            $today = now()->format('Y-m-d');
            $cacheKey = "auto_backup_{$today}";

            if (Cache::has($cacheKey)) {
                // تم إنشاء نسخة احتياطية لهذا اليوم بالفعل
                return;
            }

            // إنشاء مجلد النسخ التلقائية
            $autoBackupDir = storage_path('auto_backups');
            if (!is_dir($autoBackupDir)) {
                mkdir($autoBackupDir, 0755, true);
            }

            $timestamp = now()->format('Y_m_d_H_i_s');
            $backupFolder = "{$autoBackupDir}/auto_backup_{$timestamp}";

            if (!mkdir($backupFolder, 0755, true)) {
                Log::error('Failed to create auto backup folder');
                return;
            }

            // إنشاء نسخة قاعدة البيانات
            $controller = new self();
            $dbBackupStatus = $controller->backupDatabase($backupFolder, $timestamp);

            if ($dbBackupStatus['success']) {
                // حفظ في الـ cache لمدة يوم واحد
                Cache::put($cacheKey, true, now()->endOfDay());

                Log::info('Automatic backup created successfully', [
                    'timestamp' => $timestamp,
                    'size' => $dbBackupStatus['size']
                ]);

                // حذف النسخ القديمة (أكثر من 15 يوم)
                self::cleanOldAutoBackups();
            }

        } catch (\Exception $e) {
            Log::error('Automatic backup failed: ' . $e->getMessage());
        }
    }

    /**
     * حذف النسخ التلقائية الأقدم من 15 يوم
     */
    private static function cleanOldAutoBackups()
    {
        try {
            $autoBackupDir = storage_path('auto_backups');

            if (!is_dir($autoBackupDir)) {
                return;
            }

            $folders = array_filter(scandir($autoBackupDir), function ($item) use ($autoBackupDir) {
                return strpos($item, 'auto_backup_') === 0 && is_dir("{$autoBackupDir}/{$item}");
            });

            $cutoffDate = now()->subDays(15);

            foreach ($folders as $folder) {
                // استخراج التاريخ من اسم المجلد: auto_backup_2026_02_24_15_30_45
                if (preg_match('/auto_backup_(\d{4})_(\d{2})_(\d{2})_(\d{2})_(\d{2})_(\d{2})/', $folder, $matches)) {
                    $folderDate = \Carbon\Carbon::create(
                        $matches[1], // year
                        $matches[2], // month
                        $matches[3], // day
                        $matches[4], // hour
                        $matches[5], // minute
                        $matches[6]  // second
                    );

                    if ($folderDate->lt($cutoffDate)) {
                        $folderPath = "{$autoBackupDir}/{$folder}";
                        $controller = new self();
                        $controller->deleteDirectory($folderPath);

                        Log::info('Old auto backup deleted', [
                            'folder' => $folder,
                            'age_days' => $folderDate->diffInDays(now())
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Clean old auto backups failed: ' . $e->getMessage());
        }
    }

    /**
     * تنسيق حجم الملف
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
