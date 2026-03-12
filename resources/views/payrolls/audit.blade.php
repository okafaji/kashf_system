<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right">سجل تدقيق الكشوفات</h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <form method="GET" action="{{ route('payrolls.audit') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <input type="text" name="kashf_no" value="{{ $filters['kashf_no'] }}" placeholder="رقم الكشف" class="border-gray-300 rounded-md">

                    <select name="action" class="border-gray-300 rounded-md">
                        <option value="">كل العمليات</option>
                        @foreach($actionOptions as $action)
                            <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $actionLabels[$action] ?? $action }}</option>
                        @endforeach
                    </select>

                    <select name="user_id" class="border-gray-300 rounded-md">
                        <option value="">كل المستخدمين</option>
                        @foreach($userOptions as $user)
                            <option value="{{ $user->id }}" @selected((string)$filters['user_id'] === (string)$user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="from_date" value="{{ $filters['from_date'] }}" placeholder="yyyy/mm/dd" class="border-gray-300 rounded-md">
                    <input type="text" name="to_date" value="{{ $filters['to_date'] }}" placeholder="yyyy/mm/dd" class="border-gray-300 rounded-md">

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">تصفية</button>
                        <a href="{{ route('payrolls.audit') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md">إعادة</a>
                    </div>
                </form>
            </div>

            <script>
            (function () {
                var form = document.querySelector('form[action*="audit"]');
                if (!form) return;

                // رقم الكشف: بحث عند الضغط على Enter فقط
                var kashfInput = form.querySelector('input[name="kashf_no"]');
                if (kashfInput) {
                    kashfInput.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            form.submit();
                        }
                    });
                }

                // العملية والمستخدم: بحث فوري عند التغيير
                ['action', 'user_id'].forEach(function (name) {
                    var sel = form.querySelector('select[name="' + name + '"]');
                    if (sel) {
                        sel.addEventListener('change', function () { form.submit(); });
                    }
                });
            })();
            </script>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-3 text-right">الوقت</th>
                            <th class="p-3 text-right">العملية</th>
                            <th class="p-3 text-right">الكشف</th>
                            <th class="p-3 text-right">المستخدم</th>
                            <th class="p-3 text-right">الوصف</th>
                            <th class="p-3 text-right">تفاصيل التغيير</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($logs as $log)
                            @php
                                $fieldLabels = [
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

                                $formatValue = function ($key, $value) use ($cityMap, $missionTypeMap, $departmentMap, $statusLabels) {
                                    if ($value === null || $value === '') {
                                        return '-';
                                    }

                                    if (in_array($key, ['is_archived'], true)) {
                                        return (int)$value === 1 ? 'نعم' : 'لا';
                                    }

                                    if ($key === 'is_half_allowance') {
                                        return (int)$value === 1 ? '50%' : '100%';
                                    }

                                    if ($key === 'status') {
                                        return $statusLabels[(string)$value] ?? (string)$value;
                                    }

                                    if ($key === 'city_id') {
                                        return $cityMap[(int)$value] ?? (string)$value;
                                    }

                                    if ($key === 'mission_type_id') {
                                        return $missionTypeMap[(int)$value] ?? (string)$value;
                                    }

                                    if ($key === 'created_by_department_id') {
                                        return $departmentMap[(int)$value] ?? (string)$value;
                                    }

                                    if (in_array($key, ['admin_order_date', 'start_date', 'end_date'], true)) {
                                        try {
                                            return \Carbon\Carbon::parse((string)$value)->format('Y/m/d');
                                        } catch (\Throwable $exception) {
                                            return (string)$value;
                                        }
                                    }

                                    if (in_array($key, ['daily_allowance', 'accommodation_fee', 'total_amount', 'transportation_fee', 'receipts_amount'], true) && is_numeric($value)) {
                                        return number_format((float)$value);
                                    }

                                    return (string)$value;
                                };

                                $changes = [];
                                $oldValues = is_array($log->old_values) ? $log->old_values : [];
                                $newValues = is_array($log->new_values) ? $log->new_values : [];

                                $keys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                                foreach ($keys as $key) {
                                    $oldValue = $oldValues[$key] ?? null;
                                    $newValue = $newValues[$key] ?? null;
                                    if ((string)$oldValue === (string)$newValue) {
                                        continue;
                                    }

                                    $changes[] = [
                                        'label' => $fieldLabels[$key] ?? $key,
                                        'old' => $formatValue($key, $oldValue),
                                        'new' => $formatValue($key, $newValue),
                                    ];
                                }
                            @endphp
                            <tr>
                                <td class="p-3 text-gray-600">{{ $log->created_at?->format('Y/m/d H:i') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700">{{ $actionLabels[$log->action] ?? $log->action }}</span>
                                </td>
                                <td class="p-3">{{ $log->kashf_no ?? '-' }}</td>
                                <td class="p-3">{{ $log->user_name ?? '-' }}</td>
                                <td class="p-3 text-gray-700">{{ $log->description ?? '-' }}</td>
                                <td class="p-3 text-xs text-gray-700">
                                    @if(count($changes) > 0)
                                        <div class="space-y-1 max-w-[460px]">
                                            @foreach($changes as $change)
                                                <div class="rounded bg-gray-50 px-2 py-1 border border-gray-200">
                                                    <span class="font-semibold text-gray-900">{{ $change['label'] }}:</span>
                                                    <span class="text-red-600">{{ $change['old'] }}</span>
                                                    <span class="mx-1 text-gray-400">←</span>
                                                    <span class="text-green-700">{{ $change['new'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">لا توجد تفاصيل إضافية</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">لا توجد أحداث تدقيق مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div dir="ltr">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
