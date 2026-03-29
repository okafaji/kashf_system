
<x-app-layout>

    <x-slot name="header">
        <x-floating-toolbar :title="'تعديل إيفاد منتسب'" :subtitle="'الكشف رقم ' . $payroll->kashf_no . ' - ' . $payroll->name">
        </x-floating-toolbar>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-white rounded-2xl shadow-2xl p-4 md:p-5 border border-slate-200">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-4 text-right rounded-xl">
                    <ul class="mb-0 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/payrolls/{{ $payroll->id }}" method="POST" id="editPayrollForm" onsubmit="return validateEditForm()">
                @csrf
                @method('PUT')

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
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">الاسم</label>
                        <input type="text" name="name" value="{{ $payroll->name }}" required
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">القسم</label>
                        <input type="text" name="department" value="{{ $payroll->department }}" readonly
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">العنوان الوظيفي</label>
                        <input type="text" name="job_title" value="{{ $payroll->job_title }}"
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">جهة الإيفاد</label>
                        <select name="destination" id="city_id"
                                class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                onchange="updateDestinationFields()">
                            <option value="">اختر الوجهة...</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->name }}" data-price="{{ $city->daily_allowance }}" data-type="city" {{ $payroll->destination == $city->name ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                            <optgroup label="خارج القطر">
                                <option value="خارج القطر/1" data-type="mission" {{ (strpos($payroll->destination ?? '', 'خارج القطر 1') === 0 || strpos($payroll->destination ?? '', 'خارج القطر/1') === 0) ? 'selected' : '' }}>خارج القطر/1</option>
                                <option value="خارج القطر/2" data-type="mission" {{ (strpos($payroll->destination ?? '', 'خارج القطر 2') === 0 || strpos($payroll->destination ?? '', 'خارج القطر/2') === 0) ? 'selected' : '' }}>خارج القطر/2</option>
                                <option value="خارج القطر/3" data-type="mission" {{ (strpos($payroll->destination ?? '', 'خارج القطر 3') === 0 || strpos($payroll->destination ?? '', 'خارج القطر/3') === 0) ? 'selected' : '' }}>خارج القطر/3</option>
                                <option value="خارج القطر/4" data-type="mission" {{ (strpos($payroll->destination ?? '', 'خارج القطر 4') === 0 || strpos($payroll->destination ?? '', 'خارج القطر/4') === 0) ? 'selected' : '' }}>خارج القطر/4</option>
                            </optgroup>
                        </select>
                    </div>

                    <div id="responsibilityLevelDiv" style="display: none;" class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">المستوى الوظيفي</label>
                        <select name="responsibility_level" id="responsibility_level"
                                class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                onchange="calculateDaily()">
                            <option value="">اختر المستوى الوظيفي...</option>
                            <option value="منتسب" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'منتسب') !== false ? 'selected' : '' }}>منتسب</option>
                            <option value="مسؤول شعبة" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'مسؤول شعبة') !== false ? 'selected' : '' }}>مسؤول شعبة</option>
                            <option value="مسؤول وجبة" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'مسؤول وجبة') !== false ? 'selected' : '' }}>مسؤول وجبة</option>
                            <option value="معاون" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'معاون') !== false ? 'selected' : '' }}>معاون</option>
                            <option value="رئيس" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'رئيس') !== false ? 'selected' : '' }}>رئيس</option>
                            <option value="عضو" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'عضو') !== false ? 'selected' : '' }}>عضو</option>
                            <option value="مستشار" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'مستشار') !== false ? 'selected' : '' }}>مستشار</option>
                            <option value="نائب أمين عام" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'نائب أمين عام') !== false ? 'selected' : '' }}>نائب أمين عام</option>
                            <option value="أمين عام" {{ $payroll->mission_type_id && strpos($payroll->destination ?? '', 'أمين عام') !== false ? 'selected' : '' }}>أمين عام</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">رقم الأمر الإداري</label>
                        <input type="text" name="admin_order_no" value="{{ $payroll->admin_order_no }}" required
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">تاريخ الأمر</label>
                        <input type="text" name="admin_order_date" value="{{ $payroll->admin_order_date }}" placeholder="yyyy/mm/dd" required
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">من تاريخ</label>
                        <input type="text" name="start_date" id="start_date" value="{{ $payroll->start_date }}" placeholder="yyyy/mm/dd" required
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                               onchange="calculateDays()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">إلى تاريخ</label>
                        <input type="text" name="end_date" id="end_date" value="{{ $payroll->end_date }}" placeholder="yyyy/mm/dd" required
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                               onchange="calculateDays()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">عدد الأيام</label>
                        <input type="number" id="days_count" name="days_count" value="{{ $payroll->days_count }}" readonly
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">المبلغ اليومي *</label>
                        <input type="number" id="daily_allowance" name="daily_allowance" value="{{ $selectedDaily }}" required step="0.01"
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                               oninput="calculateTotal()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">أجور المبيت (تلقائي)</label>
                        <input type="number" id="accommodation_fee" name="accommodation_fee" step="0.01"
                               value="{{ str_starts_with($payroll->destination, 'خارج القطر') ? 0 : ($payroll->days_count > 1 ? 10000 : 0) }}"
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-500"
                               readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">وصولات أخرى</label>
                        <input type="number" id="receipts_amount" name="receipts_amount" step="0.01"
                               value="{{ $payroll->receipts_amount ?? 0 }}"
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                               oninput="calculateTotal()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">نسبة الإيفاد</label>
                        <select name="is_half_allowance" id="is_half_allowance"
                                class="w-full px-3 py-2.5 border border-amber-200 rounded-xl bg-amber-50 text-amber-800 font-semibold focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                                onchange="calculateTotal()">
                            <option value="0" {{ !$payroll->is_half_allowance ? 'selected' : '' }}>كامل 100%</option>
                            <option value="1" {{ $payroll->is_half_allowance ? 'selected' : '' }}>نصف 50%</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">مبلغ الإيفاد الكلي</label>
                        <input type="number" id="total_amount" name="total_amount" readonly
                               value="{{ $payroll->total_amount }}"
                               class="w-full px-3 py-2.5 border border-emerald-200 rounded-xl bg-emerald-50 text-emerald-800 font-bold">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ request('back') ?? route('payrolls.show', $payroll->kashf_no) }}"
                       class="px-5 py-2.5 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium">
                        إلغاء
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-sm">
                        حفظ المنتسب
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function normalizeOutsideMissionName(value) {
            if (!value) return '';
            const missionOnly = String(value).split(' - ')[0].trim();
            const match = missionOnly.match(/^خارج\s+القطر\s*[\/\s]?\s*(\d+)$/);
            if (match) return `خارج القطر/${match[1]}`;
            return missionOnly;
        }

        function inferResponsibilityLevelFromJobTitle(jobTitle) {
            const text = (jobTitle || '').trim();
            if (!text) return '';
            if (text.includes('أمين عام')) return 'أمين عام';
            if (text.includes('نائب أمين عام')) return 'نائب أمين عام';
            if (text.includes('مستشار')) return 'مستشار';
            if (text.includes('مسؤول شعبة')) return 'مسؤول شعبة';
            if (text.includes('مسؤول وجبة')) return 'مسؤول وجبة';
            if (text.includes('معاون')) return 'معاون';
            if (text.includes('رئيس')) return 'رئيس';
            if (text.includes('عضو')) return 'عضو';
            return 'منتسب';
        }

        function validateEditForm() {
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const startValue = startInput?.value;
            const endValue = endInput?.value;

            if (startValue && endValue) {
                const startDate = new Date(startValue);
                const endDate = new Date(endValue);
                if (!Number.isNaN(startDate.getTime()) && !Number.isNaN(endDate.getTime()) && endDate < startDate) {
                    alert('خطأ: تاريخ البداية لا يمكن أن يكون بعد تاريخ النهاية');
                    endInput.focus();
                    return false;
                }
            }
            return true;
        }

        function updateDestinationFields() {
            const destinationSelect = document.getElementById('city_id');
            if (!destinationSelect) return;

            const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
            const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';
            const responsibilityDiv = document.getElementById('responsibilityLevelDiv');
            const responsibilitySelect = document.getElementById('responsibility_level');
            const accommodationInput = document.getElementById('accommodation_fee');

            if (isOutsideCountry) {
                if (responsibilityDiv) responsibilityDiv.style.display = 'block';
                if (responsibilitySelect && !responsibilitySelect.value) {
                    const jobTitleText = document.querySelector('input[name="job_title"]')?.value || '';
                    const inferredLevel = inferResponsibilityLevelFromJobTitle(jobTitleText);
                    if (inferredLevel) responsibilitySelect.value = inferredLevel;
                }
                if (accommodationInput) accommodationInput.value = 0;
            } else {
                if (responsibilityDiv) responsibilityDiv.style.display = 'none';
                if (responsibilitySelect) responsibilitySelect.value = '';
                const days = Number(document.getElementById('days_count')?.value || 0);
                if (accommodationInput) accommodationInput.value = days > 1 ? 10000 : 0;
            }

            calculateDaily();
        }

        function calculateDaily() {
            const destinationSelect = document.getElementById('city_id');
            if (!destinationSelect) return;

            const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
            const selectedValue = destinationSelect.value;
            const normalizedSelectedValue = normalizeOutsideMissionName(selectedValue);
            const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';

            let dailyPrice = 0;

            if (isOutsideCountry) {
                const responsibilityLevel = document.getElementById('responsibility_level')?.value || '';
                if (responsibilityLevel && window.missionRates) {
                    const missionRate = window.missionRates.find(item =>
                        normalizeOutsideMissionName(item.name) === normalizedSelectedValue &&
                        item.responsibility_level === responsibilityLevel
                    );
                    dailyPrice = missionRate ? parseFloat(missionRate.daily_rate) : 0;
                }
            } else {
                const price = selectedOption ? selectedOption.getAttribute('data-price') : '';
                dailyPrice = price ? parseFloat(price) : 0;
            }

            const dailyInput = document.getElementById('daily_allowance');
            if (dailyInput) dailyInput.value = dailyPrice;
            calculateTotal();
        }

        function calculateDays() {
            const startValue = document.getElementById('start_date')?.value;
            const endValue = document.getElementById('end_date')?.value;
            const daysInput = document.getElementById('days_count');

            if (!startValue || !endValue) {
                if (daysInput) daysInput.value = '';
                return;
            }

            const startDate = new Date(startValue);
            const endDate = new Date(endValue);

            if (endDate < startDate) {
                if (daysInput) daysInput.value = 0;
                return;
            }

            const diffDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            if (daysInput) daysInput.value = diffDays;

            calculateTotal();
        }

        function calculateTotal() {
            const days = Number(document.getElementById('days_count')?.value || 0);
            const dailyAllowance = Number(document.getElementById('daily_allowance')?.value || 0);
            const receiptsAmount = Number(document.getElementById('receipts_amount')?.value || 0);
            const isHalf = document.getElementById('is_half_allowance')?.value === '1';

            const destinationSelect = document.getElementById('city_id');
            const selectedOption = destinationSelect?.options[destinationSelect.selectedIndex];
            const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';

            const effectiveDaily = isHalf ? (dailyAllowance / 2) : dailyAllowance;
            const nights = days > 1 ? days - 1 : 0;

            let accommodationFee = 0;
            if (!isOutsideCountry && days > 1) {
                accommodationFee = 10000;
            }

            const accommodationInput = document.getElementById('accommodation_fee');
            if (accommodationInput) accommodationInput.value = accommodationFee;

            let totalAmount;
            if (isOutsideCountry) {
                totalAmount = (days * effectiveDaily) + receiptsAmount;
            } else {
                totalAmount = (days * effectiveDaily) + (nights * accommodationFee) + receiptsAmount;
            }

            const totalInput = document.getElementById('total_amount');
            if (totalInput) totalInput.value = Number.isFinite(totalAmount) ? totalAmount.toFixed(2) : '0';

            const liveDays = document.getElementById('modalLiveDays');
            const liveDaily = document.getElementById('modalLiveDaily');
            const liveTotal = document.getElementById('modalLiveTotal');
            if (liveDays) liveDays.textContent = String(days || 0);
            if (liveDaily) liveDaily.textContent = Number.isFinite(effectiveDaily) ? effectiveDaily.toLocaleString('ar-IQ') : '0';
            if (liveTotal) liveTotal.textContent = Number.isFinite(totalAmount) ? totalAmount.toLocaleString('ar-IQ') : '0';
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateDestinationFields();
            calculateDays();
            calculateTotal();
        });
    </script>
</x-app-layout>

