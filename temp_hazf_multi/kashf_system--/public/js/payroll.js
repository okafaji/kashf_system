// دالة حساب الأيام (تعمل في الصفحتين)
window.calculateDays = function() {
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
            if (accInput) {
                if (diffDays > 1) {
                    accInput.disabled = false; // تفعيل الحقل
                    accInput.classList.remove('disabled-field');
                } else {
                    accInput.disabled = true; // تعطيل الحقل
                    accInput.value = 0;
                    accInput.classList.add('disabled-field');
                }
            }

            calculateTotal(); // تحديث المجموع فوراً
        }
    }
}

// دالة الحساب الكلي الموحدة
window.calculateTotal = function() {
    const getV = (id) => parseFloat(document.getElementById(id)?.value) || 0;

    const days = getV('days_count');
    const daily = getV('daily_allowance');
    let accomm = getV('accommodation_fee');
    const transport = getV('transportation_fee');
    const receipts = getV('receipts_amount');
    const meals = getV('meals_count');

    // التعامل مع الـ 50% سواء كانت Checkbox أو Select
    const isHalfEl = document.getElementById('is_half_allowance');
    let isHalf = false;
    if (isHalfEl) {
        isHalf = isHalfEl.type === 'checkbox' ? isHalfEl.checked : isHalfEl.value == '1';
    }

    let price = daily;
    if (isHalf) price /= 2;

    if (days <= 1) {
        accomm = 0;
        const accInput = document.getElementById('accommodation_fee');
        if (accInput) {
            accInput.value = 0;
        }
    }

    const base = days * price;
    const nights = days > 1 ? days - 1 : 0;
    const accommodationTotal = nights * accomm;
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
                calculateTotal();
            }
        });
    }

    // 3. تشغيل الحساب الأولي
    calculateDays();
    calculateTotal();
});

const employeeSelect = document.getElementById('employee_select');
if (employeeSelect) {
    employeeSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            // ملء الحقول تلقائياً
            document.querySelector('input[name="name"]').value = selected.dataset.name;
            document.querySelector('select[name="department"]').value = selected.dataset.dept;
            document.querySelector('input[name="job_title"]').value = selected.dataset.job;
        }
    });
}
