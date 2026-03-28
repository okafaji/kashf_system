

<x-app-layout>

    <x-slot name="header">
        <div style="height: 80px;"></div> <!-- مساحة فارغة أعلى الصفحة -->
        <div id="floatingToolbar" dir="rtl"
            style="position: fixed; top: 80px; right: 30px; z-index: 1000; background: #fff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 12px 24px; min-width: 350px; max-width: 98vw;">
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mr-2 mb-0">{{ __('إضافة كشف إيفاد جديد') }}</h2>
                <button type="button" onclick="document.getElementById('excel_input').click()" class="bg-green-600 hover:bg-green-700 text-white h-10 w-36 rounded shadow flex items-center justify-center text-sm">
                    📥 استيراد إكسل
                </button>
                <button type="button" id="excel2_btn" class="bg-green-600 hover:bg-green-700 text-white h-10 w-36 rounded shadow flex items-center justify-center text-sm ml-2">
                    📥 استيراد من اكسل2
                </button>
                <input type="file" id="excel2_input" class="hidden" accept=".xlsx, .xls">
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var btn2 = document.getElementById('excel2_btn');
                        if (btn2) {
                            btn2.addEventListener('click', function() {
                                console.log('🟢 فتح نافذة اختيار ملف Excel2');
                                document.getElementById('excel2_input').click();
                            });
                        }
                    });
                </script>
                <input type="file" id="excel_input" class="hidden" accept=".xlsx, .xls">
                <button type="button" onclick="clearAllRows()" class="bg-red-600 hover:bg-red-700 text-white h-10 w-36 rounded text-sm font-bold shadow-sm">
                    🗑️ حذف الكل
                </button>
                <button type="submit" id="submitBtn" form="mainPayrollForm" class="bg-blue-600 hover:bg-blue-700 text-white font-bold h-10 w-44 rounded shadow">
                    حفظ وترحيل البيانات
                </button>
            </div>
            <div style="margin-top: 10px; text-align: right;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-1">ابحث عن المنتسب:</label>
                    <span id="employeeCountLabel" style="font-size: 13px; color: #2563eb; font-weight: bold; background: #f3f4f6; border-radius: 6px; padding: 2px 10px;">(0 منتسب)</span>
                </div>
                <select id="employee_search" style="width: 320px;"></select>
            </div>
        </div>
    </x-slot>

    <script>
        // تحميل مكتبة xlsx.full.min.js بمسار ديناميكي حسب الدومين أو الآيبي
        var sheetjsScript = document.createElement('script');
        sheetjsScript.src = window.location.protocol + '//' + window.location.host + '/js/xlsx.full.min.js';
        sheetjsScript.onload = function() {
            if (typeof window.XLSX === 'undefined') {
                alert('مكتبة Excel (SheetJS) غير متوفرة محلياً! يرجى رفع js/xlsx.full.min.js إلى السيرفر.');
            }
        };
        sheetjsScript.onerror = function() {
            alert('تعذر تحميل مكتبة Excel (SheetJS)! تحقق من وجود js/xlsx.full.min.js على السيرفر.');
        };
        document.head.appendChild(sheetjsScript);
    </script>
    <!-- flatpickr datepicker العصري -->
    <link rel="stylesheet" href="/js/flatpickr.min.css">
        <style>
            input[type="text"][placeholder*="yyyy/mm/dd"] {
                cursor: pointer;
            }
        </style>
    <script src="/js/flatpickr.min.js"></script>
    <script src="/js/ar.js"></script>
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

    <div class="py-6" style="margin-top: 100px;">
        <div class="max-w-full mx-auto px-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">

                @if(session('error'))
                    <div class="bg-red-100 p-3 rounded mb-4 text-right" dir="rtl">{{ session('error') }}</div>
                @endif

                <form id="mainPayrollForm" action="{{ route('payrolls.store_multiple') }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <div style="display: flex; flex-direction: row-reverse; align-items: flex-start;">
                            <div id="multiDeleteBar" style="display:none; margin-bottom: 10px; margin-right: 8px;">
                                <button type="button" id="multiDeleteBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow text-lg">
                                    الحذف المتعدد (<span id="multiDeleteCount">0</span>)
                                </button>
                            </div>
                            <div style="flex: 1;"></div>
                        </div>
                        <table class="min-w-full border-collapse border border-gray-200" id="payrollTable" dir="rtl">
                            <thead class="bg-gray-800 text-white" style="font-size: 13px;">
                                <tr style="vertical-align: middle;">
                                    <th class="border p-2 text-center">الاسم والقسم</th>
                                    <th class="border p-2 text-center">العنوان الوظيفي</th>
                                    <th class="border p-2 text-center" style="width: 140px;">
                                        جهة الايفاد
                                        <div style="margin-top: 4px;">
                                            <select id="masterGovernorateSelect" class="border border-gray-300 rounded px-1 py-0.5 text-center" style="width: 120px; font-size: 12px; color: #222; background: #fff;">
                                                <option value="">اختر المحافظة</option>
                                                <!-- سيتم ملء الخيارات ديناميكيًا -->
                                            </select>
                                        </div>
                                    </th>
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
                                    <th class="border p-2 w-32 text-center">ملاحظات</th>
                                    <th class="border p-2 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <span>50%</span>
                                            <input type="checkbox" id="checkAllHalf" class="w-4 h-4 cursor-pointer" title="تأشير الكل">
                                        </div>
                                    </th>
                                    <th class="border p-2 text-center">
                                        <div class="flex flex-col items-center justify-center gap-1">
                                            <div id="multiDeleteBar" style="display:none; margin-bottom: 2px;">
                                                <button type="button" id="multiDeleteBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded shadow text-sm mb-1">
                                                    الحذف المتعدد (<span id="multiDeleteCount">0</span>)
                                                </button>
                                            </div>
                                            <span>حذف</span>
                                            <input type="checkbox" id="checkAllDeleteMulti" class="w-4 h-4 mt-1" title="تحديد الكل للحذف المتعدد">
                                        </div>
                                    </th>
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


