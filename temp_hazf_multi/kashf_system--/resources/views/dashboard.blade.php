<x-app-layout>
    <!-- المحتوى الرئيسي -->
    <div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold text-gray-900">⚡ اختصارات سريعة</h3>
                <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-lg border border-blue-300">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700">{{ date('Y/m/d') }}</span>
                    <span class="text-xs text-gray-500">|</span>
                    <span class="text-xs font-semibold text-gray-700">🕐 <span id="dashboardTime">{{ date('H:i') }}</span></span>
                </div>
            </div>
            <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">4 وظائف رئيسية</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Add New Payroll -->
            <a href="{{ route('payrolls.create') }}" class="group flex items-center gap-3 bg-white hover:bg-blue-50 border-l-4 border-blue-500 hover:border-blue-600 rounded-lg p-3 transition-all duration-200 shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 bg-blue-100 rounded p-1.5 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">كشف جديد</p>
                    <p class="text-xs text-gray-500">إضافة إيفاد</p>
                </div>
            </a>

            <!-- Payrolls Registry -->
            <a href="{{ route('payrolls.index') }}" class="group flex items-center gap-3 bg-white hover:bg-green-50 border-l-4 border-green-500 hover:border-green-600 rounded-lg p-3 transition-all duration-200 shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 bg-green-100 rounded p-1.5 group-hover:bg-green-200 transition-colors">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $statsData['total_payrolls'] }}</p>
                    <p class="text-xs text-gray-500">الكشوفات</p>
                </div>
            </a>

            <!-- Employees Management -->
            <a href="{{ route('employees.index') }}" class="group flex items-center gap-3 bg-white hover:bg-amber-50 border-l-4 border-amber-500 hover:border-amber-600 rounded-lg p-3 transition-all duration-200 shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 bg-amber-100 rounded p-1.5 group-hover:bg-amber-200 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm6-6a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $statsData['total_employees'] }}</p>
                    <p class="text-xs text-gray-500">المنتسبين</p>
                </div>
            </a>

            <!-- Settings -->
            <a href="{{ route('signatures.index') }}" class="group flex items-center gap-3 bg-white hover:bg-purple-50 border-l-4 border-purple-500 hover:border-purple-600 rounded-lg p-3 transition-all duration-200 shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 bg-purple-100 rounded p-1.5 group-hover:bg-purple-200 transition-colors">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">التواقيع</p>
                    <p class="text-xs text-gray-500">إدارة</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Statistics Cards - New Layout -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <!-- Payrolls Statistics Card - مع التبويبات -->
        <div class="bg-white rounded-lg shadow-md border-t-4 border-blue-500 p-6">
            <!-- Tab Navigation -->
            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button id="tabMyStats" data-tab="my-stats" class="px-4 py-2 text-sm font-semibold text-blue-600 border-b-2 border-blue-600 focus:outline-none">
                    📊 احصائياتي
                </button>
                <button id="tabTeamStats" data-tab="team-stats" class="px-4 py-2 text-sm font-semibold text-gray-600 border-b-2 border-transparent hover:text-gray-900 focus:outline-none">
                    👥 احصائيات الفريق
                </button>
            </div>

            <!-- Tab Content: My Stats -->
            <div id="content-my-stats" class="tab-content">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    احصائيات الكشوفات <span class="text-sm text-blue-600 font-normal">({{ $statsData['today_payrolls'] }})</span>
                </h3>

                <!-- Filters for My Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 pb-4 border-b border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📅 اختر السنة</label>
                        <select id="filterYear" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">اختر سنة...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📆 اختر الشهر</label>
                        <select id="filterMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="">اختر شهراً...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📆 اختر اليوم</label>
                        <select id="filterDay" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="">اختر يوماً...</option>
                        </select>
                    </div>
                </div>

                <!-- Stats Cards - My Stats -->
                <div class="grid grid-cols-4 gap-3">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <p class="text-xs text-gray-600 font-semibold mb-2">🔹 الإجمالي</p>
                        <p class="text-2xl font-bold text-blue-900 mb-3" id="statTotalPayrolls">{{ $statsData['total_payrolls'] }}</p>
                        <div class="pt-3 border-t border-blue-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-lg font-bold text-blue-700" id="statTotalAmount">{{ number_format($statsData['total_amount']) }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg p-4 border border-cyan-200">
                        <p class="text-xs text-gray-600 font-semibold mb-2">📅 السنة</p>
                        <p class="text-2xl font-bold text-cyan-900 mb-3" id="statFilteredPayrolls">{{ $statsData['this_year_payrolls'] }}</p>
                        <div class="pt-3 border-t border-cyan-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-lg font-bold text-cyan-700" id="statFilteredAmount">{{ number_format($statsData['this_year_amount']) }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200">
                        <p class="text-xs text-gray-600 font-semibold mb-2">📆 الشهر</p>
                        <p class="text-2xl font-bold text-indigo-900 mb-3" id="statFilteredPayrolls2">{{ $statsData['this_month_payrolls'] }}</p>
                        <div class="pt-3 border-t border-indigo-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-lg font-bold text-indigo-700" id="statFilteredAmount2">{{ number_format($statsData['this_month_amount']) }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                        <p class="text-xs text-gray-600 font-semibold mb-2">📆 اليوم</p>
                        <p class="text-2xl font-bold text-purple-900 mb-3" id="statFilteredPayrolls3">{{ $statsData['today_payrolls'] }}</p>
                        <div class="pt-3 border-t border-purple-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-lg font-bold text-purple-700" id="statFilteredAmount3">{{ number_format($statsData['today_amount']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Team Stats (يظهر فقط لمسؤولي الوحدات والشعب) -->
            @if(auth()->user()->hasRole(['مسؤول وحدة', 'مسؤول شعبة', 'رئيس قسم', 'admin']))
            <div id="content-team-stats" class="tab-content hidden">
                <div class="flex items-end gap-0.5 mb-4 flex-nowrap overflow-x-auto">
                    <div class="flex items-end gap-0.5 flex-nowrap shrink-0">
                        <div class="w-64 shrink-0">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">👤 اختر المنتسب</label>
                            <select id="filterTeamUser" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">كل المنتسبين ضمن الصلاحية...</option>
                            </select>
                        </div>
                        <div class="w-40 shrink-0">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">🗓️ من تاريخ</label>
                            <input type="text" id="filterFromDate" placeholder="yyyy/mm/dd" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="w-40 shrink-0">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">🗓️ إلى تاريخ</label>
                            <input type="text" id="filterToDate" placeholder="yyyy/mm/dd" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Filters for Team Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6 pb-4 border-b border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📅 اختر السنة</label>
                        <select id="filterYearTeam" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">اختر سنة...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📆 اختر الشهر</label>
                        <select id="filterMonthTeam" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="">اختر شهراً...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">📆 اختر اليوم</label>
                        <select id="filterDayTeam" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="">اختر يوماً...</option>
                        </select>
                    </div>
                </div>

                <!-- Stats Cards - Team Stats - سطر ثاني للبطاقات الخمسة -->
                <div class="grid grid-cols-5 gap-3">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-3 border border-green-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">🔹 الإجمالي</p>
                        <p class="text-2xl font-bold text-green-900 mb-2" id="teamStatTotalPayrolls">0</p>
                        <div class="pt-2 border-t border-green-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-base font-bold text-green-700" id="teamStatTotalAmount">0</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 border border-teal-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">📅 السنة</p>
                        <p class="text-2xl font-bold text-teal-900 mb-2" id="teamStatFilteredPayrolls">0</p>
                        <div class="pt-2 border-t border-teal-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-base font-bold text-teal-700" id="teamStatFilteredAmount">0</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-3 border border-emerald-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">📆 الشهر</p>
                        <p class="text-2xl font-bold text-emerald-900 mb-2" id="teamStatFilteredPayrolls2">0</p>
                        <div class="pt-2 border-t border-emerald-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-base font-bold text-emerald-700" id="teamStatFilteredAmount2">0</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-lime-50 to-lime-100 rounded-lg p-3 border border-lime-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">📆 اليوم</p>
                        <p class="text-2xl font-bold text-lime-900 mb-2" id="teamStatFilteredPayrolls3">0</p>
                        <div class="pt-2 border-t border-lime-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-base font-bold text-lime-700" id="teamStatFilteredAmount3">0</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-3 border border-indigo-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">🗓️ بين تاريخين</p>
                        <p class="text-2xl font-bold text-indigo-900 mb-2" id="teamStatDateRange">0</p>
                        <div class="pt-2 border-t border-indigo-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">المبلغ (د.ع)</p>
                            <p class="text-base font-bold text-indigo-700" id="teamStatDateRangeAmount">0</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                    <div class="bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg p-3 border border-sky-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">🆕 تم إنشاؤها</p>
                        <p class="text-2xl font-bold text-sky-900" id="teamStatCreatedCount">0</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-3 border border-amber-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">✏️ تم تعديلها</p>
                        <p class="text-2xl font-bold text-amber-900" id="teamStatEditedCount">0</p>
                    </div>
                    <div class="bg-gradient-to-br from-fuchsia-50 to-fuchsia-100 rounded-lg p-3 border border-fuchsia-200">
                        <p class="text-xs text-gray-600 font-semibold mb-1">🖨️ تم طباعتها</p>
                        <p class="text-2xl font-bold text-fuchsia-900" id="teamStatPrintedCount">0</p>
                        <p class="text-xs text-fuchsia-700 mt-1">مرات الطباعة: <span id="teamStatPrintActions">0</span></p>
                    </div>
                </div>

                <div class="text-xs text-gray-500 italic">
                    📊 ملاحظة: الأرقام أعلاه تمثل <strong>عدد الكشوفات الفريدة</strong> (kashf_no) وليس عدد الموظفين
                </div>
            </div>
            @endif
        </div>
    <div class="mb-8 bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">📌 ملخص تنفيذي لحالة الكشوفات</h3>
            <span class="text-xs text-gray-500">محدث تلقائياً</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs text-gray-600 mb-1">جاهز للطباعة</p>
                <p class="text-2xl font-bold text-amber-700">{{ number_format($statsData['ready_for_print_payrolls'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs text-gray-600 mb-1">مطبوع</p>
                <p class="text-2xl font-bold text-emerald-700">{{ number_format($statsData['printed_payrolls'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs text-gray-600 mb-1">مؤرشف</p>
                <p class="text-2xl font-bold text-slate-700">{{ number_format($statsData['archived_payrolls'] ?? 0) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-2 text-right">القسم</th>
                        <th class="p-2 text-right">عدد الكشوفات هذا الشهر</th>
                        <th class="p-2 text-right">إجمالي المبلغ (د.ع)</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse(($departmentMonthlyStats ?? collect()) as $row)
                        <tr>
                            <td class="p-2">{{ $row->department }}</td>
                            <td class="p-2">{{ number_format($row->payroll_count) }}</td>
                            <td class="p-2 font-semibold text-green-700">{{ number_format($row->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-3 text-center text-gray-500">لا توجد بيانات شهرية حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Payrolls (Takes 2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">📋 آخر الكشوفات</h3>
                    <p class="text-xs text-gray-500 mt-1">أحدث 10 كشوفات مسجلة</p>
                </div>
                <a href="{{ route('payrolls.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
                    عرض الكل
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">الاسم</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">القسم</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">الجهة</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">المبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentPayrolls as $payroll)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payroll['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $payroll['department'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $payroll['destination'] }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-green-600">{{ number_format($payroll['total_amount']) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $payroll['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    لا توجد كشوفات بعد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">🎯 أكثر الجهات</h3>
                <p class="text-xs text-gray-500 mt-1">الجهات الأكثر استقطاباً</p>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($topDestinations as $destination)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-blue-50 transition-colors cursor-pointer">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $destination->destination ?? 'غير محدد' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $destination->count }} كشف</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-blue-600">{{ number_format($destination->total) }}</p>
                            <div class="w-12 h-8 bg-blue-100 rounded mt-1 flex items-center justify-center">
                                <span class="text-xs font-semibold text-blue-700">{{ $destination->count }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>
                        </svg>
                        لا توجد بيانات
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Employees -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mb-8">
        <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">👥 آخر المنتسبين المضافين</h3>
                <p class="text-xs text-gray-500 mt-1">أحدث 5 موظفين تم إضافتهم</p>
            </div>
            <a href="{{ route('employees.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
                عرض الكل
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">الاسم</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">الرقم الوظيفي</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">القسم</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700">المسمى الوظيفي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentEmployees as $employee)
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $employee['name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $employee['employee_id'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $employee['department'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $employee['job_title'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm6-6a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                لا يوجد موظفين بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }
    </style>

    <script>
        // تهيئة الفيلترات الديناميكية
        document.addEventListener('DOMContentLoaded', function() {
            const filterYear = document.getElementById('filterYear');
            const filterMonth = document.getElementById('filterMonth');
            const filterDay = document.getElementById('filterDay');

            // تحميل السنوات المتاحة
            function loadYears() {
                console.log('تحميل السنوات...');
                fetch('/api/dashboard/years')
                    .then(response => response.json())
                    .then(years => {
                        console.log('السنوات المحملة:', years);
                        const currentYear = new Date().getFullYear();

                        // إضافة خيار "الكل"
                        if (filterYear.options.length === 1) {
                            const allOption = document.createElement('option');
                            allOption.value = '';
                            allOption.textContent = '📅 الكل';
                            filterYear.appendChild(allOption);
                        }

                        years.forEach(year => {
                            const option = document.createElement('option');
                            option.value = year;
                            option.textContent = year;
                            if (year === currentYear) {
                                option.selected = true;
                            }
                            filterYear.appendChild(option);
                        });

                        // تحميل الأشهر للسنة الحالية/المحددة
                        if (filterYear.value) {
                            loadMonths(filterYear.value);
                        }

                        // تحديث الإحصائيات الأولية
                        updateStats();
                    })
                    .catch(error => console.error('خطأ في تحميل السنوات:', error));
            }

            // تحميل الأشهر حسب السنة المحددة (عند تغيير السنة فقط)
            function loadMonths(year) {
                console.log('تحميل أشهر السنة:', year);

                // احفظ القيمة الحالية للشهر والأيام
                const currentMonth = filterMonth.value;
                const currentDay = filterDay.value;

                filterMonth.innerHTML = '<option value="">📆 الكل</option>';
                filterMonth.disabled = !year;

                // حافظ على حالة الأيام ولا تمسحها عند تغيير السنة فحسب
                // لكن عطلها إذا لم تكن هناك سنة مختارة
                if (!year) {
                    filterDay.disabled = true;
                    return;
                }

                fetch(`/api/dashboard/months?year=${year}`)
                    .then(response => response.json())
                    .then(months => {
                        console.log('الأشهر المحملة:', months);
                        months.forEach(month => {
                            const option = document.createElement('option');
                            option.value = month.value;
                            option.textContent = month.label;
                            filterMonth.appendChild(option);
                        });

                        // إذا كانت هناك قيمة شهر سابقة موجودة في القائمة الجديدة، أخترها
                        if (currentMonth && filterMonth.querySelector(`option[value="${currentMonth}"]`)) {
                            filterMonth.value = currentMonth;
                            // أعد تحميل الأيام للشهر المحفوظ مع السنة الجديدة
                            loadDays(year, currentMonth);
                        } else {
                            // إذا لم توجد القيمة السابقة، امسح selection الأيام
                            filterDay.innerHTML = '<option value="">📆 الكل</option>';
                            filterDay.disabled = true;
                        }
                    })
                    .catch(error => console.error('خطأ في تحميل الأشهر:', error));
            }

            // تحميل الأيام حسب الشهر المحدد (عند تغيير الشهر فقط)
            function loadDays(year, month) {
                console.log('تحميل أيام الشهر:', year, month);

                // احفظ القيمة الحالية للأيام
                const currentDay = filterDay.value;

                // إعادة تعيين الأيام فقط، الأشهر لا تتغيّر
                filterDay.innerHTML = '<option value="">📆 الكل</option>';
                filterDay.disabled = !month;

                if (!month) {
                    return;
                }

                fetch(`/api/dashboard/days?year=${year}&month=${month}`)
                    .then(response => response.json())
                    .then(days => {
                        console.log('الأيام المحملة:', days);
                        days.forEach(day => {
                            const option = document.createElement('option');
                            option.value = day;
                            option.textContent = day;
                            filterDay.appendChild(option);
                        });

                        // إذا كانت هناك قيمة يوم سابقة موجودة في القائمة الجديدة، أخترها
                        if (currentDay && filterDay.querySelector(`option[value="${currentDay}"]`)) {
                            filterDay.value = currentDay;
                        }
                    })
                    .catch(error => console.error('خطأ في تحميل الأيام:', error));
            }

            // تحديث الإحصائيات - 4 طلبات منفصلة
            function updateStats() {
                const year = filterYear.value || new Date().getFullYear();
                const month = filterMonth.value || null;
                const day = filterDay.value || null;

                console.log('جلب الإحصائيات الأربع - السنة:', year, 'الشهر:', month, 'اليوم:', day);

                // 1️⃣ الإجمالي الكلي (بدون فيلترات)
                fetch('/api/dashboard/stats')
                    .then(response => response.json())
                    .then(data => {
                        console.log('الإجمالي الكلي:', data);
                        document.getElementById('statTotalPayrolls').textContent = data.total_payrolls || 0;
                        const totalAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                        document.getElementById('statTotalAmount').textContent = totalAmount;
                    })
                    .catch(error => console.error('خطأ في جلب الإجمالي:', error));

                // 2️⃣ إحصائية السنة (year فقط)
                fetch(`/api/dashboard/stats?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('إحصائية السنة:', data);
                        document.getElementById('statFilteredPayrolls').textContent = data.total_payrolls || 0;
                        const yearAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                        document.getElementById('statFilteredAmount').textContent = yearAmount;
                    })
                    .catch(error => console.error('خطأ في جلب إحصائية السنة:', error));

                // 3️⃣ إحصائية الشهر (year + month)
                if (month) {
                    fetch(`/api/dashboard/stats?year=${year}&month=${month}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('إحصائية الشهر:', data);
                            document.getElementById('statFilteredPayrolls2').textContent = data.total_payrolls || 0;
                            const monthAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                            document.getElementById('statFilteredAmount2').textContent = monthAmount;
                        })
                        .catch(error => console.error('خطأ في جلب إحصائية الشهر:', error));
                } else {
                    // إذا لم يكن هناك شهر مختار، عرّض 0
                    document.getElementById('statFilteredPayrolls2').textContent = 0;
                    document.getElementById('statFilteredAmount2').textContent = 0;
                }

                // 4️⃣ إحصائية اليوم (year + month + day)
                if (month && day) {
                    fetch(`/api/dashboard/stats?year=${year}&month=${month}&day=${day}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('إحصائية اليوم:', data);
                            document.getElementById('statFilteredPayrolls3').textContent = data.total_payrolls || 0;
                            const dayAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                            document.getElementById('statFilteredAmount3').textContent = dayAmount;
                        })
                        .catch(error => console.error('خطأ في جلب إحصائية اليوم:', error));
                } else {
                    // إذا لم يكن هناك يوم مختار، عرّض 0
                    document.getElementById('statFilteredPayrolls3').textContent = 0;
                    document.getElementById('statFilteredAmount3').textContent = 0;
                }
            }

            // Event Listeners - كل اختيار يؤثر على نفسه والمرتبط به فقط

            // اختيار السنة → يحدّث الأشهر فقط
            filterYear.addEventListener('change', function() {
                console.log('✓ تغيير السنة:', this.value);
                loadMonths(this.value);
                updateStats();
            });

            // اختيار الشهر → يحدّث الأيام فقط (لا يهز السنة)
            filterMonth.addEventListener('change', function() {
                console.log('✓ تغيير الشهر:', this.value);
                const year = filterYear.value || new Date().getFullYear();
                loadDays(year, this.value);
                updateStats();
            });

            // اختيار اليوم → يحدّث الإحصائيات فقط (لا يهز شيء آخر)
            filterDay.addEventListener('change', function() {
                console.log('✓ تغيير اليوم:', this.value);
                updateStats();
            });

            // تحميل البيانات الأولية
            loadYears();
        });

        // ============ TAB SWITCHING LOGIC ============
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');

            // 🔄 استعادة آخر tab تم اختياره
            const savedTab = localStorage.getItem('selectedDashboardTab') || 'my-stats';

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // حفظ الـ tab المختار
                    localStorage.setItem('selectedDashboardTab', tabName);

                    // إخفاء جميع التبويبات
                    tabContents.forEach(content => content.classList.add('hidden'));

                    // إزالة التنسيق النشط من جميع الأزرار
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-600', 'border-blue-600');
                        btn.classList.add('text-gray-600', 'border-transparent');
                    });

                    // إظهار التبويب المحدد (مع فحص وجوده)
                    const contentElement = document.getElementById(`content-${tabName}`);
                    if (contentElement) {
                        contentElement.classList.remove('hidden');

                        // تطبيق التنسيق النشط على الزر المحدد
                        this.classList.remove('text-gray-600', 'border-transparent');
                        this.classList.add('text-blue-600', 'border-blue-600');

                        // إذا كان التبويب هو team-stats، حمّل بيانات الفريق
                        if (tabName === 'team-stats') {
                            console.log('📊 تحميل إحصائيات الفريق...');
                            loadTeamStatsInitial();
                        }
                    }
                });
            });

            // ✨ تطبيق الـ saved tab على الحمل الأولي
            const savedTabButton = document.querySelector(`[data-tab="${savedTab}"]`);
            if (savedTabButton) {
                savedTabButton.click();
            } else {
                // إذا لم يكن هناك saved tab، اختر أول tab available
                if (tabButtons.length > 0) {
                    tabButtons[0].click();
                }
            }
        });

        // ============ TEAM STATS LOGIC ============
        function getSelectedTeamUserId() {
            const filterTeamUser = document.getElementById('filterTeamUser');
            if (!filterTeamUser || !filterTeamUser.value) {
                return null;
            }

            return parseInt(filterTeamUser.value, 10);
        }

        function loadTeamMembers() {
            const filterTeamUser = document.getElementById('filterTeamUser');
            if (!filterTeamUser) return Promise.resolve();

            const currentSelected = filterTeamUser.value;

            return fetch('/api/user-team-members', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(members => {
                    filterTeamUser.innerHTML = '<option value="">كل المنتسبين ضمن الصلاحية...</option>';

                    members.forEach(member => {
                        const option = document.createElement('option');
                        option.value = member.id;
                        option.textContent = `${member.name} (${member.total_payrolls})`;
                        filterTeamUser.appendChild(option);
                    });

                    if (currentSelected && filterTeamUser.querySelector(`option[value="${currentSelected}"]`)) {
                        filterTeamUser.value = currentSelected;
                    }
                })
                .catch(error => console.error('❌ خطأ في تحميل قائمة المنتسبين:', error));
        }

        function loadTeamStatsInitial() {
            const filterYearTeam = document.getElementById('filterYearTeam');

            if (!filterYearTeam) return; // إذا لم تكن هناك عناصر، توجد صلاحيات غير كافية

            loadTeamMembers().finally(() => {
                loadTeamYears();
            });
        }

        function loadTeamYears() {
            const filterYearTeam = document.getElementById('filterYearTeam');
            if (!filterYearTeam) return;

            const selectedUserId = getSelectedTeamUserId();
            const yearsUrl = selectedUserId
                ? `/api/user-years?selected_user_id=${selectedUserId}`
                : '/api/user-years';

            console.log('تحميل السنوات للفريق...');
            fetch(yearsUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(years => {
                    console.log('✓ السنوات المتاحة للفريق:', years);
                    const currentYear = new Date().getFullYear();

                    filterYearTeam.innerHTML = '<option value="">اختر سنة...</option>';

                    years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        if (year === currentYear) {
                            option.selected = true;
                        }
                        filterYearTeam.appendChild(option);
                    });

                    if (filterYearTeam.value) {
                        loadTeamMonths(filterYearTeam.value);
                    }

                    updateTeamStats();
                })
                .catch(error => console.error('❌ خطأ في تحميل سنوات الفريق:', error));
        }

        function loadTeamMonths(year) {
            const filterMonthTeam = document.getElementById('filterMonthTeam');
            const filterDayTeam = document.getElementById('filterDayTeam');
            if (!filterMonthTeam) return;

            const selectedUserId = getSelectedTeamUserId();
            const monthsUrl = selectedUserId
                ? `/api/user-months?year=${year}&selected_user_id=${selectedUserId}`
                : `/api/user-months?year=${year}`;

            console.log('تحميل أشهر الفريق للسنة:', year);

            const currentMonth = filterMonthTeam.value;

            filterMonthTeam.innerHTML = '<option value="">اختر شهراً...</option>';
            filterMonthTeam.disabled = !year;

            if (!year) {
                filterDayTeam.innerHTML = '<option value="">اختر يوماً...</option>';
                filterDayTeam.disabled = true;
                return;
            }

            fetch(monthsUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(months => {
                    console.log('✓ الأشهر المتاحة للفريق:', months);
                    months.forEach(month => {
                        const option = document.createElement('option');
                        option.value = month.value;
                        option.textContent = month.label;
                        filterMonthTeam.appendChild(option);
                    });

                    if (currentMonth && filterMonthTeam.querySelector(`option[value="${currentMonth}"]`)) {
                        filterMonthTeam.value = currentMonth;
                        loadTeamDays(year, currentMonth);
                    } else {
                        filterDayTeam.innerHTML = '<option value="">اختر يوماً...</option>';
                        filterDayTeam.disabled = true;
                    }

                    updateTeamStats();
                })
                .catch(error => console.error('❌ خطأ في تحميل أشهر الفريق:', error));
        }

        function loadTeamDays(year, month) {
            const filterDayTeam = document.getElementById('filterDayTeam');
            if (!filterDayTeam) return;

            const selectedUserId = getSelectedTeamUserId();
            const daysUrl = selectedUserId
                ? `/api/user-days?year=${year}&month=${month}&selected_user_id=${selectedUserId}`
                : `/api/user-days?year=${year}&month=${month}`;

            console.log('تحميل أيام الفريق:', year, month);

            const currentDay = filterDayTeam.value;

            filterDayTeam.innerHTML = '<option value="">اختر يوماً...</option>';
            filterDayTeam.disabled = !month;

            if (!month) {
                return;
            }

            fetch(daysUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(days => {
                    console.log('✓ الأيام المتاحة للفريق:', days);
                    days.forEach(day => {
                        const option = document.createElement('option');
                        option.value = day;
                        option.textContent = day;
                        filterDayTeam.appendChild(option);
                    });

                    if (currentDay && filterDayTeam.querySelector(`option[value="${currentDay}"]`)) {
                        filterDayTeam.value = currentDay;
                    }

                    updateTeamStats();
                })
                .catch(error => console.error('❌ خطأ عند تحميل أيام الفريق:', error));
        }

        function updateTeamStats() {
            const filterYearTeam = document.getElementById('filterYearTeam');
            const filterMonthTeam = document.getElementById('filterMonthTeam');
            const filterDayTeam = document.getElementById('filterDayTeam');

            if (!filterYearTeam) return;

            const year = filterYearTeam.value || new Date().getFullYear();
            const month = filterMonthTeam.value || null;
            const day = filterDayTeam.value || null;
            const selectedUserId = getSelectedTeamUserId();
            const selectedUserParam = selectedUserId ? `selected_user_id=${selectedUserId}` : '';

            console.log('تحديث إحصائيات الفريق - السنة:', year, 'الشهر:', month, 'اليوم:', day);

            // 1️⃣ الإجمالي الكلي (بدون فيلترات)
            fetch(`/api/user-stats${selectedUserParam ? `?${selectedUserParam}` : ''}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('✓ بيانات الإجمالي:', data);
                    const element1 = document.getElementById('teamStatTotalPayrolls');
                    const element2 = document.getElementById('teamStatTotalAmount');
                    if (element1) {
                        element1.textContent = data.total_payrolls || 0;
                    }
                    if (element2) {
                        const totalAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                        element2.textContent = totalAmount;
                    }

                    const createdElement = document.getElementById('teamStatCreatedCount');
                    const editedElement = document.getElementById('teamStatEditedCount');
                    const printedElement = document.getElementById('teamStatPrintedCount');
                    const printActionsElement = document.getElementById('teamStatPrintActions');

                    if (createdElement) {
                        createdElement.textContent = data.created_payrolls || 0;
                    }
                    if (editedElement) {
                        editedElement.textContent = data.modified_payrolls || 0;
                    }
                    if (printedElement) {
                        printedElement.textContent = data.printed_payrolls || 0;
                    }
                    if (printActionsElement) {
                        printActionsElement.textContent = data.print_actions_count || 0;
                    }
                })
                .catch(error => console.error('❌ خطأ في جلب إجمالي الفريق:', error));

            // 2️⃣ إحصائية السنة
            fetch(`/api/user-stats?year=${year}${selectedUserId ? `&selected_user_id=${selectedUserId}` : ''}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('✓ بيانات السنة:', data);
                    const element1 = document.getElementById('teamStatFilteredPayrolls');
                    const element2 = document.getElementById('teamStatFilteredAmount');
                    if (element1) {
                        element1.textContent = data.total_payrolls || 0;
                    }
                    if (element2) {
                        const yearAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                        element2.textContent = yearAmount;
                    }
                })
                .catch(error => console.error('❌ خطأ في جلب سنة الفريق:', error));

            // 3️⃣ إحصائية الشهر
            if (month) {
                fetch(`/api/user-stats?year=${year}&month=${month}${selectedUserId ? `&selected_user_id=${selectedUserId}` : ''}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('✓ بيانات الشهر:', data);
                        const element1 = document.getElementById('teamStatFilteredPayrolls2');
                        const element2 = document.getElementById('teamStatFilteredAmount2');
                        if (element1) {
                            element1.textContent = data.total_payrolls || 0;
                        }
                        if (element2) {
                            const monthAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                            element2.textContent = monthAmount;
                        }
                    })
                    .catch(error => console.error('❌ خطأ في جلب شهر الفريق:', error));
            } else {
                document.getElementById('teamStatFilteredPayrolls2').textContent = 0;
                document.getElementById('teamStatFilteredAmount2').textContent = 0;
            }

            // 4️⃣ إحصائية اليوم
            if (month && day) {
                fetch(`/api/user-stats?year=${year}&month=${month}&day=${day}${selectedUserId ? `&selected_user_id=${selectedUserId}` : ''}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('✓ بيانات اليوم:', data);
                        const element1 = document.getElementById('teamStatFilteredPayrolls3');
                        const element2 = document.getElementById('teamStatFilteredAmount3');
                        if (element1) {
                            element1.textContent = data.total_payrolls || 0;
                        }
                        if (element2) {
                            const dayAmount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                            element2.textContent = dayAmount;
                        }
                    })
                    .catch(error => console.error('❌ خطأ في جلب يوم الفريق:', error));
            } else {
                document.getElementById('teamStatFilteredPayrolls3').textContent = 0;
                document.getElementById('teamStatFilteredAmount3').textContent = 0;
            }
        }

        // ============ EVENT LISTENERS FOR TEAM STATS ============
        document.addEventListener('DOMContentLoaded', function() {
            const filterTeamUser = document.getElementById('filterTeamUser');
            const filterYearTeam = document.getElementById('filterYearTeam');
            const filterMonthTeam = document.getElementById('filterMonthTeam');
            const filterDayTeam = document.getElementById('filterDayTeam');
            const filterFromDate = document.getElementById('filterFromDate');
            const filterToDate = document.getElementById('filterToDate');

            if (!filterYearTeam) return; // لا توجد عناصر = لا توجد صلاحيات

            if (filterTeamUser) {
                filterTeamUser.addEventListener('change', function() {
                    console.log('✓ تغيير المنتسب:', this.value || 'الكل');
                    loadTeamYears();
                });
            }

            filterYearTeam.addEventListener('change', function() {
                console.log('✓ تغيير سنة الفريق:', this.value);
                loadTeamMonths(this.value);
            });

            filterMonthTeam.addEventListener('change', function() {
                console.log('✓ تغيير شهر الفريق:', this.value);
                const year = filterYearTeam.value || new Date().getFullYear();
                loadTeamDays(year, this.value);
            });

            filterDayTeam.addEventListener('change', function() {
                console.log('✓ تغيير يوم الفريق:', this.value);
                updateTeamStats();
            });

            // البحث بين تاريخين - تلقائي عند اختيار "إلى تاريخ"
            if (filterToDate) {
                filterToDate.addEventListener('change', function() {
                    const fromDate = filterFromDate.value;
                    const toDate = this.value;

                    if (!fromDate) {
                        alert('الرجاء اختيار "من تاريخ" أولاً');
                        filterToDate.value = ''; // إعادة تعيين الحقل
                        return;
                    }

                    if (fromDate > toDate) {
                        alert('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
                        filterToDate.value = ''; // إعادة تعيين الحقل
                        return;
                    }

                    console.log('✓ البحث التلقائي بين:', fromDate, 'و', toDate);
                    searchByDateRange(fromDate, toDate);
                });
            }
        });

        // دالة البحث بين تاريخين
        function searchByDateRange(fromDate, toDate) {
            const selectedUserId = getSelectedTeamUserId();
            const dateRangeUrl = selectedUserId
                ? `/api/user-stats?from_date=${fromDate}&to_date=${toDate}&selected_user_id=${selectedUserId}`
                : `/api/user-stats?from_date=${fromDate}&to_date=${toDate}`;

            fetch(dateRangeUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('✓ بيانات بين التاريخين:', data);
                    const element1 = document.getElementById('teamStatDateRange');
                    const element2 = document.getElementById('teamStatDateRangeAmount');
                    if (element1) {
                        element1.textContent = data.total_payrolls || 0;
                    }
                    if (element2) {
                        const amount = new Intl.NumberFormat('ar-IQ').format(data.total_amount || 0);
                        element2.textContent = amount;
                    }
                })
                .catch(error => console.error('❌ خطأ في البحث بين التاريخين:', error));
        }

        // تحديث الساعة في الوقت الفعلي (Dashboard)
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const dashboardTimeElement = document.getElementById('dashboardTime');
            if (dashboardTimeElement) {
                dashboardTimeElement.textContent = `${hours}:${minutes}`;
            }
        }
        updateTime(); // تحديث فوري عند تحميل الصفحة
        setInterval(updateTime, 1000); // تحديث كل ثانية
    </script>
</x-app-layout>
