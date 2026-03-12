function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function normalizeOutsideMissionName(value) {
    if (!value) {
        return '';
    }

    const missionOnly = String(value).split(' - ')[0].trim();
    const match = missionOnly.match(/^خارج\s+القطر\s*[\/\s]?\s*(\d+)$/);
    if (match) {
        return `خارج القطر/${match[1]}`;
    }

    return missionOnly;
}

function inferResponsibilityLevelFromJobTitle(jobTitle) {
    const text = (jobTitle || '').trim();
    if (!text) {
        return '';
    }

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

function calculateDaysFromModal() {
    const startInput = document.querySelector('#addEmployeeForm input[name="start_date"]');
    const endInput = document.querySelector('#addEmployeeForm input[name="end_date"]');
    const daysInput = document.getElementById('daysCount');
    const accommodationInput = document.getElementById('accommodation_fee_input') || document.querySelector('#addEmployeeForm input[name="accommodation_fee"]');

    if (!startInput || !endInput || !daysInput) {
        return;
    }

    const start = startInput.value;
    const end = endInput.value;

    if (!start || !end) {
        daysInput.value = '';
        return;
    }

    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffTime = endDate - startDate;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

    const destinationSelect = document.getElementById('destinationSelect');
    const selectedOption = destinationSelect?.options[destinationSelect.selectedIndex];
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';

    daysInput.value = diffDays > 0 ? diffDays : 0;

    // إدارة حقل المبيت حسب نوع الإيفاد (تلقائي وثابت)
    if (accommodationInput) {
        if (isOutsideCountry) {
            accommodationInput.value = 0;
        } else if (diffDays > 1) {
            accommodationInput.value = 10000;
        } else {
            accommodationInput.value = 0;
        }
    }

    calculateTotalFromModal();
}

function calculateTotalFromModal() {
    const daysInput = document.getElementById('daysCount');
    const dailyInput = document.querySelector('#addEmployeeForm input[name="daily_allowance"]');
    const accommodationInput = document.getElementById('accommodation_fee_input') || document.querySelector('#addEmployeeForm input[name="accommodation_fee"]');
    const receiptsInput = document.querySelector('#addEmployeeForm input[name="receipts_amount"]');
    const halfSelect = document.querySelector('#addEmployeeForm select[name="is_half_allowance"]');
    const totalInput = document.getElementById('totalAmount');
    const destinationSelect = document.getElementById('destinationSelect');
    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';

    if (!daysInput || !dailyInput || !totalInput) {
        return;
    }

    const days = Number(daysInput.value || 0);
    const dailyAllowance = Number(dailyInput.value || 0);
    const receiptsAmount = Number(receiptsInput ? receiptsInput.value : 0);
    const isHalf = halfSelect && halfSelect.value === '1';

    let accommodationFee = 0;

    if (isOutsideCountry) {
        // خارج القطر: بدون مبيت
        accommodationFee = 0;
    } else {
        // مدينة عادية: المبيت ثابت 10,000 ولا يطبق عليه 50%
        accommodationFee = 10000;
    }

    // تحديث حقل المبيت
    if (accommodationInput) {
        if (days <= 1 && !isOutsideCountry) {
            accommodationInput.value = 0;
            accommodationFee = 0;
        } else if (isOutsideCountry) {
            accommodationInput.value = 0;
        } else {
            accommodationInput.value = accommodationFee;
        }
    }

    const nights = days > 1 ? days - 1 : 0;

    // تطبيق نصف الاستحقاق على اليومية فقط (للمدينة وخارج القطر)
    const effectiveDaily = isHalf ? (dailyAllowance / 2) : dailyAllowance;

    // حساب المجموع:
    // - خارج القطر: (أيام × سعر اليوم الفعلي) + وصولات
    // - مدينة: (أيام × سعر اليوم الفعلي) + (ليالي × مبيت) + وصولات
    let baseAmount = days * effectiveDaily;
    let totalAmount;

    if (isOutsideCountry) {
        totalAmount = baseAmount + receiptsAmount;
    } else {
        totalAmount = baseAmount + (nights * accommodationFee) + receiptsAmount;
    }

    const liveDays = document.getElementById('modalLiveDays');
    const liveDaily = document.getElementById('modalLiveDaily');
    const liveTotal = document.getElementById('modalLiveTotal');
    if (liveDays) liveDays.textContent = String(days || 0);
    if (liveDaily) liveDaily.textContent = Number.isFinite(effectiveDaily) ? effectiveDaily.toLocaleString('ar-IQ') : '0';
    if (liveTotal) liveTotal.textContent = Number.isFinite(totalAmount) ? totalAmount.toLocaleString('ar-IQ') : '0';

    totalInput.value = Number.isFinite(totalAmount) ? totalAmount.toFixed(2) : '';
}

function openAddEmployeeModal() {
    const modal = document.getElementById('addEmployeeModal');
    if (modal) {
        modal.classList.remove('hidden');
    }

    // 🔥 احفظ البيانات الأصلية من الكشف قبل البدء
    const form = document.getElementById('addEmployeeForm');
    if (form) {
        const destination = form.querySelector('select[name="destination"]')?.value;
        const orderNo = form.querySelector('input[name="admin_order_no"]')?.value;
        const orderDate = form.querySelector('input[name="admin_order_date"]')?.value;
        const startDate = form.querySelector('input[name="start_date"]')?.value;
        const endDate = form.querySelector('input[name="end_date"]')?.value;
        const daily = form.querySelector('input[name="daily_allowance"]')?.value;

        // احفظها كـ data attributes للاستعادة لاحقاً
        form.setAttribute('data-original-destination', destination);
        form.setAttribute('data-original-order-no', orderNo);
        form.setAttribute('data-original-order-date', orderDate);
        form.setAttribute('data-original-start-date', startDate);
        form.setAttribute('data-original-end-date', endDate);
        form.setAttribute('data-original-daily', daily);

        console.log('💾 تم حفظ البيانات الأصلية:', {
            destination,
            orderNo,
            orderDate,
            startDate,
            endDate,
            daily
        });
    }

    // تهيئة Select2 في كل مرة يتم فتح Modal
    setTimeout(() => {
        initializeEmployeeSelect();
    }, 100);

    updateDestinationFieldsModal();
    updateAllowanceFromDestination();
    calculateDaysFromModal();
    calculateTotalFromModal();
}

function updateAllowanceFromDestination() {
    const destinationSelect = document.querySelector('#addEmployeeForm select[name="destination"]');
    const dailyInput = document.querySelector('#addEmployeeForm input[name="daily_allowance"]');
    if (!destinationSelect || !dailyInput) {
        return;
    }

    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';

    if (isOutsideCountry) {
        calculateDailyModal();
        return;
    }

    const price = selectedOption ? selectedOption.getAttribute('data-price') : null;
    if (price !== null && price !== '') {
        dailyInput.value = price;
    }

    calculateTotalFromModal();
}

// تحديث عرض حقول المستوى الوظيفي بناءً على اختيار الوجهة
function updateDestinationFieldsModal() {
    const destinationSelect = document.getElementById('destinationSelect');
    if (!destinationSelect) {
        return;
    }

    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';
    const responsibilityDiv = document.getElementById('responsibilityLevelDivModal');
    const responsibilitySelect = document.getElementById('responsibility_level_modal');
    const accommodationInput = document.getElementById('accommodation_fee_input');
    const accommodationSection = accommodationInput ? accommodationInput.closest('div') : null;

    if (isOutsideCountry) {
        if (responsibilityDiv) responsibilityDiv.style.display = 'block';
        if (accommodationInput) {
            accommodationInput.value = 0;
        }
        if (accommodationSection) {
            accommodationSection.style.display = 'none';
        }

        if (responsibilitySelect && !responsibilitySelect.value) {
            const jobTitleText = document.getElementById('job_title_display')?.value || '';
            const inferredLevel = inferResponsibilityLevelFromJobTitle(jobTitleText);
            if (inferredLevel) {
                responsibilitySelect.value = inferredLevel;
            }
        }
    } else {
        if (responsibilityDiv) responsibilityDiv.style.display = 'none';
        if (responsibilitySelect) responsibilitySelect.value = '';
        if (accommodationSection) {
            accommodationSection.style.display = '';
        }
    }

    calculateDaysFromModal();
    calculateDailyModal();
}

// حساب المبلغ اليومي بناءً على نوع الإيفاد
function calculateDailyModal() {
    const destinationSelect = document.getElementById('destinationSelect');
    if (!destinationSelect) {
        return;
    }

    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
    const selectedValue = destinationSelect.value;
    const normalizedSelectedValue = normalizeOutsideMissionName(selectedValue);
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';
    const dailyInput = document.getElementById('dailyAllowanceInput');

    let dailyPrice = 0;

    if (isOutsideCountry) {
        const responsibilityLevel = document.getElementById('responsibility_level_modal')?.value || '';
        if (responsibilityLevel && window.missionRates) {
            const missionRate = window.missionRates.find(item =>
                normalizeOutsideMissionName(item.name) === normalizedSelectedValue && item.responsibility_level === responsibilityLevel
            );
            dailyPrice = missionRate ? parseFloat(missionRate.daily_rate) : 0;
        }
    } else {
        const price = selectedOption ? selectedOption.getAttribute('data-price') : '';
        dailyPrice = price ? parseFloat(price) : 0;
    }

    if (dailyInput) dailyInput.value = dailyPrice;
    calculateTotalFromModal();
}

function closeAddEmployeeModal() {
    const modal = document.getElementById('addEmployeeModal');
    if (modal) {
        modal.classList.add('hidden');
    }

    // تنظيف Select2
    const $ = window.$;
    if ($ && $.fn && $.fn.select2) {
        const $select = $('#employee_select');
        if ($select.length > 0 && $select.data('select2')) {
            $select.select2('close').val(null).trigger('change');
        }
    }

    // 🔥 حذف فقط حقول الموظف والحسابات، احفظ بيانات الكشف الأصلية
    const form = document.getElementById('addEmployeeForm');
    if (form) {
        // احفظ البيانات الأصلية من الكشف قبل الحذف
        const originalDestination = form.getAttribute('data-original-destination');
        const originalOrderNo = form.getAttribute('data-original-order-no');
        const originalOrderDate = form.getAttribute('data-original-order-date');
        const originalStartDate = form.getAttribute('data-original-start-date');
        const originalEndDate = form.getAttribute('data-original-end-date');
        const originalDaily = form.getAttribute('data-original-daily');

        // حذف فقط حقول الموظف
        const employeeIdInput = document.getElementById('employee_id_input');
        if (employeeIdInput) employeeIdInput.value = '';

        const nameField = document.getElementById('name_display');
        if (nameField) nameField.value = '';

        const deptField = document.getElementById('department_display');
        if (deptField) deptField.value = '';

        const jobField = document.getElementById('job_title_display');
        if (jobField) jobField.value = '';

        // استعد البيانات الأصلية للكشف
        if (originalDestination) document.querySelector('select[name="destination"]').value = originalDestination;
        if (originalOrderNo) document.querySelector('input[name="admin_order_no"]').value = originalOrderNo;
        if (originalOrderDate) document.querySelector('input[name="admin_order_date"]').value = originalOrderDate;
        if (originalStartDate) document.querySelector('input[name="start_date"]').value = originalStartDate;
        if (originalEndDate) document.querySelector('input[name="end_date"]').value = originalEndDate;
        if (originalDaily) document.querySelector('input[name="daily_allowance"]').value = originalDaily;

        // أعد حساب الأيام والمجموع
        calculateDaysFromModal();
        calculateTotalFromModal();
    }

    const errors = document.getElementById('addEmployeeErrors');
    if (errors) {
        errors.classList.add('hidden');
    }

    const errorsList = document.getElementById('errorsList');
    if (errorsList) {
        errorsList.innerHTML = '';
    }

    const daysInput = document.getElementById('daysCount');
    if (daysInput) {
        daysInput.value = '';
    }

    const totalInput = document.getElementById('totalAmount');
    if (totalInput) {
        totalInput.value = '';
    }
}

function initializeEmployeeSelect() {
    const $ = window.$;
    if (!$ || !$.fn || !$.fn.select2) {
        return;
    }

    const $select = $('#employee_select');
    if ($select.length === 0) {
        return;
    }

    // تدمير Select2 السابق إن وجد
    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.select2({
        placeholder: 'ابحث عن موظف بالاسم أو الرقم الوظيفي...',
        allowClear: true,
        width: '100%',
        dir: 'rtl',
        dropdownParent: $('#addEmployeeModal'),
        minimumInputLength: 2,
        ajax: {
            url: '/api/employees/search',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                const rawResults = data.results || data || [];
                const normalizedResults = rawResults.map((item) => ({
                    ...item,
                    job_title: item.job_title || item.job || ''
                }));

                return {
                    results: normalizedResults,
                    pagination: data.pagination || { more: false }
                };
            }
        }
    }).on('select2:select', function(e) {
        const employee = e.params.data || {};
        console.log('👤 تم اختيار موظف:', employee);

        const hiddenId = document.getElementById('employee_id_input');
        if (hiddenId) {
            hiddenId.value = employee.id || '';
        }

        const nameField = document.getElementById('name_display');
        if (nameField) {
            nameField.value = employee.name || employee.text || '';
        }

        const deptField = document.getElementById('department_display');
        if (deptField) {
            deptField.value = employee.dept || '';
        }

        const jobField = document.getElementById('job_title_display');
        if (jobField) {
            jobField.value = employee.job_title || '';
        }

        // fallback: جلب بيانات الموظف مباشرة لضمان ظهور العنوان الوظيفي دائماً
        fetch(`/api/employees/${encodeURIComponent(employee.id || '')}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then((response) => response.ok ? response.json() : null)
            .then((payload) => {
                if (!payload) {
                    return;
                }

                if (nameField && payload.name) {
                    nameField.value = payload.name;
                }
                if (deptField && payload.department) {
                    deptField.value = payload.department;
                }
                if (jobField && payload.job_title) {
                    jobField.value = payload.job_title;
                }

                const destinationSelectAfterFetch = document.getElementById('destinationSelect');
                const selectedDestinationAfterFetch = destinationSelectAfterFetch?.options[destinationSelectAfterFetch.selectedIndex];
                const isOutsideCountryAfterFetch = selectedDestinationAfterFetch && selectedDestinationAfterFetch.getAttribute('data-type') === 'mission';
                if (isOutsideCountryAfterFetch) {
                    const responsibilitySelect = document.getElementById('responsibility_level_modal');
                    const inferredLevel = inferResponsibilityLevelFromJobTitle(payload.job_title || '');
                    if (responsibilitySelect && inferredLevel) {
                        responsibilitySelect.value = inferredLevel;
                        calculateDailyModal();
                    }
                }
            })
            .catch(() => {
                // fallback صامت بدون كسر الـ UX
            });

        const destinationSelect = document.getElementById('destinationSelect');
        const selectedDestination = destinationSelect?.options[destinationSelect.selectedIndex];
        const isOutsideCountry = selectedDestination && selectedDestination.getAttribute('data-type') === 'mission';
        if (isOutsideCountry) {
            const responsibilitySelect = document.getElementById('responsibility_level_modal');
            const inferredLevel = inferResponsibilityLevelFromJobTitle(employee.job_title || '');
            if (responsibilitySelect && inferredLevel) {
                responsibilitySelect.value = inferredLevel;
            }
        }

        // 🔥 تأكد من أن بيانات الكشف الأصلية محفوظة ولم تُحذف
        const form = document.getElementById('addEmployeeForm');
        if (form) {
            const orderNo = form.getAttribute('data-original-order-no');
            const orderDate = form.getAttribute('data-original-order-date');
            const startDate = form.getAttribute('data-original-start-date');
            const endDate = form.getAttribute('data-original-end-date');

            // لا نعيد تعيين الوجهة هنا حتى لا نكسر اختيار المستخدم (خارج القطر/مدينة)
            if (orderNo) form.querySelector('input[name="admin_order_no"]').value = orderNo;
            if (orderDate) form.querySelector('input[name="admin_order_date"]').value = orderDate;
            if (startDate) form.querySelector('input[name="start_date"]').value = startDate;
            if (endDate) form.querySelector('input[name="end_date"]').value = endDate;

            console.log('✅ تم إعادة تعيين بيانات الكشف الأصلية للموظف الجديد');

            // أعد حساب الأيام والمجموع
            calculateDaysFromModal();
            updateDestinationFieldsModal();
            updateAllowanceFromDestination();
            calculateTotalFromModal();
        }
    });
}

function initializeAddEmployeeHandlers() {
    const form = document.getElementById('addEmployeeForm');
    if (!form) {
        return;
    }

    const destinationSelect = form.querySelector('select[name="destination"]');
    const startInput = form.querySelector('input[name="start_date"]');
    const endInput = form.querySelector('input[name="end_date"]');
    const dailyInput = form.querySelector('input[name="daily_allowance"]');
    const accommodationInput = document.getElementById('accommodation_fee_input') || form.querySelector('input[name="accommodation_fee"]');
    const receiptsInput = form.querySelector('input[name="receipts_amount"]');
    const halfSelect = form.querySelector('select[name="is_half_allowance"]');

    if (destinationSelect) {
        destinationSelect.addEventListener('change', function() {
            updateDestinationFieldsModal();
            updateAllowanceFromDestination();
        });
    }

    if (startInput) {
        startInput.addEventListener('change', calculateDaysFromModal);
    }

    if (endInput) {
        endInput.addEventListener('change', calculateDaysFromModal);
    }

    const recalc = () => calculateTotalFromModal();
    if (dailyInput) {
        dailyInput.addEventListener('input', recalc);
    }
    if (accommodationInput) {
        accommodationInput.addEventListener('input', recalc);
    }
    if (receiptsInput) {
        receiptsInput.addEventListener('input', recalc);
    }
    if (halfSelect) {
        halfSelect.addEventListener('change', recalc);
    }
}

function validateAndSubmitForm(event) {
    event.preventDefault();

    const form = document.getElementById('addEmployeeForm');
    if (!form) {
        return;
    }

    // Validation errors
    const errors = [];

    // التحقق من اختيار الموظف
    const employeeId = document.getElementById('employee_id_input')?.value;
    if (!employeeId) {
        errors.push('يجب اختيار موظف');
    }

    // التحقق من التواريخ والمبيت
    const startDate = form.querySelector('input[name="start_date"]')?.value;
    const endDate = form.querySelector('input[name="end_date"]')?.value;
    const destinationSelect = document.getElementById('destinationSelect');
    const selectedOption = destinationSelect?.options[destinationSelect.selectedIndex];
    const isOutsideCountry = selectedOption && selectedOption.getAttribute('data-type') === 'mission';
    const responsibilityLevel = document.getElementById('responsibility_level_modal')?.value || '';

    if (!startDate || !endDate) {
        errors.push('يجب تحديد فترة الإيفاد');
    }

    if (isOutsideCountry && !responsibilityLevel) {
        errors.push('يجب اختيار المستوى الوظيفي عند اختيار خارج القطر');
    }

    // إذا كانت هناك أخطاء، عرضها والخروج
    if (errors.length > 0) {
        const errorContainer = document.getElementById('addEmployeeErrors');
        const errorsList = document.getElementById('errorsList');
        if (errorContainer && errorsList) {
            errorsList.innerHTML = '';
            errors.forEach((error) => {
                const li = document.createElement('li');
                li.textContent = error;
                errorsList.appendChild(li);
            });
            errorContainer.classList.remove('hidden');
        }
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
    }

    const formData = new FormData(form);
    const kashfNo = formData.get('kashf_no');
    const url = `/payrolls/${kashfNo}/add-employee`;

    const handleErrors = (message, details) => {
        const errorContainer = document.getElementById('addEmployeeErrors');
        const errorsList = document.getElementById('errorsList');
        if (errorContainer && errorsList) {
            errorsList.innerHTML = '';
            const items = details && Array.isArray(details) ? details : [message];
            items.forEach((item) => {
                const li = document.createElement('li');
                li.textContent = item;
                errorsList.appendChild(li);
            });
            errorContainer.classList.remove('hidden');
        }
    };

    const $ = window.$;
    if ($) {
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            success: function() {
                window.location.reload();
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                handleErrors(response.error || response.message || 'حدث خطأ أثناء الإضافة', response.details);
            },
            complete: function() {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
    }).then(async (response) => {
        if (!response.ok) {
            const payload = await response.json().catch(() => ({}));
            handleErrors(payload.error || payload.message || 'حدث خطأ أثناء الإضافة', payload.details);
            return;
        }
        window.location.reload();
    }).catch(() => {
        handleErrors('حدث خطأ أثناء الإضافة');
    }).finally(() => {
        if (submitButton) {
            submitButton.disabled = false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeEmployeeSelect();
        initializeAddEmployeeHandlers();
    });
} else {
    initializeEmployeeSelect();
    initializeAddEmployeeHandlers();
}

window.initializeEmployeeSelect = initializeEmployeeSelect;
window.openAddEmployeeModal = openAddEmployeeModal;
window.closeAddEmployeeModal = closeAddEmployeeModal;
window.validateAndSubmitForm = validateAndSubmitForm;
window.calculateDaysFromModal = calculateDaysFromModal;
window.calculateTotalFromModal = calculateTotalFromModal;
window.updateAllowanceFromDestination = updateAllowanceFromDestination;
window.updateDestinationFieldsModal = updateDestinationFieldsModal;
window.calculateDailyModal = calculateDailyModal;
