<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Department;
use App\Models\MissionType;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillPayrollAuditDescriptions extends Command
{
    protected $signature = 'payroll:audit-backfill-descriptions {--all : تحديث جميع السجلات حتى لو كان الوصف موجودا}';

    protected $description = 'تحديث أوصاف سجل تدقيق الكشوفات القديمة لتكون تفصيلية وواضحة';

    private array $statusLabels = [];
    private array $cityMap = [];
    private array $missionTypeMap = [];
    private array $departmentMap = [];

    public function handle(): int
    {
        $this->statusLabels = Payroll::statusLabels();
        $this->cityMap = City::query()->pluck('name', 'id')->toArray();
        $this->missionTypeMap = MissionType::query()->pluck('name', 'id')->toArray();
        $this->departmentMap = Department::query()->pluck('name', 'id')->toArray();

        $forceAll = (bool) $this->option('all');
        $updated = 0;
        $scanned = 0;

        $query = PayrollAuditLog::query()->orderBy('id');

        if (!$forceAll) {
            $query->where(function ($q) {
                $q->whereNull('description')
                    ->orWhere('description', '')
                    ->orWhere('description', 'تم التحديث')
                    ->orWhere('description', 'LIKE', 'تعديل كشف إيفاد (%')
                    ->orWhere('description', 'إنشاء كشف إيفاد جديد')
                    ->orWhere('description', 'إضافة منتسب إلى كشف موجود')
                    ->orWhere('description', 'حذف سجل إيفاد')
                    ->orWhere('description', 'إنشاء سجل ضمن الإدخال الجماعي')
                    ->orWhere('description', 'تأكيد الطباعة وترحيل الكشف إلى الأرشيف');
            });
        }

        $query->chunkById(500, function ($logs) use (&$updated, &$scanned, $forceAll) {
            foreach ($logs as $log) {
                $scanned++;

                if (!$forceAll && !$this->shouldReplaceDescription((string) $log->description)) {
                    continue;
                }

                $newDescription = $this->generateDescription($log);
                if ($newDescription === '') {
                    continue;
                }

                if (trim((string) $log->description) === trim($newDescription)) {
                    continue;
                }

                $log->forceFill([
                    'description' => $newDescription,
                ])->saveQuietly();

                $updated++;
            }
        });

        $this->newLine();
        $this->info('✅ تم فحص ' . number_format($scanned) . ' سجل.');
        $this->info('✅ تم تحديث ' . number_format($updated) . ' وصف.');

        return self::SUCCESS;
    }

    private function shouldReplaceDescription(string $description): bool
    {
        $value = trim($description);

        if ($value === '' || $value === 'تم التحديث') {
            return true;
        }

        if (str_starts_with($value, 'تعديل كشف إيفاد (')) {
            return true;
        }

        return in_array($value, [
            'إنشاء كشف إيفاد جديد',
            'إضافة منتسب إلى كشف موجود',
            'حذف سجل إيفاد',
            'إنشاء سجل ضمن الإدخال الجماعي',
            'تأكيد الطباعة وترحيل الكشف إلى الأرشيف',
        ], true);
    }

    private function generateDescription(PayrollAuditLog $log): string
    {
        $action = (string) $log->action;
        $oldValues = is_array($log->old_values) ? $log->old_values : [];
        $newValues = is_array($log->new_values) ? $log->new_values : [];

        if ($action === 'updated') {
            $changedLabels = $this->collectChangedFieldLabels($oldValues, $newValues);

            return empty($changedLabels)
                ? 'تم فتح التعديل بدون تغييرات فعلية'
                : 'تم تعديل: ' . implode('، ', $changedLabels);
        }

        if ($action === 'created') {
            return $this->buildDescription(
                'إنشاء سجل إيفاد',
                $newValues,
                ['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount']
            );
        }

        if ($action === 'deleted') {
            return $this->buildDescription(
                'حذف سجل إيفاد',
                $oldValues,
                ['name', 'kashf_no', 'admin_order_no', 'status', 'total_amount']
            );
        }

        if ($action === 'print_confirmed') {
            $summary = $this->buildDescription(
                'تأكيد الطباعة وترحيل الكشف إلى الأرشيف',
                array_merge($oldValues, $newValues),
                ['name', 'kashf_no']
            );

            $beforeStatus = $this->formatValue('status', $oldValues['status'] ?? null);
            $afterStatus = $this->formatValue('status', $newValues['status'] ?? Payroll::STATUS_ARCHIVED);

            if ($beforeStatus !== '-' && $afterStatus !== '-') {
                $summary .= '، الحالة: ' . $beforeStatus . ' ← ' . $afterStatus;
            }

            return $summary;
        }

        $changedLabels = $this->collectChangedFieldLabels($oldValues, $newValues);
        if (!empty($changedLabels)) {
            return 'تم تعديل: ' . implode('، ', $changedLabels);
        }

        return trim((string) $log->description);
    }

    private function buildDescription(string $prefix, array $values, array $orderedKeys): string
    {
        $labels = $this->fieldLabels();
        $parts = [];

        foreach ($orderedKeys as $key) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            $formatted = $this->formatValue($key, $values[$key]);
            if ($formatted === '-') {
                continue;
            }

            $parts[] = ($labels[$key] ?? $key) . ': ' . $formatted;
        }

        if (empty($parts)) {
            return $prefix;
        }

        return $prefix . ' - ' . implode('، ', $parts);
    }

    private function collectChangedFieldLabels(array $oldValues, array $newValues): array
    {
        $labels = $this->fieldLabels();
        $keys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        $changed = [];

        foreach ($keys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;
            if ((string) $old === (string) $new) {
                continue;
            }

            $changed[] = $labels[$key] ?? $key;
        }

        return $changed;
    }

    private function formatValue(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if ($key === 'status') {
            return $this->statusLabels[(string) $value] ?? (string) $value;
        }

        if ($key === 'city_id') {
            return $this->cityMap[(int) $value] ?? (string) $value;
        }

        if ($key === 'mission_type_id') {
            return $this->missionTypeMap[(int) $value] ?? (string) $value;
        }

        if ($key === 'created_by_department_id') {
            return $this->departmentMap[(int) $value] ?? (string) $value;
        }

        if ($key === 'is_archived') {
            return (int) $value === 1 ? 'نعم' : 'لا';
        }

        if ($key === 'is_half_allowance') {
            return (int) $value === 1 ? '50%' : '100%';
        }

        if (in_array($key, ['admin_order_date', 'start_date', 'end_date'], true)) {
            try {
                return Carbon::parse((string) $value)->format('Y/m/d');
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        }

        if (in_array($key, ['daily_allowance', 'accommodation_fee', 'transportation_fee', 'receipts_amount', 'total_amount'], true) && is_numeric($value)) {
            return number_format((float) $value);
        }

        return (string) $value;
    }

    private function fieldLabels(): array
    {
        return [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'employee_id' => 'الرقم الوظيفي',
            'department' => 'القسم',
            'destination' => 'الوجهة',
            'governorate_id' => 'المحافظة',
            'job_title' => 'العنوان الوظيفي',
            'admin_order_no' => 'رقم الأمر الإداري',
            'receipt_no' => 'رقم الوصل',
            'admin_order_date' => 'تاريخ الأمر الإداري',
            'start_date' => 'تاريخ بداية الإيفاد',
            'end_date' => 'تاريخ نهاية الإيفاد',
            'days_count' => 'عدد الأيام',
            'daily_allowance' => 'مبلغ اليومية',
            'accommodation_fee' => 'مبلغ المبيت',
            'is_half_allowance' => 'نسبة الإيفاد',
            'mission_type_id' => 'نوع الإيفاد',
            'city_id' => 'المدينة',
            'transportation_fee' => 'أجور النقل',
            'meals_count' => 'عدد الوجبات',
            'receipts_amount' => 'مبالغ الوصولات',
            'total_amount' => 'المبلغ الكلي',
            'kashf_no' => 'رقم الكشف',
            'order_year' => 'سنة الأمر',
            'group_no' => 'رقم المجموعة',
            'status' => 'الحالة',
            'is_archived' => 'الأرشفة',
            'notes' => 'الملاحظات',
            'user_id' => 'المستخدم',
            'created_by_department_id' => 'قسم الإنشاء',
        ];
    }
}
