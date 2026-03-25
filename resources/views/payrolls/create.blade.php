

<x-app-layout>

    <x-slot name="header">
        <div style="height: 80px;"></div> <!-- مساحة فارغة أعلى الصفحة -->
        <div id="floatingToolbar" dir="rtl"
            style="position: fixed; top: 80px; right: 30px; z-index: 1000; background: #fff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 12px 24px; min-width: 350px; max-width: 98vw;">
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mr-2 mb-0">
                    {{ __('إضافة كشف إيفاد جديد') }}
                    <span id="employeeCountLabel" style="font-size:16px; color:#2563eb; font-weight:600; margin-right:10px;">
                        (عدد المنتسبين: <span id="employeeCount">0</span>)
                    </span>
                </h2>
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
            <div style="margin-top: 10px; text-align: right;">
                <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-1">ابحث عن المنتسب:</label>
                <select id="employee_search" style="width: 320px;"></select>
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
    // تحديث عداد المنتسبين بجانب العنوان
    function updateEmployeeCountLabel() {
        var count = document.querySelectorAll('#payrollTable tbody tr').length;
        document.getElementById('employeeCount').textContent = count;
    }
    // تحديث العداد عند أي تغيير في الجدول
    document.addEventListener('DOMContentLoaded', function() {
        updateEmployeeCountLabel();
        // مراقبة التغييرات في tbody
        var tbody = document.querySelector('#payrollTable tbody');
        if (tbody) {
            var observer = new MutationObserver(function() {
                updateEmployeeCountLabel();
            });
            observer.observe(tbody, { childList: true, subtree: false });
        }
    });
    // تحديث العداد عند إضافة موظف عبر الجافاسكريبت (لضمان التوافق)
    window.updateEmployeeCountLabel = updateEmployeeCountLabel;
    </script>

    <div class="py-6" style="margin-top: 100px;">
        <div class="max-w-full mx-auto px-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">

                @if(session('error'))
                    <div class="bg-red-100 p-3 rounded mb-4 text-right" dir="rtl">{{ session('error') }}</div>
                @endif

                <form id="mainPayrollForm" action="{{ route('payrolls.store_multiple') }}" method="POST">
                    @csrf
                    <div class="flex gap-2 mb-3" style="justify-content: flex-end;">
                        <button type="button" id="multiDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white h-10 w-40 rounded text-sm font-bold shadow-sm flex items-center justify-center" style="display: none;">
                            🗑️ حذف متعدد (<span id="multiDeleteCount">0</span>)
                        </button>
                    </div>
                    <div class="overflow-x-auto">
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
                            </select>
                        </div>
                    </th>

<script>
// ملء قائمة المحافظات في رأس العمود بنفس الخيارات من #cities_source وتعميم الاختيار
document.addEventListener('DOMContentLoaded', function() {
    var masterGov = document.getElementById('masterGovernorateSelect');
    var citiesSource = document.getElementById('cities_source');
    if (masterGov && citiesSource) {
        masterGov.innerHTML = '<option value="">اختر المحافظة</option>' + citiesSource.innerHTML;
    }
    if (masterGov) {
        masterGov.addEventListener('change', function() {
            var value = this.value;
            // تعميم الاختيار على كل قوائم الوجهة في الصفوف
            document.querySelectorAll('select.js-city-id').forEach(function(sel) {
                sel.value = value;
                sel.dispatchEvent(new Event('change'));
            });
        });
    }
});
</script>
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
                                    <th class="border p-2 text-center">
                                        <input type="checkbox" id="checkAllDeleteMulti" class="w-4 h-4 cursor-pointer" title="تأشير الكل للحذف المتعدد">
                                        <div style="font-size:10px; color:#888;">حذف</div>
                                    </th>
                                    <script>
                                    // منطق تأشير كل checkboxes الحذف المتعدد من رأس الجدول
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var checkAll = document.getElementById('checkAllDeleteMulti');
                                        if (checkAll) {
                                            checkAll.addEventListener('change', function() {
                                                var checked = this.checked;
                                                var allCheckboxes = document.querySelectorAll('.js-delete-row-multi');
                                                allCheckboxes.forEach(function(cb) {
                                                    cb.checked = checked;
                                                });
                                                // تحديث عداد الحذف المتعدد وزر الحذف المتعدد مباشرة
                                                var multiDeleteCount = document.getElementById('multiDeleteCount');
                                                if (multiDeleteCount) {
                                                    multiDeleteCount.textContent = checked ? allCheckboxes.length : 0;
                                                }
                                                // إظهار أو إخفاء زر الحذف المتعدد مباشرة
                                                var multiDeleteBtn = document.getElementById('multiDeleteBtn');
                                                if (multiDeleteBtn) {
                                                    if (checked && allCheckboxes.length > 0) {
                                                        multiDeleteBtn.style.display = '';
                                                    } else {
                                                        multiDeleteBtn.style.display = 'none';
                                                    }
                                                }
                                            });
                                        }
                                        // تحديث حالة checkbox الرئيسي عند تغيير أي صف
                                        document.addEventListener('change', function(e) {
                                            if (e.target.classList && e.target.classList.contains('js-delete-row-multi')) {
                                                var all = document.querySelectorAll('.js-delete-row-multi');
                                                var checked = document.querySelectorAll('.js-delete-row-multi:checked');
                                                if (checkAll) {
                                                    if (checked.length === 0) {
                                                        checkAll.checked = false;
                                                        checkAll.indeterminate = false;
                                                    } else if (checked.length === all.length) {
                                                        checkAll.checked = true;
                                                        checkAll.indeterminate = false;
                                                    } else {
                                                        checkAll.checked = false;
                                                        checkAll.indeterminate = true;
                                                    }
                                                }
                                            }
                                        });
                                    });
                                    </script>
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


