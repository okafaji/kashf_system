<x-app-layout>
    <x-slot name="header">
        <div style="height: 80px;"></div>
        <div id="floatingToolbar" dir="rtl"
            style="position: fixed; top: 80px; right: 30px; left: 30px; z-index: 1000; background: #fff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 12px 24px; min-width: 350px; max-width: 98vw; display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight" style="margin: 0;">
                تفاصيل الكشف رقم: {{ $kashf_no }}
            </h2>
            <div class="flex gap-2">
                <button type="button" onclick="openAddEmployeeModal()"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    + إضافة منتسب
                </button>
                <a href="{{ route('payrolls.print_multiple', ['kashf_no' => $kashf_no]) }}" target="_blank"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    طباعة الكشف
                </a>
                <a href="{{ request('back') ?? route('payrolls.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    رجوع
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" style="margin-top: 120px;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- متغير JavaScript لحفظ بيانات معدلات الإيفاد -->
            <script>
                window.missionRates = @json(\App\Models\MissionType::select('name', 'responsibility_level', 'daily_rate')->get());
            </script>

            <!-- معلومات عامة عن الكشف -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6" dir="rtl">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">رقم الأمر الإداري</p>
                        <p class="text-lg font-bold text-gray-900">{{ $kashfInfo->admin_order_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">تاريخ الأمر</p>
                        <p class="text-lg font-bold text-gray-900">{{ \Carbon\Carbon::parse($kashfInfo->admin_order_date)->format('Y/m/d') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">عدد المنتسبين</p>
                        <p class="text-lg font-bold text-green-700">{{ $payrolls->count() }} منتسب</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">المجموع الكلي</p>
                        <p class="text-2xl font-bold text-green-700">{{ number_format($payrolls->sum('total_amount')) }} <span class="text-sm">د.ع</span></p>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-4 shadow-sm text-right" dir="rtl">
                    {{ session('success') }}
                </div>
            @endif

            <!-- جدول الإيفادات -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse" dir="rtl">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr class="text-gray-600 text-sm font-bold">
                                <th class="p-3">#</th>
                                <th class="p-3">الاسم</th>
                                <th class="p-3">القسم</th>
                                <th class="p-3">الوظيفة</th>
                                <th class="p-3">جهة الإيفاد</th>
                                <th class="p-3">الفترة</th>
                                <th class="p-3">الأيام</th>
                                <th class="p-3">المبلغ</th>
                                <th class="p-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($payrolls as $index => $p)
                                <tr class="hover:bg-blue-50/50 transition duration-150">
                                    <td class="p-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                                    <td class="p-3">
                                        <div class="font-bold text-gray-900">{{ $p->name }}</div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-600">{{ $p->department }}</td>
                                    <td class="p-3 text-sm text-gray-600">{{ $p->job_title }}</td>
                                    <td class="p-3 text-sm text-gray-600">{{ $p->destination }}</td>
                                    <td class="p-3 text-xs text-gray-600">
                                        {{ \Carbon\Carbon::parse($p->start_date)->format('Y/m/d') }}
                                        -
                                        {{ \Carbon\Carbon::parse($p->end_date)->format('Y/m/d') }}
                                    </td>
                                    <td class="p-3 text-center font-semibold">{{ $p->days_count }}</td>
                                    <td class="p-3">
                                        <span class="text-lg font-bold text-green-700">{{ number_format($p->total_amount) }}</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="inline-flex items-center gap-2 rounded-xl p-1.5">
                                            @if(isset($canEditPayrolls[$p->id]) && $canEditPayrolls[$p->id])
                                                                <a href="{{ route('payrolls.edit', $p->id) }}"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition duration-150 focus:outline-none"
                                                                    style="background-color:#fbbf24;color:#78350f;border:1px solid #f59e0b;"
                                                   title="تعديل بيانات الإيفاد">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-7 14h12a2 2 0 002-2V8.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0015.586 4H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 9.5l-5 5L8 16l1.5-1.5 5-5a1.414 1.414 0 012 2z"/>
                                                    </svg>
                                                    <span>تعديل</span>
                                                </a>
                                                <form action="{{ route('payrolls.destroy', $p->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('هل أنت متأكد من الحذف؟')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition duration-150 focus:outline-none"
                                                            style="background-color:#fca5a5;color:#7f1d1d;border:1px solid #f87171;"
                                                            title="حذف هذا السجل من الكشف">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                                                        </svg>
                                                        <span>حذف</span>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold border border-slate-400 text-slate-500 opacity-85 cursor-not-allowed"
                                                      title="لا تملك صلاحية التعديل على هذا الكشف">
                                                    <span>🔒</span>
                                                    <span>محدود</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="7" class="p-4 text-left font-bold text-gray-700">المجموع الكلي:</td>
                                <td class="p-4">
                                    <span class="text-2xl font-bold text-green-700">{{ number_format($payrolls->sum('total_amount')) }}</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <!-- Modal لإضافة منتسب جديد -->
    <div id="addEmployeeModal" class="hidden fixed inset-0 bg-slate-900/55 backdrop-blur-sm z-50 flex items-center justify-center p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-2xl p-4 md:p-5 max-w-6xl w-full max-h-screen overflow-y-auto border border-slate-200">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl md:text-2xl font-extrabold text-slate-800">إضافة منتسب جديد</h3>
                        <p class="text-xs text-slate-500">ضمن الكشف رقم {{ $kashf_no }}</p>
                    </div>
                </div>
                <button onclick="closeAddEmployeeModal()" class="w-9 h-9 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 text-2xl leading-none">×</button>
            </div>

            <div id="addEmployeeErrors" class="hidden bg-red-50 border border-red-200 text-red-700 p-4 mb-4 text-right rounded-xl">
                <ul id="errorsList" class="mb-0"></ul>
            </div>

            <form id="addEmployeeForm" onsubmit="validateAndSubmitForm(event)">
                @csrf
                <input type="hidden" name="kashf_no" value="{{ $kashf_no }}">
                <input type="hidden" id="employee_id_input" name="employee_id">

                <div class="sticky top-0 z-10 bg-white/95 backdrop-blur border border-slate-200 rounded-xl p-2.5 mb-3">
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-slate-50 rounded-lg p-2">
                            <div class="text-slate-400">الأيام</div>
                            <div id="modalLiveDays" class="font-bold text-slate-700">0</div>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-2">
                            <div class="text-amber-600">اليومية الفعلية</div>
                            <div id="modalLiveDaily" class="font-bold text-amber-700">0</div>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-2">
                            <div class="text-emerald-600">المجموع</div>
                            <div id="modalLiveTotal" class="font-bold text-emerald-700">0</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                    <div class="col-span-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">اختر الموظف *</label>
                        <select id="employee_select" required class="w-full"></select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">الاسم</label>
                        <input type="text" id="name_display" name="name" readonly class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">القسم</label>
                        <input type="text" id="department_display" name="department" readonly class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">العنوان الوظيفي</label>
                        <input type="text" id="job_title_display" name="job_title" readonly class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">جهة الإيفاد</label>
                        <select name="destination" id="destinationSelect" class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" onchange="updateDestinationFieldsModal()">
                            <option value="{{ $kashfInfo->destination }}" data-price="{{ $kashfInfo->daily_allowance }}" data-type="{{ str_starts_with($kashfInfo->destination, 'خارج القطر') ? 'mission' : 'city' }}" selected>{{ $kashfInfo->destination }}</option>
                            @foreach(\App\Models\Governorate::with('cities')->get() as $gov)
                                @foreach($gov->cities as $city)
                                    <option value="{{ $city->name }}" data-price="{{ $city->daily_allowance }}" data-type="city">{{ $gov->name }} - {{ $city->name }}</option>
                                @endforeach
                            @endforeach
                            <optgroup label="خارج القطر">
                                <option value="خارج القطر/1" data-type="mission">خارج القطر/1</option>
                                <option value="خارج القطر/2" data-type="mission">خارج القطر/2</option>
                                <option value="خارج القطر/3" data-type="mission">خارج القطر/3</option>
                                <option value="خارج القطر/4" data-type="mission">خارج القطر/4</option>
                            </optgroup>
                        </select>
                    </div>

                    <div id="responsibilityLevelDivModal" style="display: none;" class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">المستوى الوظيفي</label>
                        <select name="responsibility_level" id="responsibility_level_modal" class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" onchange="calculateDailyModal()">
                            <option value="">اختر المستوى الوظيفي...</option>
                            <option value="منتسب">منتسب</option>
                            <option value="مسؤول شعبة">مسؤول شعبة</option>
                            <option value="مسؤول وجبة">مسؤول وجبة</option>
                            <option value="معاون">معاون</option>
                            <option value="رئيس">رئيس</option>
                            <option value="عضو">عضو</option>
                            <option value="مستشار">مستشار</option>
                            <option value="نائب أمين عام">نائب أمين عام</option>
                            <option value="أمين عام">أمين عام</option>
                        </select>
                    </div>

                    <div class="col-span-4">
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-slate-700 whitespace-nowrap">رقم الأمر الإداري:</label>
                                <input type="text" name="admin_order_no" value="{{ $kashfInfo->admin_order_no }}" required class="px-2.5 py-2 border border-slate-200 rounded-xl" style="width: 140px;">
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-slate-700 whitespace-nowrap">تاريخ الأمر:</label>
                                <input type="text" name="admin_order_date" value="{{ $kashfInfo->admin_order_date }}" placeholder="yyyy/mm/dd" required class="px-2.5 py-2 border border-slate-200 rounded-xl" style="width: 170px;">
                            </div>
                        </div>
                    </div>

                    <div class="col-span-4">
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="text-sm font-medium text-slate-700 whitespace-nowrap">فترة الإيفاد:</label>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-slate-500">من</label>
                                <input type="text" name="start_date" value="{{ $kashfInfo->start_date }}" placeholder="yyyy/mm/dd" required class="px-2.5 py-2 border border-slate-200 rounded-xl" style="width: 170px;" onchange="calculateDaysFromModal()">
                                <label class="text-sm text-slate-500">إلى</label>
                                <input type="text" name="end_date" value="{{ $kashfInfo->end_date }}" placeholder="yyyy/mm/dd" required class="px-2.5 py-2 border border-slate-200 rounded-xl" style="width: 170px;" onchange="calculateDaysFromModal()">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">عدد الأيام</label>
                        <input type="number" id="daysCount" name="days_count" readonly class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">المبلغ اليومي *</label>
                        <input type="number" id="dailyAllowanceInput" name="daily_allowance" value="{{ $kashfInfo->daily_allowance }}" required step="0.01" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" oninput="calculateTotalFromModal()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">أجور المبيت (تلقائي)</label>
                        <input type="number" id="accommodation_fee_input" name="accommodation_fee" step="0.01" value="0" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-500" oninput="calculateTotalFromModal()" inputmode="decimal" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">وصولات أخرى</label>
                        <input type="number" name="receipts_amount" step="0.01" value="0" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" oninput="calculateTotalFromModal()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">نسبة الإيفاد</label>
                        <select name="is_half_allowance" class="w-full px-3 py-2.5 border border-amber-200 rounded-xl bg-amber-50 text-amber-800 font-semibold focus:border-amber-400 focus:ring-2 focus:ring-amber-100" onchange="calculateTotalFromModal()">
                            <option value="0">كامل 100%</option>
                            <option value="1">نصف 50%</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">مبلغ الإيفاد الكلي</label>
                        <input type="number" id="totalAmount" name="total_amount" readonly class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl bg-emerald-50 text-emerald-800 font-bold">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeAddEmployeeModal()" class="px-5 py-2.5 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium">إلغاء</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-sm">حفظ المنتسب</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
