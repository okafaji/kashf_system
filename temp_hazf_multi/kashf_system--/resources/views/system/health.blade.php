<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right">صحة النظام</h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4 space-y-3">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm whitespace-pre-line">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 {{ $health['db_ok'] ? 'border-green-500' : 'border-red-500' }}">
                <p class="text-xs text-gray-500 mb-1">قاعدة البيانات</p>
                <p class="text-lg font-bold {{ $health['db_ok'] ? 'text-green-700' : 'text-red-700' }}">{{ $health['db_ok'] ? 'متصلة' : 'مشكلة اتصال' }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ $health['db_message'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-blue-500">
                <p class="text-xs text-gray-500 mb-1">آخر نسخة احتياطية (يدوية)</p>
                <p class="text-lg font-bold text-blue-700">{{ $health['last_manual_backup']['name'] ?? 'لا توجد' }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ $health['last_manual_backup']['modified_at'] ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-indigo-500">
                <p class="text-xs text-gray-500 mb-1">آخر نسخة احتياطية (تلقائية)</p>
                <p class="text-lg font-bold text-indigo-700">{{ $health['last_auto_backup']['name'] ?? 'لا توجد' }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ $health['last_auto_backup']['modified_at'] ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-amber-500">
                <p class="text-xs text-gray-500 mb-1">ملف السجل</p>
                <p class="text-lg font-bold text-amber-700">{{ $health['log_exists'] ? 'موجود' : 'غير موجود' }}</p>
                <p class="text-sm text-gray-600 mt-2">الحجم: {{ number_format($health['log_size'] / 1024, 1) }} KB</p>
                <p class="text-xs text-gray-500 mt-1">آخر تحديث: {{ $health['log_modified_at'] ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-red-500">
                <p class="text-xs text-gray-500 mb-1">الوظائف الفاشلة</p>
                <p class="text-lg font-bold text-red-700">{{ $health['failed_jobs_count'] ?? 'غير متاح' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-emerald-500">
                <p class="text-xs text-gray-500 mb-1">إحصائيات سريعة</p>
                <p class="text-sm text-gray-700">إجمالي السجلات: <strong>{{ number_format($health['total_payrolls']) }}</strong></p>
                <p class="text-sm text-gray-700">المؤرشف: <strong>{{ number_format($health['total_archived']) }}</strong></p>
                <p class="text-sm text-gray-700">أحداث تدقيق اليوم: <strong>{{ number_format($health['audit_events_today']) }}</strong></p>
            </div>

            @can('manage-settings')
                <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-fuchsia-500 md:col-span-2 xl:col-span-3">
                    <p class="text-xs text-gray-500 mb-1">أدوات الصيانة</p>
                    <p class="text-sm text-gray-700 mb-3">تحديث أوصاف سجل التدقيق القديمة لتظهر بصيغة تفصيلية وواضحة.</p>
                    <p class="text-xs text-gray-600 mb-3">الفائدة: هذا الإيعاز لا يغير مبالغ أو بيانات الكشوفات، فقط يعيد كتابة وصف أحداث التدقيق القديمة لتظهر بشكل مفهوم مثل: من الذي تغير، وما الحقول التي تغيرت.</p>

                    <form method="POST" action="{{ route('system.health.audit_backfill') }}" onsubmit="return confirm('تشغيل تحديث أوصاف سجل التدقيق الآن؟')">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md transition-colors"
                            style="background-color:#1d4ed8;color:#ffffff;border:1px solid #1d4ed8;"
                            onmouseover="this.style.backgroundColor='#1e40af'"
                            onmouseout="this.style.backgroundColor='#1d4ed8'"
                        >
                            تشغيل تحديث أوصاف سجل التدقيق
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-5 border-r-4 border-gray-300 md:col-span-2 xl:col-span-3">
                    <p class="text-xs text-gray-500 mb-1">أدوات الصيانة</p>
                    <p class="text-sm text-gray-600">زر تحديث أوصاف سجل التدقيق يحتاج صلاحية manage-settings.</p>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
