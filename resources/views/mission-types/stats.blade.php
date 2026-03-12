<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4" dir="rtl">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">📊 إحصائيات ايفاد خارج البلد</h2>
            <a href="{{ route('mission-types.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm font-semibold">
                ← العودة للقائمة
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <!-- إحصائيات سريعة -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-blue-600 text-sm font-semibold mb-2">إجمالي الأنواع</div>
                    <div class="text-3xl font-bold text-blue-800">{{ $stats->total() }}</div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-green-600 text-sm font-semibold mb-2">الأنواع المستخدمة</div>
                    <div class="text-3xl font-bold text-green-800">
                        {{ $stats->where('usage_count', '>', 0)->count() }}
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="text-yellow-600 text-sm font-semibold mb-2">الأنواع غير المستخدمة</div>
                    <div class="text-3xl font-bold text-yellow-800">
                        {{ $stats->where('usage_count', '=', 0)->count() }}
                    </div>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="text-purple-600 text-sm font-semibold mb-2">إجمالي الاستخدام</div>
                    <div class="text-2xl font-bold text-purple-800">
                        {{ $stats->sum('usage_count') }} إيفادة
                    </div>
                </div>
            </div>

            <!-- جدول الإحصائيات -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full border-collapse" dir="rtl">
                    <thead class="bg-gray-800 text-white text-sm">
                        <tr>
                            <th class="border px-4 py-3 text-center w-12">#</th>
                            <th class="border px-4 py-3 text-center">نوع الإيفاد</th>
                            <th class="border px-4 py-3 text-center">مستوى المسؤولية</th>
                            <th class="border px-4 py-3 text-center">عدد الاستخدام</th>
                            <th class="border px-4 py-3 text-center">إجمالي المبالغ</th>
                            <th class="border px-4 py-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($stats as $stat)
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="border px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="border px-4 py-3 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $stat->name }}
                                    </span>
                                </td>
                                <td class="border px-4 py-3 text-center">{{ $stat->responsibility_level }}</td>
                                <td class="border px-4 py-3 text-center">
                                    <span class="font-bold text-blue-600">{{ $stat->usage_count }}</span>
                                </td>
                                <td class="border px-4 py-3 text-center">
                                    <span class="font-bold text-green-700">
                                        {{ number_format($stat->total_amount, 0) }}
                                    </span>
                                </td>
                                <td class="border px-4 py-3 text-center">
                                    @if($stat->usage_count > 0)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                            مستخدم
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">
                                            غير مستخدم
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border px-4 py-3 text-center text-gray-500">
                                    لا توجد بيانات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            @if($stats->hasPages())
                <div class="mt-4">
                    {{ $stats->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
