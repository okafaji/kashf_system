// دالة حساب الأيام (تعمل في الصفحتين)
const payrollSharedCalculateDays = function() {
    const start = document.getElementById('start_date')?.value;
    const end = document.getElementById('end_date')?.value;

    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        const diffTime = endDate - startDate;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        const daysInput = document.getElementById('days_count');
        if (daysInput) {
            daysInput.value = diffDays > 0 ? diffDays : 0;

            // تفعيل/تعطيل حقل أجور المبيت حسب عدد الأيام
            const accInput = document.getElementById('accommodation_fee');
            const accommodationHint = document.getElementById('accommodationHint');

            if (accInput) {
                if (diffDays > 1) {
                    // تفعيل الحقل عند الأيام > 1
                    accInput.required = true;
                    accInput.classList.add('border-danger');
                    accInput.style.borderColor = '#dc3545';

                    // إظهار النص التوضيحي
                    if (accommodationHint) {
                        accommodationHint.style.display = 'block';
                        accommodationHint.classList.add('text-danger');
                    }
                } else {
                    // تعطيل الحقل عند يوم واحد
                    accInput.required = false;
                    accInput.value = 0;
                    accInput.classList.remove('border-danger');
                    accInput.style.borderColor = '';

                    // إخفاء النص التوضيحي
                    if (accommodationHint) {
                        accommodationHint.style.display = 'none';
                        accommodationHint.classList.remove('text-danger');
                    }
                }
            }

            if (typeof window.calculateTotal === 'function') {
                window.calculateTotal();
            }
        }
    }
}

// دالة الحساب الكلي الموحدة
const payrollSharedCalculateTotal = function() {
    const getV = (id) => parseFloat(document.getElementById(id)?.value) || 0;

    const days = getV('days_count');
    const daily = getV('daily_allowance');
    const transport = getV('transportation_fee');
    const receipts = getV('receipts_amount');
    const meals = getV('meals_count');

    // المبيت ثابت 10,000 دينار عراقي
    const FIXED_ACCOMMODATION_FEE = 10000;

    // التعامل مع الـ 50% سواء كانت Checkbox أو Select
    const isHalfEl = document.getElementById('is_half_allowance');
    let isHalf = false;
    if (isHalfEl) {
        isHalf = isHalfEl.type === 'checkbox' ? isHalfEl.checked : isHalfEl.value == '1';
    }

    let price = daily;
    let accommodationFee = FIXED_ACCOMMODATION_FEE;

    // تطبيق الـ 50% على البدل اليومي والمبيت معاً
    if (isHalf) {
        price /= 2;
        accommodationFee = FIXED_ACCOMMODATION_FEE * 0.5;
    }

    // تحديث حقل المبيت بالقيمة الثابتة
    const accInput = document.getElementById('accommodation_fee');
    if (accInput) {
        if (days <= 1) {
            accInput.value = 0;
            accommodationFee = 0;
        } else {
            accInput.value = accommodationFee;
        }
    }

    const base = days * price;
    const nights = days > 1 ? days - 1 : 0;
    const accommodationTotal = nights * accommodationFee;
    const mealsDeduction = meals * (price * 0.10);
    const total = base + accommodationTotal + transport + receipts - mealsDeduction;

    const totalInput = document.getElementById('total_amount');
    if (totalInput) {
        totalInput.value = Math.round(total);
    }
}

// دالة جلب المدن
function loadCities(govId) {
    const citySelect = document.getElementById('city_id');
    if (!citySelect || !govId) return;

    const currentCity = citySelect.getAttribute('data-selected');
    citySelect.innerHTML = '<option value="">جاري التحميل...</option>';

    fetch('/api/cities?governorate_id=' + govId)
        .then(res => res.json())
        .then(data => {
            citySelect.innerHTML = '<option value="">مركز المحافظة / أخرى</option>';
            data.forEach(city => {
                const selected = (city.name == currentCity) ? 'selected' : '';
                citySelect.innerHTML += `<option value="${city.name}" data-price="${city.daily_allowance}" ${selected}>${city.name}</option>`;
            });
        }).catch(err => console.error("Error fetching cities:", err));
}

// ربط الأحداث عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // 1. مراقبة تغيير المحافظة
    const govEl = document.getElementById('governorate_id');
    if (govEl) {
        govEl.addEventListener('change', (e) => loadCities(e.target.value));
        // إذا كان هناك قيمة مختارة أصلاً (في التعديل)
        if (govEl.value) loadCities(govEl.value);
    }

    // 2. مراقبة تغيير المدينة لتحديث السعر اليومي
    const cityEl = document.getElementById('city_id');
    if (cityEl) {
        cityEl.addEventListener('change', function() {
            const price = this.options[this.selectedIndex]?.getAttribute('data-price');
            if (price) {
                document.getElementById('daily_allowance').value = price;
                if (typeof window.calculateTotal === 'function') {
                    window.calculateTotal();
                }
            }
        });
    }

    // 3. تشغيل الحساب الأولي
    if (typeof window.calculateDays === 'function') {
        window.calculateDays();
    }
    if (typeof window.calculateTotal === 'function') {
        window.calculateTotal();
    }
});

if (typeof window.calculateDays !== 'function') {
    window.calculateDays = payrollSharedCalculateDays;
}

if (typeof window.calculateTotal !== 'function') {
    window.calculateTotal = payrollSharedCalculateTotal;
}

const employeeSelect = document.getElementById('employee_select');
if (employeeSelect) {
    employeeSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (!selected || !selected.value || !selected.dataset || !selected.dataset.name) {
            return;
        }

        const nameInput = document.querySelector('input[name="name"]');
        const departmentSelect = document.querySelector('select[name="department"]');
        const jobTitleInput = document.querySelector('input[name="job_title"]');

        if (nameInput) {
            nameInput.value = selected.dataset.name || '';
        }
        if (departmentSelect) {
            departmentSelect.value = selected.dataset.dept || '';
        }
        if (jobTitleInput) {
            jobTitleInput.value = selected.dataset.job || selected.dataset.jobTitle || '';
        }
    });
}
