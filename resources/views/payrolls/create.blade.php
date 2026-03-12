

<x-app-layout>
    <x-slot name="header">
        <div class="mt-3 mb-2 flex items-center gap-3" dir="rtl">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mr-2">{{ __('إضافة كشف إيفاد جديد') }}</h2>

            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('excel_input').click()" class="bg-green-600 hover:bg-green-700 text-white h-10 w-36 rounded shadow flex items-center justify-center text-sm">
                    📥 استيراد إكسل
                </button>
                <input type="file" id="excel_input" class="hidden" accept=".xlsx, .xls">
                <button type="button" onclick="clearAllRows()" class="bg-red-600 hover:bg-red-700 text-white h-10 w-36 rounded text-sm font-bold shadow-sm">
                    🗑️ حذف الكل
                </button>
                <button type="submit" id="submitBtn" form="mainPayrollForm" class="bg-blue-600 hover:bg-blue-700 text-white font-bold h-10 w-44 rounded shadow">
                    حفظ وترحيل البيانات
                </button>
            </div>
        </div>

    </x-slot>

    <script>
        // تعريف معرّف المستخدم الحالي ليستخدم في localStorage scope
        window.currentUserId = {{ Auth::id() }};
        console.log('🔒 User ID for localStorage scope:', window.currentUserId);

        // تحميل أسعار الإيفاد (خصوصاً خارج القطر) لاستخدامها في حسابات صفحة الإنشاء
        window.missionRates = @json(
            \App\Models\MissionType::select('name', 'responsibility_level', 'daily_rate')->get()
        );
        console.log('📊 Mission rates loaded:', window.missionRates ? window.missionRates.length : 0);
    </script>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="mb-4 text-right" dir="rtl">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ابحث عن المنتسب:</label>
                    <select id="employee_search" class="w-full"></select>
                </div>

                @if(session('error'))
                    <div class="bg-red-100 p-3 rounded mb-4 text-right" dir="rtl">{{ session('error') }}</div>
                @endif

                <form id="mainPayrollForm" action="{{ route('payrolls.store_multiple') }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-200" id="payrollTable" dir="rtl">
                            <thead class="bg-gray-800 text-white" style="font-size: 13px;">
                                <tr style="vertical-align: middle;">
                                    <th class="border p-2 text-center">الاسم والقسم</th>
                                    <th class="border p-2 text-center">العنوان الوظيفي</th>
                                    <th class="border p-2 text-center" style="width: 140px;">جهة الايفاد</th>
                                    <th class="border p-2 text-center" style="width: 170px;">
                                        <div style="font-size: 11px; margin-bottom: 3px;">رقم الأمر / تاريخه</div>
                                        <div class="flex items-center gap-1 mt-1" style="font-size: 10px;">
                                            <span>رقم:</span>
                                            <input type="text" id="masterOrderNo" class="border border-gray-300 rounded px-1 py-0.5 text-center" placeholder="رقم" style="width: 45px; color: #2563eb; font-weight: 500; font-size: 11px;">
                                            <span>تاريخ:</span>
                                            <input type="text" id="masterOrderDate" class="border border-gray-300 rounded px-1 py-0.5" placeholder="yyyy/mm/dd" style="width: 95px; color: #2563eb; font-weight: 500; font-size: 11px;">
                                        </div>
                                    </th>
                                    <th class="border p-2 text-center" style="width: 170px;">
                                        <div style="font-size: 11px; margin-bottom: 3px;">فترة الإيفاد</div>
                                        <div class="flex items-center gap-1 mt-1" style="font-size: 10px;">
                                            <span>من:</span>
                                            <input type="text" id="masterStartDate" class="border border-gray-300 rounded px-1 py-0.5" title="تاريخ البداية" placeholder="yyyy/mm/dd" style="width: 95px; color: #2563eb; font-weight: 500; font-size: 11px;">
                                            <span>إلى:</span>
                                            <input type="text" id="masterEndDate" class="border border-gray-300 rounded px-1 py-0.5" title="تاريخ النهاية" placeholder="yyyy/mm/dd" style="width: 95px; color: #2563eb; font-weight: 500; font-size: 11px;">
                                        </div>
                                    </th>
                                    <th class="border p-2 text-center" style="width: 60px;">عدد أيام الإيفاد</th>
                                    <th class="border p-2 text-center" style="width: 80px;">مبلغ ايفاد اليوم الواحد</th>
                                    <th class="border p-2 text-center">المبيت</th>
                                    <th class="border p-2 text-center">الوصولات</th>
                                    <th class="border p-2 text-center">المجموع الكلي</th>
                                    <th class="border p-2 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <span>50%</span>
                                            <input type="checkbox" id="checkAllHalf" class="w-4 h-4 cursor-pointer" title="تأشير الكل">
                                        </div>
                                    </th>
                                    <th class="border p-2 w-32 text-center">ملاحظات</th>
                                    <th class="border p-2 text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white text-right"></tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="cities_source" style="display: none;">
        @foreach(\App\Models\Governorate::with('cities')->get() as $gov)
            @foreach($gov->cities as $city)
                <option value="{{ $city->id }}" data-price="{{ $city->daily_allowance }}">
                    {{ $gov->name }} - {{ $city->name }}
                </option>
            @endforeach
        @endforeach

        <optgroup label="خارج القطر">
            @php
                $outsideMissions = \App\Models\MissionType::query()
                    ->where('name', 'like', 'خارج القطر/%')
                    ->select('name')
                    ->distinct()
                    ->orderBy('name')
                    ->pluck('name');
            @endphp

            @if($outsideMissions->isNotEmpty())
                @foreach($outsideMissions as $missionName)
                    <option value="{{ $missionName }}" data-type="mission">{{ $missionName }}</option>
                @endforeach
            @else
                <option value="خارج القطر/1" data-type="mission">خارج القطر/1</option>
                <option value="خارج القطر/2" data-type="mission">خارج القطر/2</option>
                <option value="خارج القطر/3" data-type="mission">خارج القطر/3</option>
                <option value="خارج القطر/4" data-type="mission">خارج القطر/4</option>
            @endif
        </optgroup>
    </div>

</x-app-layout>


