<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center; direction: rtl;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                احصائيات المنتسب: {{ $name }}
            </h2>
            <a href="{{ route('payrolls.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 transition ease-in-out duration-150">
                رجوع لسجل الكشوفات
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6" dir="rtl">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">عدد الايفادات</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['count'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">المجموع الكلي</p>
                        <p class="text-2xl font-bold text-green-700">{{ number_format($stats['total']) }} <span class="text-sm">د.ع</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">اول تاريخ ايفاد</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ \Carbon\Carbon::parse($stats['first_start_date'])->format('Y/m/d') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">اخر تاريخ ايفاد</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ \Carbon\Carbon::parse($stats['last_end_date'])->format('Y/m/d') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse" dir="rtl">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr class="text-gray-600 text-sm font-bold">
                                <th class="p-4">رقم الكشف</th>
                                <th class="p-4">رقم الامر</th>
                                <th class="p-4">جهة الايفاد</th>
                                <th class="p-4">الفترة</th>
                                <th class="p-4">المبلغ</th>
                                <th class="p-4 text-center">الاجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($records as $row)
                                <tr class="hover:bg-blue-50/50 transition duration-150">
                                    <td class="p-4">
                                        <span class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-medium">
                                            {{ $row->kashf_no }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">
                                            {{ $row->admin_order_no }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600">
                                        {{ $row->destination }}
                                    </td>
                                    <td class="p-4 text-xs text-gray-600">
                                        {{ \Carbon\Carbon::parse($row->start_date)->format('Y/m/d') }}
                                        -
                                        {{ \Carbon\Carbon::parse($row->end_date)->format('Y/m/d') }}
                                    </td>
                                    <td class="p-4">
                                        <span class="text-lg font-bold text-green-700">{{ number_format($row->total_amount) }}</span>
                                        <span class="text-[10px] text-gray-400 mr-1">د.ع</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex gap-1 justify-center">
                                            <a href="{{ route('payrolls.show', ['kashf_no' => $row->kashf_no]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-teal-500 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-200 transition ease-in-out duration-150">
                                                عرض الكشف
                                            </a>
                                            <a href="{{ route('payrolls.edit', $row->id) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 border border-transparent rounded-md font-bold text-xs text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300 transition ease-in-out duration-150 shadow-sm">
                                                <span>✏️</span>
                                                <span>تعديل</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
