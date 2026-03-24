<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            try {
                $modelName = null;
                $oldData = null;
                $description = '';

                $route = $request->route();
                if ($route && $route->parameterNames && count($route->parameterNames) > 0) {
                    foreach ($route->parameters as $param) {
                        if (is_object($param) && method_exists($param, 'getTable')) {
                            $modelName = get_class($param);
                            $oldData = json_encode($param->getOriginal(), JSON_UNESCAPED_UNICODE);
                            break;
                        }
                    }
                }

                $newDataArr = $request->all();
                $oldDataArr = $oldData ? json_decode($oldData, true) : [];

                // توليد وصف مختصر مرتب
                if (empty($oldDataArr) && !empty($newDataArr)) {
                    $description = 'تمت إضافة سجل جديد: ' . $this->summarizeFields($newDataArr);
                } elseif (!empty($oldDataArr) && empty($newDataArr)) {
                    $description = 'تم حذف السجل: ' . $this->summarizeFields($oldDataArr);
                } elseif (!empty($oldDataArr) && !empty($newDataArr)) {
                    $changed = [];
                    foreach (array_unique(array_merge(array_keys($oldDataArr), array_keys($newDataArr))) as $k) {
                        if (($oldDataArr[$k] ?? null) != ($newDataArr[$k] ?? null)) {
                            $changed[] = $this->fieldLabel($k);
                        }
                    }
                    $description = $changed
                        ? ('تم تعديل: ' . implode('، ', $changed))
                        : 'تم فتح التعديل بدون تغييرات فعلية';
                }

                DB::table('audit_logs')->insert([
                    'user_id'    => $request->user()?->id,
                    'method'     => $request->method(),
                    'url'        => $request->fullUrl(),
                    'model'      => $modelName,
                    'old_data'   => $oldData,
                    'new_data'   => json_encode($newDataArr, JSON_UNESCAPED_UNICODE),
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description'=> $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('فشل تسجيل سجل التدقيق: ' . $e->getMessage(), [
                    'exception' => $e,
                    'request_data' => $request->all(),
                ]);
            }
        }
        return $response;
    }

    // دالة تلخيص الحقول المهمة
    private function summarizeFields(array $data): string
    {
        $fields = ['name', 'kashf_no', 'admin_order_no', 'start_date', 'end_date', 'status', 'total_amount'];
        $labels = $this->fieldLabels();
        $parts = [];
        foreach ($fields as $key) {
            if (!empty($data[$key])) {
                $parts[] = ($labels[$key] ?? $key) . ': ' . $data[$key];
            }
        }
        return implode('، ', $parts);
    }

    // دالة تسمية الحقول بالعربي
    private function fieldLabel($key): string
    {
        $labels = $this->fieldLabels();
        return $labels[$key] ?? $key;
    }

    private function fieldLabels(): array
    {
        return [
            'name' => 'الاسم',
            'kashf_no' => 'رقم الكشف',
            'admin_order_no' => 'رقم الأمر الإداري',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'status' => 'الحالة',
            'total_amount' => 'المبلغ الكلي',
            // أضف المزيد حسب الحاجة
        ];
    }
}
