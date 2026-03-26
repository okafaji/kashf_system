<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center; direction: rtl;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                نتائج البحث عن: {{ $search }}
            </h2>
            <a href="{{ route('payrolls.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 transition ease-in-out duration-150">
                رجوع لسجل الكشوفات
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-4 rounded-lg shadow-sm mb-6" dir="rtl">
                <form action="/payrolls" method="GET" class="flex gap-1">
                    <input type="text" name="search" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full text-sm"
                           placeholder="ابحث عن رقم الأمر أو رقم الكشف أو اسم المنتسب..." value="{{ $search }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150 whitespace-nowrap">بحث</button>
                    <a href="/payrolls" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 transition ease-in-out duration-150 whitespace-nowrap">تصفية</a>
                </form>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">بحث سريع بالاسم لعرض الاحصائيات</label>
                    <select id="payroll_name_search" class="w-full"></select>
                    <p class="text-xs text-gray-500 mt-1">اختر الاسم الكامل من الاقتراحات لفتح صفحة الاحصائيات.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse" dir="rtl">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr class="text-gray-600 text-sm font-bold">
                                <th class="p-4">الاسم</th>
                                <th class="p-4">رقم الكشف</th>
                                <th class="p-4">رقم الأمر</th>
                                <th class="p-4">جهة الايفاد</th>
                                <th class="p-4">الفترة</th>
                                <th class="p-4">المبلغ</th>
                                <th class="p-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($results as $row)
                                <tr class="hover:bg-blue-50/50 transition duration-150 {{ $row->is_archived ? 'opacity-60' : '' }}">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">{{ $row->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $row->department }} - {{ $row->job_title }}</div>
                                        @if($row->is_archived)
                                            <span class="text-xs bg-gray-500 text-white px-2 py-1 rounded-full mt-1 inline-block">مؤرشف</span>
                                        @endif
                                    </td>
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
                                            <a href="{{ route('payrolls.show', ['kashf_no' => $row->kashf_no, 'back' => url()->full()]) }}"
                                                              class="inline-flex items-center px-3 py-1.5 bg-teal-500 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-200 transition ease-in-out duration-150">
                                                عرض الكشف
                                            </a>
                                            <a href="{{ route('payrolls.edit', ['id' => $row->id, 'back' => url()->full()]) }}"
                                                              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 border border-transparent rounded-md font-bold text-xs text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300 transition ease-in-out duration-150 shadow-sm">
                                                <span>✏️</span>
                                                <span>تعديل</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">
                                        لا توجد نتائج مطابقة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4" dir="ltr">
                {{ $results->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
