<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-4 shadow-sm text-right" dir="rtl">
                    {{ session('success') }}
                </div>
            @endif

                        <!-- القائمة العائمة: بحث وتصفية وإضافة -->
                        <div class="sticky top-20 z-50 w-full" dir="rtl" style="margin-top: 10px;">
                        <div class="sticky top-20 z-50 w-full" dir="rtl" style="margin-top: 10px;">
                            <form action="{{ route('payrolls.index') }}" method="GET" data-no-sticky-actions>
                                <div class="bg-white rounded-lg shadow-lg p-3 mb-2 border border-gray-300 w-full max-w-full" style="box-shadow:0 4px 24px 0 rgba(0,0,0,0.10);">
                                    <div class="flex flex-wrap gap-2 items-center w-full">
                                        <a href="{{ route('payrolls.create') }}"
                                            onclick="localStorage.removeItem('draft_payroll');"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 min-w-max">
                                            إضافة كشف جديد +
                                        </a>
                                        <input type="text" name="search" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-52 min-w-52 text-sm" placeholder="بحث عام (رقم أمر/كشف/اسم)..." value="{{ $filters['search'] ?? '' }}">
                                        <input type="text" name="admin_order_no" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-40 min-w-40 text-sm" placeholder="رقم الأمر الإداري" value="{{ $filters['admin_order_no'] ?? '' }}">
                                        <select name="department" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-36 min-w-36 text-sm">
                                            <option value="">كل الأقسام</option>
                                            @foreach(($departmentOptions ?? collect()) as $department)
                                                <option value="{{ $department }}" @selected(($filters['department'] ?? '') === $department)>{{ $department }}</option>
                                            @endforeach
                                        </select>
                                        <select name="status" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-32 min-w-32 text-sm">
                                            <option value="">كل الحالات</option>
                                            @foreach(($statusOptions ?? []) as $statusKey => $statusLabel)
                                                <option value="{{ $statusKey }}" @selected(($filters['status'] ?? '') === $statusKey)>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="from_date" value="{{ $filters['from_date'] ?? '' }}" placeholder="yyyy/mm/dd" class="border-gray-300 rounded-md shadow-sm w-32 min-w-32 text-sm">
                                        <input type="text" name="to_date" value="{{ $filters['to_date'] ?? '' }}" placeholder="yyyy/mm/dd" class="border-gray-300 rounded-md shadow-sm w-32 min-w-32 text-sm">
                                        <select name="created_by" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-36 min-w-36 text-sm">
                                            <option value="">كل المستخدمين</option>
                                            @foreach(($creatorOptions ?? collect()) as $creator)
                                                <option value="{{ $creator->id }}" @selected((string)($filters['created_by'] ?? '') === (string)$creator->id)>{{ $creator->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="flex flex-col items-center min-w-48">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">بحث بالاسم الكامل</label>
                                            <select id="payroll_name_search" class="w-full min-w-48"></select>
                                            <p class="text-xs text-gray-500 mt-1">اختر الاسم الكامل من الاقتراحات لعرض الاحصائيات.</p>
                                        </div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150 whitespace-nowrap">بحث</button>
                                        <a href="{{ route('payrolls.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 transition ease-in-out duration-150 whitespace-nowrap">إعادة ضبط</a>
                                    </div>
                                </div>
                                <hr class="my-0 mb-2 border-gray-200">
                            </form>
                        </div>
            @if(session('error'))
                <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-4 shadow-sm text-right" dir="rtl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse" dir="rtl">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr class="text-gray-600 text-sm font-bold">
                                <th class="p-4">رقم الكشف</th>
                                <th class="p-4">رقم الأمر</th>
                                <th class="p-4">تاريخ الأمر</th>
                                <th class="p-4">عدد المنتسبين</th>
                                <th class="p-4">الفترة</th>
                                <th class="p-4">المجموع الكلي</th>
                                <th class="p-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($payrollGroups as $group)
                                <tr class="hover:bg-blue-50/50 transition duration-150 {{ $group->is_archived ? 'opacity-60' : '' }}">
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-medium">
                                                {{ $group->kashf_no }}
                                            </span>
                                            @php
                                                $statusColor = 'bg-blue-500';
                                                if (($group->status ?? '') === 'archived') $statusColor = 'bg-gray-600';
                                                elseif (($group->status ?? '') === 'printed') $statusColor = 'bg-emerald-600';
                                                elseif (($group->status ?? '') === 'draft') $statusColor = 'bg-amber-500';
                                            @endphp
                                            <span class="text-xs text-white px-2 py-1 rounded-full {{ $statusColor }}">{{ $group->status_label ?? 'جاهز للطباعة' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">
                                            {{ $group->admin_order_no }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($group->admin_order_date)->format('Y/m/d') }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold text-sm">
                                            {{ $group->employees_count }} منتسب
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs text-gray-600">
                                        {{ \Carbon\Carbon::parse($group->min_start_date)->format('Y/m/d') }}
                                        -
                                        {{ \Carbon\Carbon::parse($group->max_end_date)->format('Y/m/d') }}
                                    </td>
                                    <td class="p-4">
                                        <span class="text-lg font-bold text-green-700">{{ number_format($group->total_sum) }}</span>
                                        <span class="text-[10px] text-gray-400 mr-1">د.ع</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex gap-1 justify-center">
                                            <a href="{{ route('payrolls.show', ['kashf_no' => $group->kashf_no]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                                عرض التفاصيل
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500">
                                        لا توجد كشوفات مرحلة بعد
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4" dir="ltr">
                {{ $payrollGroups->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
