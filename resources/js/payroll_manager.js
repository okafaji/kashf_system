// ============ payroll_manager.js - النسخة المصححة ============

// ========== دوال مساعدة للتعامل مع localStorage بـ scope للمستخدم ==========
/**
 * الحصول على مفتاح localStorage مرتبط بـ ID المستخدم الحالي
 * يضمن أن كل مستخدم له مسودته الخاصة
 * @returns {string} المفتاح بصيغة payroll_draft:<userId> أو payroll_draft للسقوط العكسي
 */
function getStorageKey() {
    if (window.currentUserId) {
        return `payroll_draft:${window.currentUserId}`;
    }
    // السقوط العكسي للمفتاح العام إذا لم يكن userId متاحاً
    console.warn('⚠️ currentUserId غير متاح - استخدام المفتاح العام');
    return 'payroll_draft';
}

/**
 * تنظيف المفاتيح القديمة من localStorage
 * إزالة البيانات بـ format قديم إذا كانت موجودة
 */
function cleanupLegacyStorageKeys() {
    try {
        const currentKey = getStorageKey();
        // حذف المفتاح العام القديم إذا كنا نستخدم مفتاح مرتبط بـ user
        if (window.currentUserId && localStorage.getItem('payroll_draft')) {
            console.log('🧹 حذف المفتاح العام القديم (payroll_draft)');
            localStorage.removeItem('payroll_draft');
        }
    } catch (error) {
        console.warn('⚠️ خطأ في تنظيف المفاتيح القديمة:', error);
    }
}

/**
 * توحيد صيغة المسودة القادمة من localStorage إلى Array
 * يدعم الصيغ القديمة: {data:[]}, {items:[]}, {rows:[]} أو object مفهرس رقمياً
 */
function normalizeDraftData(parsedData) {
    if (Array.isArray(parsedData)) {
        return parsedData;
    }

    if (parsedData && typeof parsedData === 'object') {
        if (Array.isArray(parsedData.data)) return parsedData.data;
        if (Array.isArray(parsedData.items)) return parsedData.items;
        if (Array.isArray(parsedData.rows)) return parsedData.rows;

        const numericKeys = Object.keys(parsedData).filter(key => /^\d+$/.test(key));
        if (numericKeys.length > 0) {
            return numericKeys
                .sort((a, b) => Number(a) - Number(b))
                .map(key => parsedData[key]);
        }
    }

    return [];
}

/**
 * تنظيف البيانات الفاسدة من localStorage (سجلات بدون أسماء)
 */
function cleanupCorruptedData() {
    try {
        const storageKey = getStorageKey();
        const rawData = localStorage.getItem(storageKey);

        if (!rawData) return;

        const parsedData = JSON.parse(rawData);
        const data = normalizeDraftData(parsedData);
        if (!Array.isArray(data) || data.length === 0) return;

        // فلترة السجلات - إزالة السجلات بدون اسم
        const cleanData = data.filter(item => {
            return item.name && item.name.trim() !== '';
        });

        if (cleanData.length !== data.length || !Array.isArray(parsedData)) {
            console.log(`🧹 تنظيف البيانات الفاسدة: ${data.length - cleanData.length} سجل محذوف`);

            if (cleanData.length > 0) {
                localStorage.setItem(storageKey, JSON.stringify(cleanData));
            } else {
                localStorage.removeItem(storageKey);
            }
        }
    } catch (error) {
        console.warn('⚠️ خطأ في تنظيف البيانات الفاسدة:', error);
    }
}

// ========== المتغيرات العامة ==========
var lastAddedEmployeeId = null;
var lastAddedTime = 0;
var isSubmitting = false; // لمنع الإرسال المتعدد
var hasDataLoaded = false; // لمنع تكرار تحميل البيانات
var isBulkImporting = false; // لتعطيل فحص التكرار السريع أثناء الاستيراد الجماعي
var currentBatchReceiptNo = null; // رقم الكشف الموحد للمجموعة المستوردة

// دالة بسيطة لتحديث سعر الإيفاد بناءً على المستوى الوظيفي
function updateMissionRateFromLevel($row) {
    const missionName = $row.find('.js-city-id').val();
    const responsibilityLevel = $row.find('.js-responsibility-level').val();

    if (!window.missionRates || window.missionRates.length === 0) {
        console.warn('⚠️ missionRates غير محمل');
        return;
    }

    if (!missionName || !responsibilityLevel) {
        console.log('⚠️ بيانات ناقصة: الإيفاد=' + missionName + ', المستوى=' + responsibilityLevel);
        return;
    }

    console.log(`🔍 البحث عن: "${missionName}" + "${responsibilityLevel}"`);

    // البحث عن الإيفاد المطابق
    const found = window.missionRates.find(m =>
        m.name === missionName &&
        m.responsibility_level === responsibilityLevel
    );

    if (found) {
        const daily_rate = parseFloat(found.daily_rate) || 0;
        $row.find('.js-daily-allowance').val(daily_rate);
        console.log(`✅ تم تحديث السعر: ${daily_rate}`);

        // إعادة حساب المجموع
        calculateRow($row);
        updateTotals();
    } else {
        console.log(`❌ لم يتم العثور على الإيفاد`);
        $row.find('.js-daily-allowance').val(0);
    }
}

console.log('📁 payroll_manager.js loaded');

function startApp() {
    console.log('🚀 Starting app...');

    // تنظيف المفاتيح القديمة من localStorage
    cleanupLegacyStorageKeys();

    // 🧹 تنظيف البيانات الفاسدة (سجلات بدون أسماء)
    cleanupCorruptedData();

    // Check dependencies
    const $ = window.$;
    const hasJQuery = typeof $ !== 'undefined';
    const hasSelect2 = hasJQuery && $.fn && typeof $.fn.select2 !== 'undefined';

    console.log('jQuery:', hasJQuery);
    console.log('Select2:', hasSelect2);

    if (!hasJQuery) {
        console.error('❌ jQuery not loaded');
        return;
    }

    if (!hasSelect2) {
        console.warn('⏳ Select2 still loading, waiting...');
        setTimeout(startApp, 300);  // محاولة مجددة بعد 300ms
        return;
    }

    // ✅ التحقق من الرجوع من صفحة الطباعة أو بعد الحفظ
    // إذا كان هناك علم بأن عملية حفظ بدأت قبلاً، احذف المسودة
    const hasSaveStarted = sessionStorage.getItem('save_initiated');
    const returningFromPrint = sessionStorage.getItem('returning_from_print');

    if (hasSaveStarted === 'true' || returningFromPrint === 'true') {
        // تنظيف العلامات
        sessionStorage.removeItem('save_initiated');
        sessionStorage.removeItem('returning_from_print');

        // حذف المسودات المحفوظة
        const storageKey = getStorageKey();
        console.log('🗑️ تم اكتشاف عودة من حفظ/طباعة - حذف جميع المسودات');
        localStorage.removeItem(storageKey);
        localStorage.removeItem('payroll_draft');
        console.log('✓ تم حذف المسودات بنجاح');
    }

    initEmployeeSearch();
    setupEvents();
    setupKeyboardShortcuts();

    // تحميل البيانات المحفوظة بعد إعداد الأحداث
    setTimeout(() => loadSavedData(), 100);
}

// استدعاء الدالة بعد تحميل DOM بالكامل - مرة واحدة فقط
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startApp, { once: true });
} else {
    // DOM جاهز بالفعل - الانتظار قليلاً أكثر للتأكد من تحميل Select2
    setTimeout(startApp, 500);
}

// إعادة تعيين البيانات عند العودة للصفحة (back/forward cache)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // الصفحة تم استعادتها من الـ cache - إعادة تعيين الحالة
        console.log('📄 تم استعادة الصفحة من الـ cache - إعادة تعيين الحالة');
        hasDataLoaded = false; // السماح بتحميل البيانات مجددا
        startApp(); // إعادة تشغيل التطبيق
    }
});

// حفظ البيانات تلقائياً عند الانتقال لصفحة أخرى (للحفاظ على البيانات)
window.addEventListener('beforeunload', function() {
    // تأكد من حفظ آخر تغييرات قبل المغادرة
    const data = [];
    $('#payrollTable tbody tr').each(function() {
        const $row = $(this);
        const name = $row.find('.js-name').val();
        const employeeId = $row.find('.js-employee-id').val();

        // 🔥 DEBUG: تحقق من البيانات المحفوظة
        if (employeeId || name) {
            console.log('📝 حفظ سطر:', {
                employeeId: employeeId,
                name: name,
                cityId: $row.find('.js-city-id').val()
            });
        }

        data.push({
            employeeId: employeeId,
            name: name,
            dept: $row.find('.js-dept').val(),
            jobTitle: $row.find('.js-job-title').val(),
            cityId: $row.find('.js-city-id').val() || '',
            missionType: $row.find('.js-mission-type').val() || '',
            responsibilityLevel: $row.find('.js-responsibility-level').val() || '',
            orderNo: $row.find('.js-order-no').val() || '',
            orderDate: $row.find('.js-order-date').val() || '',
            startDate: $row.find('.js-start-date').val() || '',
            endDate: $row.find('.js-end-date').val() || '',
            accFee: parseFloat($row.find('.js-acc-fee').val()) || 0,
            receipts: parseFloat($row.find('.js-receipts').val()) || 0,
            notes: $row.find('.js-notes').val() || '',
            isHalf: $row.find('.js-is-half').is(':checked'),
            receiptNo: $row.find('.js-receipt-no').val() || ''
        });
    });

    if (data.length > 0) {
        const storageKey = getStorageKey();
        localStorage.setItem(storageKey, JSON.stringify(data));
        console.log('💾 تم حفظ البيانات قبل المغادرة - المفتاح:', storageKey);
        console.log('📊 عدد السجلات:', data.length);
        console.log('🔍 بيانات العينة:', data[0]);
    }
});

function initEmployeeSearch() {
    console.log('🔧 جاري تهيئة Select2...');

    const $ = window.$;

    // التحقق من jQuery و Select2
    if (!$ || !$.fn || !$.fn.select2) {
        console.log('ℹ️ Select2 غير متاحة - قد تكون هذه ليست صفحة create');
        return;
    }

    const $select = $('#employee_search');
    if ($select.length === 0) {
        console.log('ℹ️ عنصر #employee_search غير موجود - قد تكون هذه ليست صفحة create');
        return;
    }

    console.log('✅ جميع المكونات جاهزة');

    // تدمير Select2 القديم إن وجد
    if ($select.data('select2')) {
        console.log('🧹 تدمير Select2 القديم');
        try {
            $select.select2('destroy');
        } catch (e) {
            console.warn('تحذير عند تدمير Select2:', e.message);
        }
    }

    // إزالة الأحداث السابقة
    $select.off('select2:select');

    try {
        $select.select2({
            placeholder: "ابحث عن موظف بالاسم أو الرقم الوظيفي...",
            allowClear: true,
            width: '100%',
            dir: "rtl",
            dropdownParent: $('body'),
            minimumInputLength: 2,
            ajax: {
                url: '/api/employees/search',
                dataType: 'json',
                delay: 300,
                cache: true,
                data: function(params) {
                    console.log('🔍 البحث عن:', params.term);
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    console.log('📊 نتائج البحث:', data);
                    params.page = params.page || 1;
                    return {
                        results: data.results || data,
                        pagination: data.pagination || { more: false }
                    };
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('❌ خطأ في البحث:', textStatus, errorThrown);
                }
            }
        }).on('select2:select', handleEmployeeSelect);

        console.log('✅ Select2 اتهيأت بنجاح');
    } catch (error) {
        console.error('❌ خطأ في تهيئة Select2:', error);
    }
}

function handleEmployeeSelect(e) {
    e.stopPropagation();
    e.stopImmediatePropagation();

    const employee = e.params.data;
    console.log('👤 تم اختيار الموظف:', employee);

    // إزالة الفحص الصارم - السماح بإضافة نفس الموظف بفترات أو أوامر مختلفة
    // التحقق من التكرار سيتم في checkDuplicateInTable عند ملء البيانات

    addEmployeeToTable(
        employee.id,
        employee.text,
        employee.dept || '',
        employee.job_title || ''
    );

    $(this).val(null).trigger('change');
    reopenSearch($(this));

    // تحريك التركيز للصف المضاف حديثاً
    setTimeout(() => {
        const $lastRow = $('#payrollTable tbody tr').last();
        if ($lastRow.length) {
            $lastRow.find('.js-city-id').focus();
        }
    }, 200);

    return false;
}

// دالة checkEmployeeDuplicate تم إلغاؤها - التحقق من التكرار يتم في checkDuplicateInTable
// التي تأخذ في الاعتبار الفترة ورقم الأمر الإداري

function reopenSearch($selectElement) {
    setTimeout(() => {
        try {
            $selectElement.select2('open');

            setTimeout(() => {
                const $searchField = $('.select2-search__field');
                if ($searchField.length) {
                    $searchField.focus();
                    $searchField[0].select();
                }
            }, 100);

        } catch (error) {
            console.error('❌ خطأ في فتح البحث:', error);
            $selectElement.next('.select2-container').find('.select2-selection').trigger('click').focus();
        }
    }, 200);
}

function addEmployeeToTable(employeeId, employeeText, department, jobTitle,
                           cityId = '', orderNo = '', orderDate = '',
                           startDate = '', endDate = '', accFee = 0,
                           receipts = 0, notes = '', isHalf = false, receiptNo = '', missionType = '', responsibilityLevel = '') {

    // منع الإضافة المتكررة لنفس الموظف (معطل أثناء الاستيراد الجماعي)
    if (!isBulkImporting) {
        const now = Date.now();
        if (lastAddedEmployeeId === employeeId && (now - lastAddedTime) < 1500) {
            console.warn('⚠️ تم تجاهل إضافة مكررة لنفس الموظف');
            return;
        }
        lastAddedEmployeeId = employeeId;
        lastAddedTime = now;
    }

    // تنظيف اسم الموظف
    let employeeName = employeeText;
    if (employeeName.includes(' [')) {
        employeeName = employeeName.split(' [')[0];
    }

    // 🔥 DEBUG: تنظيف اسم الموظف
    console.log(`🏗️ [addEmployeeToTable] بناء صف جديد:`, {
        employeeText: employeeText,
        employeeName: employeeName,
        employeeId: employeeId
    });

    // إزالة الفحص الصارم - السماح بإضافة نفس الموظف عدة مرات
    // التحقق من التكرار التام سيتم في checkDuplicateInTable عند ملء الفترة والأمر

    const rowId = 'row_' + Date.now();
    const citiesOptions = $('#cities_source').html();
    const missionTypesOptions = $('#mission_types_source').html() || '<option value="">نوع الإيفاد...</option>';
    const responsibilityLevelsOptions = $('#responsibility_levels_source').html() || '<option value="">المستوى الوظيفي...</option>';

    // الحصول على اليوم الحالي كقيمة افتراضية
    const today = new Date().toISOString().split('T')[0];
    const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

    const newRow = `
    <tr id="${rowId}" class="payroll-row hover:bg-gray-50 transition-colors" data-employee-id="${employeeId}">
        <td class="p-2 border text-right">
            <input type="hidden" class="js-employee-id" value="${employeeId}">
            <input type="hidden" class="js-name" value="${employeeName}">
            <input type="hidden" class="js-dept" value="${department}">
            <input type="hidden" class="js-receipt-no" value="${receiptNo}">
            <input type="hidden" class="js-mission-type" value="${missionType}">
            <div class="font-bold text-blue-900">${employeeName}</div>
            <div class="text-xs text-gray-500">${department}</div>
            ${jobTitle ? `<div class="text-xs text-blue-600">${jobTitle}</div>` : ''}
        </td>
        <td class="p-2 border">
            <div class="relative">
                <input type="text" class="js-job-title w-full border border-gray-300 rounded px-2 py-1 text-sm"
                       value="${jobTitle}" placeholder="العنوان الوظيفي" style="display: block;">
                <select class="js-responsibility-level w-full border border-gray-300 rounded px-2 py-1 text-sm" style="display: none;">
                    <option value="">اختر المستوى الوظيفي...</option>
                    <option value="منتسب">منتسب</option>
                    <option value="مسؤول شعبة">مسؤول شعبة</option>
                    <option value="مسؤول وجبة">مسؤول وجبة</option>
                    <option value="مسؤول وحدة">مسؤول وحدة</option>
                    <option value="معاون">معاون</option>
                    <option value="رئيس">رئيس</option>
                    <option value="عضو">عضو</option>
                    <option value="مستشار">مستشار</option>
                    <option value="نائب أمين عام">نائب أمين عام</option>
                    <option value="أمين عام">أمين عام</option>
                </select>
            </div>
        </td>
        <td class="p-2 border">
            <select class="js-city-id w-full border-gray-300 rounded px-1 py-1" style="font-size: 12px;">
                <option value="">اختر الوجهة...</option>
                ${citiesOptions}
            </select>
        </td>
        <td class="p-2 border text-xs">
            <input type="text" class="js-order-no w-full border-gray-200 rounded px-1 py-0.5 mb-1" style="font-size: 11px;"
                   placeholder="رقم الأمر">
            <input type="text" class="js-order-date w-full border-gray-200 rounded px-1 py-0.5" style="font-size: 11px; color: #2563eb;"
                   placeholder="yyyy/mm/dd" title="صيغة التاريخ: yyyy/mm/dd" ${orderDate ? `value="${orderDate}"` : (today ? `value="${today}"` : '')}>
        </td>
        <td class="p-2 border text-xs">
            <input type="text" class="js-start-date w-full border-gray-200 rounded px-1 py-0.5 mb-1" style="font-size: 11px; color: #2563eb;"
                   placeholder="yyyy/mm/dd" title="صيغة التاريخ: yyyy/mm/dd" ${startDate ? `value="${startDate}"` : ''}>
            <input type="text" class="js-end-date w-full border-gray-200 rounded px-1 py-0.5" style="font-size: 11px; color: #2563eb;"
                   placeholder="yyyy/mm/dd" title="صيغة التاريخ: yyyy/mm/dd" ${endDate ? `value="${endDate}"` : ''}>>
        </td>
        <td class="p-2 border text-center font-bold text-blue-800 js-days-count" style="font-size: 13px;">0</td>
        <td class="p-2 border">
            <input type="number" class="js-daily-allowance text-center border border-gray-300 rounded bg-gray-50 font-bold" style="width: 75px; padding: 4px 2px; font-size: 12px;"
                   readonly value="0">
        </td>
        <td class="p-2 border">
            <input type="number" class="js-acc-fee w-20 text-center border-gray-300 rounded px-2 py-1 js-acc-fee-input"
                   value="${accFee}" min="0" placeholder="0">
            <div class="text-[10px] text-red-600 js-acc-alert hidden">مطلوب!</div>
        </td>
        <td class="p-2 border">
            <input type="number" class="js-receipts w-20 text-center border-gray-300 rounded px-2 py-1"
                   value="${receipts}" min="0">
        </td>
        <td class="p-2 border font-bold text-green-700 js-total-amount">0</td>
        <td class="p-2 border text-center">
            <input type="checkbox" class="js-is-half w-4 h-4" ${isHalf ? 'checked' : ''}>
        </td>
        <td class="p-2 border">
            <textarea class="js-notes w-full border-gray-300 rounded px-2 py-1 text-xs"
                      rows="1" placeholder="ملاحظات">${notes}</textarea>
        </td>
        <td class="p-2 border text-center">
            <button type="button" class="js-remove-row text-red-500 hover:text-red-700 transition-colors p-1 rounded hover:bg-red-50"
                    data-row-id="${rowId}" title="حذف السطر">
                ❌
            </button>
            <br>
            <input type="checkbox" class="js-delete-row-multi w-4 h-4 cursor-pointer mt-1" title="تحديد للحذف المتعدد">
        </td>
    </tr>`;

    $('#payrollTable tbody').append(newRow);

    // الحصول على المرجع للصف المضاف حديثاً
    const $row = $(`#${rowId}`);

    // 🔥 DEBUG: التحقق من الحقول بعد الإنشاء
    const savedName = $row.find('.js-name').val();
    const savedEmpId = $row.find('.js-employee-id').val();
    console.log(`📝 [بعد الإضافة] التحقق من js-name و js-employee-id:`, {
        'js-name': savedName,
        'js-employee-id': savedEmpId,
        rowId: rowId
    });

    // 🔥 تفعيل البولك قبل تعيين القيم
    const wasInBulkMode = isBulkImporting;
    if (!wasInBulkMode) {
        isBulkImporting = true;
    }

    // أضف معالج فوراً للوجهة والمستوى الوظيفي لهذا الصف
    $row.find('.js-city-id').on('change', function() {
        const cityValue = $(this).val();
        console.log('🌍 [صف جديد] تغيير الوجهة:', cityValue);
        const isOutside = applyDestinationFieldState($row, cityValue);
        console.log('  🔍 نوع:', isOutside ? 'خارج القطر ✈️' : 'مدينة 🏙️');
        // لا تحسب أثناء بناء الصف الأولي
        if (!wasInBulkMode) {
            calculateRow($row);
            saveToLocalStorage();  // 🔥 احفظ عند تغيير الوجهة
            updateTotals();
        }
    });

    $row.find('.js-responsibility-level').on('change', function() {
        console.log('📊 [صف جديد] تغيير المستوى:', $(this).val());
        if (!wasInBulkMode) {
            calculateRow($row);
            saveToLocalStorage();  // 🔥 احفظ عند كل تغيير
            updateTotals();
        }
    });

    // تعبئة الحقول إذا مررت بيانات
    // 🔥 عيّن المستوى الوظيفي أولاً قبل تعيين الوجهة
    if (responsibilityLevel) {
        $row.find('.js-responsibility-level').val(responsibilityLevel);
    }

    // 🔥 الآن عيّن الوجهة
    if (cityId) {
        const cityValue = String(cityId).trim();
        const $citySelect = $row.find('.js-city-id');
        let $targetOption = $citySelect.find('option').filter(function() {
            return String($(this).val()).trim() === cityValue;
        });

        if ($targetOption.length === 0) {
            $citySelect.append(`<option value="${cityValue}">${cityValue}</option>`);
            $targetOption = $citySelect.find('option').filter(function() {
                return String($(this).val()).trim() === cityValue;
            });
        }

        $citySelect.find('option').prop('selected', false);
        $targetOption.prop('selected', true);
        $citySelect.val(cityValue);
        applyDestinationFieldState($row, $citySelect.val() || cityValue);
    }
    if (missionType) {
        $row.find('.js-mission-type').val(missionType);
    }
    if (orderNo) {
        $row.find('.js-order-no').val(orderNo);
    }

    // 🔥 الآن احسب بعد تعيين جميع البيانات
    console.log('📊 [addEmployeeToTable] استدعاء calculateRow...');
    console.log('   - cityId:', cityId);
    calculateRow($row);

    // 🔥 تحقق إضافي: تأكد من أن المبيت صحيح بعد الحساب
    const finalCityVal = $row.find('.js-city-id').val();
    const isOutside = finalCityVal && finalCityVal.includes('خارج القطر');
    const currentAccFee = parseFloat($row.find('.js-acc-fee').val()) || 0;

    console.log('🔍 [FINAL CHECK] بعد calculateRow:');
    console.log('   - finalCityVal:', finalCityVal);
    console.log('   - isOutside:', isOutside);
    console.log('   - currentAccFee:', currentAccFee);

    if (!isOutside && currentAccFee !== 10000) {
        console.warn(`⚠️ تصحيح المبيت: من ${currentAccFee} إلى 10000`);
        $row.find('.js-acc-fee').val(10000);
    } else if (isOutside && currentAccFee !== 0) {
        console.warn(`⚠️ تصحيح المبيت (خارج القطر): من ${currentAccFee} إلى 0`);
        $row.find('.js-acc-fee').val(0);
    }

    console.log('✅ [FINAL VALUE] المبيت النهائي:', $row.find('.js-acc-fee').val());

    // استعد البولك مود إلى حالته السابقة
    if (!wasInBulkMode) {
        isBulkImporting = false;
    }
    updateTotals();

    console.log(`✅ تم إضافة ${employeeName} للجدول`);

    // إضافة تأثير بصري للصف الجديد
    $row.addClass('bg-green-50');
    setTimeout(() => $row.removeClass('bg-green-50'), 1000);
}

function isEmployeeAlreadyInTable(employeeId) {
    let found = false;
    $('.payroll-row').each(function() {
        const existingId = $(this).find('.js-employee-id').val();
        if (existingId == employeeId) {
            found = true;
            return false; // كسر اللوب
        }
    });
    return found;
}

function removeTableRow(rowId) {
    if (confirm('هل تريد حذف هذا السطر؟')) {
        const $row = $(`#${rowId}`);
        $row.addClass('opacity-0 transform -translate-x-4 transition-all duration-300');

        setTimeout(() => {
            $row.remove();
            saveToLocalStorage();
            updateTotals();
        }, 300);
    }
}

function clearAllRows() {
    if ($('.payroll-row').length === 0) {
        alert('لا توجد بيانات للحذف!');
        return;
    }

    if (confirm('هل تريد حذف جميع البيانات؟ سيتم فقدان كل المسودة.')) {
        $('#payrollTable tbody').empty();
        // حذف جميع مفاتيح المسودات المحتملة
        localStorage.removeItem('payroll_draft');
        if (window.currentUserId) {
            localStorage.removeItem('payroll_draft:' + window.currentUserId);
        }
        updateTotals();
        showNotification('تم حذف جميع البيانات', 'success');
    }
}

/**
 * حساب اليومية للخارج القطر
 * طلب مباشر من الخادم (API)
 * @param {string} missionName - اسم الإيفاد (خارج القطر/1 إلخ)
 * @param {string} responsibilityLevel - مستوى المسؤولية/المستوى الوظيفي
 * @returns {Promise<number>} وعد برجوع سعر اليوم الواحد
 */
function f1_calculateOutsideCountryDaily(missionName, responsibilityLevel) {
    console.log('🔄 f1_calculateOutsideCountryDaily (AJAX):');
    console.log('  - missionName:', missionName);
    console.log('  - responsibilityLevel:', responsibilityLevel);

    if (!missionName || !responsibilityLevel) {
        console.log('  ❌ بيانات ناقصة');
        return Promise.resolve(0);
    }

    // طلب من الخادم
    return fetch('/api/mission-rate?mission=' + encodeURIComponent(missionName) + '&level=' + encodeURIComponent(responsibilityLevel))
        .then(response => {
            console.log('  📡 رد الخادم:', response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const rate = parseFloat(data.daily_rate) || 0;
                console.log('  ✅ حصلنا على السعر:', rate);
                return rate;
            } else {
                console.log('  ❌ خطأ من الخادم:', data.error);
                return 0;
            }
        })
        .catch(error => {
            console.log('  ❌ خطأ في الاتصال:', error);
            return 0;
        });
}

/**
 * حساب اليومية للخارج القطر - نسخة متزامنة (SYNC)
 * بحث في window.missionRates بدون انتظار
 * @param {string} missionName - اسم الإيفاد
 * @param {string} responsibilityLevel - المستوى الوظيفي
 * @returns {number} السعر (أو 0 إذا لم يُوجد)
 */
function f1_calculateOutsideCountryDaily_SYNC(missionName, responsibilityLevel) {
    console.log('f1_calculateOutsideCountryDaily_SYNC:');
    console.log('  - missionName:', missionName);
    console.log('  - responsibilityLevel:', responsibilityLevel);

    if (!missionName || !responsibilityLevel || !window.missionRates || window.missionRates.length === 0) {
        console.log('  ❌ بيانات ناقصة');
        return 0;
    }

    // البحث عن السجل في window.missionRates
    const missionRecord = window.missionRates.find(m => {
        const nameMatch = m.name === missionName;
        const levelMatch = m.responsibility_level === responsibilityLevel;
        return nameMatch && levelMatch;
    });

    if (missionRecord) {
        const rate = parseFloat(missionRecord.daily_rate) || 0;
        console.log('  ✅ وجدنا المعدل:', rate);
        return rate;
    } else {
        console.log('  ❌ لم نجد تطابق في mission_rates');
        console.log('  📋 البيانات المتاحة:');
        if (window.missionRates && window.missionRates.length > 0) {
            window.missionRates.forEach(m => {
                console.log(`    - ${m.name} | ${m.responsibility_level} | ${m.daily_rate}`);
            });
        }
        return 0;
    }
}

/**
 * حساب اليومية للمدن العادية
 * @param {number} cityPrice - سعر المدينة من جدول cities
 * @param {boolean} isHalf - هل يتم تطبيق 50%؟
 * @returns {number} سعر اليوم الواحد (مع تطبيق 50% إن وجدت)
 */
function f2_calculateCityDaily(cityPrice, isHalf) {
    console.log('f2_calculateCityDaily:');
    console.log('  - cityPrice:', cityPrice);
    console.log('  - isHalf:', isHalf);

    let dailyPrice = parseFloat(cityPrice) || 0;

    if (isHalf) {
        dailyPrice = dailyPrice * 0.5;
        console.log('  ✅ تم تطبيق 50%، السعر النهائي:', dailyPrice);
    } else {
        console.log('  ✅ بدون 50%، السعر النهائي:', dailyPrice);
    }

    return dailyPrice;
}

function calculateRow($row) {
    console.log('📐 calculateRow مستدعى...');
    console.log('  → معرّف الصف:', $row.attr('id') || 'بدون id');

    const startDate = $row.find('.js-start-date').val();
    const endDate = $row.find('.js-end-date').val();
    const accFeeInput = $row.find('.js-acc-fee');
    const accAlert = $row.find('.js-acc-alert');

    let days = 0;
    let isValid = true;

    // حساب الأيام
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end >= start) {
            const diffTime = Math.abs(end - start);
            days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
            $row.find('.js-days-count').removeClass('text-red-600').addClass('text-blue-800');
        } else {
            days = 0;
            $row.find('.js-days-count').addClass('text-red-600').removeClass('text-blue-800');
            isValid = false;
        }
    }

    // تحديد نوع الاختيار: مدينة عادي أم خارج القطر
    const citySelect = $row.find('.js-city-id');
    const cityValue = citySelect.val();  // احصل على القيمة مباشرة
    const isOutsideCountry = cityValue && cityValue.includes('خارج القطر');

    console.log('  → الوجهة:', cityValue);
    console.log('  → خارج البلد؟', isOutsideCountry);

    // المبيت ثابت 10,000 لكل ليلة (فقط للمدن العادية وإذا كانت الفترة > يوم واحد)
    // خارج القطر بدون مبيت
    const FIXED_ACCOMMODATION = 10000;
    const nights = days > 1 ? days - 1 : 0;

    console.log('  🔥 [DEBUG] قبل تعيين المبيت:');
    console.log('    - isOutsideCountry:', isOutsideCountry);
    console.log('    - days:', days);
    console.log('    - nights:', nights);
    console.log('    - FIXED_ACCOMMODATION:', FIXED_ACCOMMODATION);
    console.log('    - القيمة الحالية في الحقل:', accFeeInput.val());

    // التحقق من حالة 50% لتطبيقها على المبيت المعروض
    const isHalf = $row.find('.js-is-half').is(':checked');

    if (isOutsideCountry) {
        // خارج القطر: بدون مبيت
        console.log('    - ✈️ خارج القطر → تعيين 0');
        accFeeInput.val(0);
        accAlert.addClass('hidden');
        accFeeInput.removeClass('border-red-500 bg-red-50');
    } else if (nights > 0) {
        // مدينة عادية مع أكثر من يوم: مبيت ثابت 10,000 (أو 5,000 مع 50%)
        const accommodationValue = isHalf ? (FIXED_ACCOMMODATION * 0.5) : FIXED_ACCOMMODATION;
        console.log('    - 🏙️ مدينة بـ', nights, 'ليلة → تعيين', accommodationValue, (isHalf ? '(50%)' : ''));
        accFeeInput.val(accommodationValue);
        accAlert.addClass('hidden');
        accFeeInput.removeClass('border-red-500 bg-red-50');
    } else {
        // مدينة عادية بيوم واحد فقط: بدون مبيت
        console.log('    - 🏙️ مدينة بيوم واحد فقط → تعيين 0');
        accFeeInput.val(0);
        accAlert.addClass('hidden');
        accFeeInput.removeClass('border-red-500 bg-red-50');
    }

    console.log('  🔥 [DEBUG] بعد تعيين المبيت:');
    console.log('    - القيمة الفعلية:', accFeeInput.val());
    console.log('    - نوع القيمة:', typeof accFeeInput.val());

    // حساب السعر اليومي باستخدام الدالة المناسبة
    let totalDaily = 0;

    if (isOutsideCountry) {
        // الخارج: ابحث في window.missionRates
        const missionName = cityValue;  // استخدم cityValue بدلاً من selectedCity.val()
        const responsibilityLevel = $row.find('.js-responsibility-level').val() || '';

        console.log('  🔎 البحث في خارج القطر:');
        console.log('    - missionName:', missionName);
        console.log('    - responsibilityLevel:', responsibilityLevel);
        console.log('    - window.missionRates موجود؟', !!window.missionRates);
        console.log('    - عدد السجلات:', window.missionRates ? window.missionRates.length : 0);

        if (missionName && responsibilityLevel && window.missionRates && window.missionRates.length > 0) {
            console.log('    ✓ البحث جارٍ...');
            const found = window.missionRates.find(m => {
                return m.name === missionName && m.responsibility_level === responsibilityLevel;
            });
            if (found) {
                totalDaily = parseFloat(found.daily_rate) || 0;
                    // تطبيق 50% للخارج القطر أيضاً
                    const isHalf = $row.find('.js-is-half').is(':checked');
                    if (isHalf) {
                        totalDaily = totalDaily * 0.5;
                    }
                console.log('    ✅ وجدنا: السعر =', totalDaily);
            } else {
                console.log('    ❌ لم نجد مطابق');
                window.missionRates.slice(0, 5).forEach(m => {
                    console.log('      مثال: ' + m.name + ' | ' + m.responsibility_level + ' | ' + m.daily_rate);
                });
                totalDaily = 0;
            }
        } else {
            console.log('    ❌ بيانات ناقصة');
            totalDaily = 0;
        }
    } else {
        // استخدام f2: حساب اليومية للمدن
        const selectedOption = citySelect.find('option:selected');
        const cityPrice = parseFloat(selectedOption.data('price')) || 0;
        const isHalf = $row.find('.js-is-half').is(':checked');
        console.log('  🏙️ مدينة عادية:');
        console.log('    - selectedOption موجود؟', !!selectedOption.length);
        console.log('    - selectedOption value:', selectedOption.val());
        console.log('    - data-price:', selectedOption.data('price'));
        console.log('    - cityPrice:', cityPrice);
        totalDaily = f2_calculateCityDaily(cityPrice, isHalf);
        console.log('  ✓ مدينة: totalDaily =', totalDaily);
    }

    console.log('  → تعيين القيمة:', totalDaily, 'للحقل js-daily-allowance');
    $row.find('.js-daily-allowance').val(totalDaily);
    $row.find('.js-days-count').text(days);

    // حساب المبلغ الكلي
    const receipts = parseFloat($row.find('.js-receipts').val()) || 0;
    let total = 0;

    if (isOutsideCountry) {
        // خارج القطر: (أيام × السعر اليومي) + وصولات
        total = (days * totalDaily) + receipts;
        console.log('حساب المجموع (خارج القطر): (' + days + ' × ' + totalDaily + ') + ' + receipts + ' = ' + total);
    } else {
        // مدينة عادية: (أيام × السعر اليومي) + (ليالي × مبيت ثابت أو 50%) + وصولات
        // نستخدم القيمة الفعلية من حقل المبيت (الذي تم تحديثه بالفعل ليشمل 50% إن وجدت)
        const accommodationFee = parseFloat(accFeeInput.val()) || 0;
        total = (days * totalDaily) + (nights * accommodationFee) + receipts;
        console.log('حساب المجموع ( مدينة): (' + days + ' × ' + totalDaily + ') + (' + nights + ' × ' + accommodationFee + ') + ' + receipts + ' = ' + total);
    }

    // store numeric total for reliable calculations and show localized string for display
    $row.find('.js-total-amount').data('total', total).text(total.toLocaleString('ar-EG'));

    console.log('  ✅ تم تحديث الصف:');
    console.log('     - js-daily-allowance =', $row.find('.js-daily-allowance').val());
    console.log('     - js-total-amount =', $row.find('.js-total-amount').text());

    return { days, total, isValid };
}

function updateTotals() {
    let totalDays = 0;
    let totalAmount = 0;
    let totalEmployees = 0;

    $('.payroll-row').each(function() {
        const $row = $(this);
        const days = parseInt($row.find('.js-days-count').text()) || 0;
        // read numeric total from data attribute (safer than parsing localized text)
        const amount = parseFloat($row.find('.js-total-amount').data('total')) || 0;

        totalDays += days;
        totalAmount += amount;
        totalEmployees++;
    });

    $('#totalEmployees').text(totalEmployees);
    $('#totalDays').text(totalDays);
    $('#totalAmount').text(totalAmount.toLocaleString('ar-EG'));
}

function applyDestinationFieldState($row, cityValue) {
    const normalizedCity = String(cityValue || '').trim();
    const isOutsideCountry = normalizedCity.includes('خارج القطر');

    if (isOutsideCountry) {
        $row.find('.js-job-title').hide();
        $row.find('.js-responsibility-level').show();
    } else {
        $row.find('.js-job-title').show();
        $row.find('.js-responsibility-level').hide();
    }

    return isOutsideCountry;
}

function saveToLocalStorage() {
    const data = [];
    $('.payroll-row').each(function() {
        const $row = $(this);
        const citySelect = $row.find('.js-city-id');
        const selectedValue = citySelect.val() || '';

        const selectedLevel = ($row.find('.js-responsibility-level').val() || '').trim();

        const rowData = {
            employeeId: $row.find('.js-employee-id').val(),
            name: $row.find('.js-name').val(),
            dept: $row.find('.js-dept').val(),
            jobTitle: $row.find('.js-job-title').val(),
            receiptNo: $row.find('.js-receipt-no').val() || '',
            missionType: $row.find('.js-mission-type').val() || '',
            cityId: selectedValue,
            responsibilityLevel: selectedLevel,
            orderNo: $row.find('.js-order-no').val(),
            orderDate: $row.find('.js-order-date').val(),
            startDate: $row.find('.js-start-date').val(),
            endDate: $row.find('.js-end-date').val(),
            accFee: parseFloat($row.find('.js-acc-fee').val()) || 0,
            receipts: parseFloat($row.find('.js-receipts').val()) || 0,
            notes: $row.find('.js-notes').val(),
            isHalf: $row.find('.js-is-half').is(':checked')
        };

        data.push(rowData);
    });
    const storageKey = getStorageKey();
    localStorage.setItem(storageKey, JSON.stringify(data));
    console.log('💾 تم حفظ المسودة - عدد السجلات:', data.length, '- المفتاح:', storageKey);
}

function loadSavedData() {
    if (hasDataLoaded) {
        console.log('⚠️ البيانات تم تحميلها مسبقاً');
        return;
    }

    console.log('🔄 loadSavedData() بدأت - البحث عن البيانات المحفوظة...');

    const storageKey = getStorageKey();
    const savedData = localStorage.getItem(storageKey);
    if (!savedData) {
        console.log('ℹ️ لا توجد بيانات محفوظة - المفتاح:', storageKey);
        hasDataLoaded = true;  // 🔥 اضبط العلم حتى لا نحاول مجدداً
        return;
    }

    console.log('✅ وجدنا بيانات محفوظة - جاري الفحص والتحميل...');

    try {
        const parsedData = JSON.parse(savedData);
        const data = normalizeDraftData(parsedData);

        if (!Array.isArray(data)) {
            console.warn('⚠️ صيغة بيانات غير متوقعة في localStorage');
            hasDataLoaded = true;
            return;
        }

        if (!Array.isArray(parsedData)) {
            console.log('🔧 تحويل صيغة المسودة القديمة إلى Array...');
            localStorage.setItem(storageKey, JSON.stringify(data));
        }

        if (data.length === 0) {
            console.log('ℹ️ البيانات المحفوظة فارغة');
            hasDataLoaded = true;  // 🔥 اضبط العلم
            return;
        }

        // 🔥 DEBUG: تحقق من البيانات المحملة
        console.log('🔍 البيانات المحملة من localStorage:');
        console.log('  عدد السجلات:', data.length);
        console.log('  عينة من البيانات:', data[0]);
        console.log('  الأسماء:', data.map((d, idx) => `${idx + 1}. ${(d && d.name) || 'بدون اسم'}`).join(', '));

        // لا نحذف المسودة بسبب بيانات قديمة أو ناقصة، نستعيد ما يمكن استعادته

        // التحقق من أننا في صفحة الإنشاء فقط
        const isCreatePage = $('#payrollTable').length > 0;

        if (!isCreatePage) {
            // نحن في صفحة أخرى - لا نعرض رسالة ولا نحمل البيانات
            console.log('ℹ️ نحن في صفحة أخرى - لا نعرض رسالة البيانات المحفوظة');
            hasDataLoaded = true;
            return;
        }

        // عرض رسالة تأكيد مرة واحدة فقط في صفحة الإنشاء
        if (!hasDataLoaded) {
            if (confirm(`تم العثور على ${data.length} سجل محفوظ. هل تريد استعادتهم؟`)) {
                // مسح الجدول الحالي قبل تحميل البيانات المحفوظة
                $('#payrollTable tbody').empty();

                // تفعيل وضع الاستيراد الجماعي لتجنب الفحوصات السريعة
                isBulkImporting = true;

                // 🔥 فحص سلامة البيانات - نقبل السجلات التي لها اسم (حتى بدون employeeId من Excel)
                const validData = data.filter(item => {
                    const isValid = !!(item.name);
                    if (!isValid) {
                        console.warn('⚠️ سجل ناقص (لا يوجد اسم)، تم تجاهله:', item);
                    }
                    return isValid;
                });

                if (validData.length === 0) {
                    console.warn('⚠️ لا توجد بيانات صالحة للاستعادة');
                    isBulkImporting = false;
                    hasDataLoaded = true;
                    return;
                }

                console.log(`✅ عدد السجلات الصحيحة: ${validData.length} من ${data.length}`);

                // 🔥 إذا كانت هناك بيانات قديمة بدون responsibilityLevel، قم بحذفها وإعادة حفظ البيانات الصحيحة
                const needsUpdate = validData.length !== data.length;
                if (needsUpdate) {
                    console.log('🔄 تحديث البيانات المحفوظة - حذف السجلات غير الصحيحة');
                    // سيتم إعادة حفظ البيانات الصحيحة بعد تحميلها
                }

                validData.forEach(item => {
                    const employeeText = item.name + (item.dept ? ' [' + item.dept + ']' : '');
                    // لا تسترجع تأشير الجكبوكس للحذف المتعدد بعد التحديث
                    addEmployeeToTable(
                        item.employeeId,
                        employeeText,
                        item.dept,
                        item.jobTitle,
                        item.cityId,
                        item.orderNo,
                        item.orderDate,
                        item.startDate,
                        item.endDate,
                        item.accFee,
                        item.receipts,
                        item.notes,
                        false, // isHalf
                        item.receiptNo,
                        item.missionType || '',
                        item.responsibilityLevel
                    );
                });

                // تعطيل وضع الاستيراد الجماعي
                isBulkImporting = false;
                lastAddedEmployeeId = null;
                lastAddedTime = 0;

                // 🔥 الآن احسب جميع الصفوف بعد تحميل البيانات

                // 🔥 قبل الحساب: تأكد من تحميل جميع البيانات في الحقول
                console.log('📊 التحقق من البيانات المحملة - عدد الصفوف:', $('#payrollTable tbody tr').length);
                $('#payrollTable tbody tr').each(function(index) {
                    const $row = $(this);
                    const cityVal = $row.find('.js-city-id').val();
                    const respLevel = $row.find('.js-responsibility-level').val();
                    console.log(`  صف ${index + 1}: city="${cityVal}" | level="${respLevel}"`);
                });

                // 🔥 الآن احسب جميع الصفوف بعد تحميل البيانات بالكامل
                console.log('📊 حسابات الصفوف المحملة - عدد الصفوف:', $('#payrollTable tbody tr').length);
                $('#payrollTable tbody tr').each(function(index) {
                    const $row = $(this);
                    console.log(`  حساب صف ${index + 1}...`);
                    const cityVal = $row.find('.js-city-id').val();
                    const respLevel = $row.find('.js-responsibility-level').val();
                    console.log(`    قبل الحساب: city="${cityVal}" | level="${respLevel}"`);

                    calculateRow($row);

                    const dailyVal = $row.find('.js-daily-allowance').val();
                    const totalVal = $row.find('.js-total-amount').text();
                    console.log(`    بعد الحساب: daily="${dailyVal}" | total="${totalVal}"`);
                });

                console.log('📈 تحديث الإجماليات...');
                updateTotals();

                console.log('💾 حفظ البيانات بعد التحميل...');
                saveToLocalStorage();
                hasDataLoaded = true;
                console.log(`✅ تم تحميل ${data.length} سجل من التخزين المحلي`);
            } else {
                // إذا رفض المستخدم، لا تحذف البيانات - احتفظ بها محفوظة
                hasDataLoaded = true;
                console.log('⚠️ تم الاحتفاظ بالبيانات المحفوظة');
            }
        }
    } catch (error) {
        console.error('❌ خطأ في تحميل البيانات:', error);
        // لا تحذف المسودة تلقائياً في حالة خطأ parsing/تحميل
        // حتى لا نفقد بيانات المستخدم
        hasDataLoaded = true;
    }
}

function setupEvents() {
    console.log('🔥 setupEvents() بدأ - نسجل معالجات الأحداث');

    // إزالة الأحداث السابقة لمنع التكرار
    $(document).off('input change', '.js-start-date, .js-end-date, .js-acc-fee, .js-receipts, .js-is-half, .js-job-title, .js-order-no, .js-order-date, .js-notes, .js-responsibility-level');
    $(document).off('change', '.js-city-id, .js-responsibility-level');
    $(document).off('click', '.js-remove-row');

    // إعداد الأحداث الجديدة - الحقول العادية
    $(document).on('input change', '.js-start-date, .js-end-date, .js-acc-fee, .js-receipts, .js-is-half, .js-job-title, .js-order-no, .js-order-date, .js-notes', function() {
        const $row = $(this).closest('tr');
        if ($row.length) {
            calculateRow($row);
            saveToLocalStorage();
            updateTotals();
        }
    });

    // عند تغيير الوجهة (المدينة) - بشكل منفصل
    $(document).on('change', '.js-city-id', function() {
        const $row = $(this).closest('tr');
        const cityValue = $(this).val();
        const isOutsideCountry = applyDestinationFieldState($row, cityValue);

        console.log('🔍 تغيير الوجهة:', cityValue, 'خارج البلد؟', isOutsideCountry);

        // احسب المجموع في كل الأحوال
        if ($row.length) {
            calculateRow($row);
            // لا تحفظ أثناء استيراد البيانات (isBulkImporting = true)
            if (!isBulkImporting) {
                saveToLocalStorage();
            }
            updateTotals();
        }
    });

    // عند تغيير المستوى الوظيفي - احسب المجموع للصف مباشرة
    $(document).on('change', '.js-responsibility-level', function() {
        const $row = $(this).closest('tr');
        console.log('🔄 تغيير المستوى الوظيفي:', $(this).val());
        calculateRow($row);
        // لا تحفظ أثناء استيراد البيانات (isBulkImporting = true)
        if (!isBulkImporting) {
            saveToLocalStorage();
        }
        updateTotals();
    });

    // track hovered/active row for keyboard shortcuts (more reliable than :hover selector)
    $(document).on('mouseenter', '.payroll-row', function() {
        $(this).addClass('is-hovered');
    });
    $(document).on('mouseleave', '.payroll-row', function() {
        $(this).removeClass('is-hovered');
    });

    // حدث لحذف الصفوف
    $(document).on('click', '.js-remove-row', function() {
        const rowId = $(this).data('row-id');
        if (rowId) {
            removeTableRow(rowId);
        }
    });

    // إعداد استيراد Excel
    $('#excel_input').off('change').on('change', function(e) {
        uploadExcel(this);
    });

    // إعداد إرسال النموذج
    setupFormSubmit();

    console.log('✅ تم إعداد جميع الأحداث');
}

function setupFormSubmit() {
    $('#mainPayrollForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        if (isSubmitting) {
            alert('جاري معالجة الطلب السابق...');
            return;
        }

        const rowsData = [];
        let isValid = true;
        let validationErrors = [];

        // التحقق من جميع الصفوف
        $('.payroll-row').each(function(index) {
            const $row = $(this);
            const citySelect = $row.find('.js-city-id');
            const selectedValue = String(citySelect.val() || '').trim();
            const $selectedOption = citySelect.find('option:selected');
            const selectedText = String($selectedOption.text() || '').trim();
            const selectedDataValue = String($selectedOption.data('value') || '').trim();

            // fallback أقوى: val() -> data-value in option -> visible text -> all options text -> master field
            const masterDestination = String($('#masterDestination').val() || '').trim();
            const allOptionsText = citySelect.find('option').not(':first').map(function() { return $(this).text().trim(); }).get().find(t => t && !t.includes('اختر'));

            let normalizedDestination =
                (selectedValue && selectedValue !== '' && !selectedValue.includes('اختر')) ? selectedValue :
                (selectedDataValue && !selectedDataValue.includes('اختر')) ? selectedDataValue :
                (selectedText && !selectedText.includes('اختر')) ? selectedText :
                (allOptionsText && !allOptionsText.includes('اختر')) ? allOptionsText :
                masterDestination;

            // تطبيق صارم: إذا stillفارغة، استخدم المدينة الأولى المتاحة كفallback أخير
            if (!normalizedDestination || normalizedDestination === '') {
                const firstOption = citySelect.find('option:not(:first)').first();
                if (firstOption.length) {
                    normalizedDestination = String(firstOption.val() || firstOption.text()).trim();
                    console.warn(`⚠️ السطر ${index+1}: استخدام الخيار الأول كـ fallback:`, normalizedDestination);
                }
            }

            const isOutsideCountry = normalizedDestination && normalizedDestination.includes('خارج القطر');

            console.log(`📝 السطر ${index+1}: selectedValue="${selectedValue}" selectedText="${selectedText}" master="${masterDestination}" => normalized="${normalizedDestination}"`);

            const rowData = {
                index: index + 1,
                employee_id: $row.find('.js-employee-id').val(),
                name: $row.find('.js-name').val(),
                dept: $row.find('.js-dept').val(),
                job_title: $row.find('.js-job-title').val(),
                receipt_no: $row.find('.js-receipt-no').val(),
                city_id: isOutsideCountry ? '' : normalizedDestination,
                mission_type: isOutsideCountry ? normalizedDestination : '',
                responsibility_level: isOutsideCountry
                    ? (($row.find('.js-responsibility-level').val() || '').trim())
                    : ($row.find('.js-responsibility-level').val() || '').trim(),
                order_no: $row.find('.js-order-no').val(),
                order_date: $row.find('.js-order-date').val(),
                start_date: $row.find('.js-start-date').val(),
                end_date: $row.find('.js-end-date').val(),
                acc_fee: parseFloat($row.find('.js-acc-fee').val()) || 0,
                receipts: parseFloat($row.find('.js-receipts').val()) || 0,
                notes: $row.find('.js-notes').val(),
                is_half: $row.find('.js-is-half').is(':checked') ? 1 : 0
            };

            // التحقق الأساسي
            const missingFields = [];
            if (!rowData.name) missingFields.push('اسم الموظف');
            const hasCity = !!rowData.city_id;
            const hasMissionLevel = !!rowData.mission_type && !!rowData.responsibility_level;

            if (!hasCity && !hasMissionLevel) {
                missingFields.push('الوجهة ( مدينة أو خارج القطر + مستوى)');
            }
            if (rowData.mission_type && !rowData.responsibility_level) {
                missingFields.push('المستوى الوظيفي');
            }
            if (!rowData.order_no) missingFields.push('رقم الأمر');
            if (!rowData.order_date) missingFields.push('تاريخ الأمر');
            if (!rowData.start_date) missingFields.push('تاريخ البداية');
            if (!rowData.end_date) missingFields.push('تاريخ النهاية');

            // التحقق من ترتيب التواريخ: البداية يجب أن تكون قبل/تساوي النهاية
            if (rowData.start_date && rowData.end_date) {
                const startDateObj = new Date(rowData.start_date);
                const endDateObj = new Date(rowData.end_date);

                if (!Number.isNaN(startDateObj.getTime()) && !Number.isNaN(endDateObj.getTime()) && endDateObj < startDateObj) {
                    missingFields.push('تاريخ البداية لا يمكن أن يكون بعد تاريخ النهاية');
                }
            }

            if (missingFields.length > 0) {
                isValid = false;
                $row.addClass('bg-red-50 border-red-200');
                validationErrors.push(`السطر ${index + 1}: ${missingFields.join('، ')}`);
            } else {
                $row.removeClass('bg-red-50 border-red-200');
            }

            // التحقق من المبيت إذا الأيام > 1 (للمدن فقط)
            const days = calculateRow($row).days;
            if (!isOutsideCountry && days > 1 && rowData.acc_fee <= 0) {
                isValid = false;
                $row.addClass('bg-red-50 border-red-200');
                validationErrors.push(`السطر ${index + 1}: ${rowData.name} - مطلوب إدخال مبلغ المبيت (${days} يوم)`);
            }

            rowsData.push(rowData);
        });

        if (rowsData.length === 0) {
            alert('⚠️ لا توجد بيانات للإرسال!');
            return;
        }

        if (!isValid) {
            const errorMsg = '⚠️ يوجد أخطاء في البيانات:\n' + validationErrors.join('\n');
            alert(errorMsg);
            return;
        }

        // التحقق من التكرار مع قاعدة البيانات
        proceedWithSubmission(rowsData);
    });
}

function proceedWithSubmission(rowsData) {
    isSubmitting = true;

    // ضبط علم للدلالة على بدء عملية الحفظ - للتحقق عند العودة من الطباعة
    sessionStorage.setItem('save_initiated', 'true');
    console.log('📌 تم تعيين علم بدء عملية الحفظ في sessionStorage');

    const $btn = $('#submitBtn');
    const originalText = $btn.text();
    $btn.prop('disabled', true).text('🔍 جاري التحقق من التكرار...');

    console.log('📤 إرسال البيانات للتحقق من التكرار:', rowsData);

    $.ajax({
        url: '/api/check-duplicates',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            _token: $('meta[name="csrf-token"]').attr('content'),
            payrolls: rowsData
        }),
        success: function(duplicates) {
            console.log('📥 النتيجة من التحقق:', duplicates);

            if (duplicates && duplicates.length > 0) {
                // طباعة لفحص البيانات المستلمة
                console.log('🔍 بيانات التكرار المستلمة:', duplicates);

                let duplicateMessage = '❌ يوجد تداخل في فترات الإيفاد!\n';
                duplicateMessage += '═════════════════════════\n\n';

                duplicates.forEach((dup, index) => {
                    const matchedIndex = rowsData.findIndex(row =>
                        String(row.name || '').trim() === String(dup.name || '').trim() &&
                        (String(row.start_date || '') + ' إلى ' + String(row.end_date || '')) === String(dup.new_period || '')
                    );

                    const lineInfo = matchedIndex >= 0
                        ? `السطر ${matchedIndex + 1}`
                        : 'السطر غير محدد';

                    // نفس منطق الإضافة/التعديل: عرض الكشف دائماً مع fallback
                    const duplicateKashfNo = (dup.kashf_no ?? dup.receipt_no ?? 'غير محدد');

                    const orderInfo = (dup.admin_order_no && dup.admin_order_no !== 'بدون')
                        ? ` | 📄 الأمر: ${dup.admin_order_no}`
                        : '';

                    duplicateMessage += `📌 التداخل ${index + 1} | 🧾 ${lineInfo} | 📋 الكشف: ${duplicateKashfNo}${orderInfo}\n`;
                    duplicateMessage += `👤 ${dup.name}\n`;
                    duplicateMessage += `📅 الموجودة: ${dup.existing_period} | الجديدة: ${dup.new_period}\n`;
                    duplicateMessage += '\n';
                });

                duplicateMessage += '═════════════════════════\n';
                duplicateMessage += '⚠️ لا يمكن الحفظ - يرجى تعديل البيانات';

                alert(duplicateMessage);
                $btn.prop('disabled', false).text(originalText);
                isSubmitting = false;
                return;
            }

            // إذا لا يوجد تكرار، أكمل الحفظ
            console.log('✅ لا توجد تداخلات - جاري الحفظ...');
            saveData(rowsData, $btn, originalText);
        },
        error: function(xhr) {
            console.error('❌ خطأ في التحقق من التكرار:', xhr);
            console.error('الحالة:', xhr.status);
            console.error('النص:', xhr.responseText);

            alert('❌ فشل التحقق من التكرار. الرجاء المحاولة لاحقاً.');
            $btn.prop('disabled', false).text(originalText);
            isSubmitting = false;
        }
    });
}

function saveData(rowsData, $btn, originalText) {
    $btn.text('💾 جاري الحفظ...');

    $.ajax({
        url: $('#mainPayrollForm').attr('action'),
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            payload: JSON.stringify(rowsData)
        },
        success: function(response) {
            // حذف المسودة المحفوظة بعد النجاح - فوري وبدون تأخير
            const storageKey = getStorageKey();
            console.log('🗑️ جاري حذف المسودات من localStorage:');
            console.log('  - مفتاح المستخدم:', storageKey, '← نتيجة:', localStorage.getItem(storageKey) === null ? 'تم الحذف ✓' : 'لا يزال موجود ✗');
            localStorage.removeItem(storageKey);
            localStorage.removeItem('payroll_draft');
            console.log('✓ تم حذف جميع المسودات المحفوظة بنجاح');
            console.log('✓ التحقق النهائي - localStorage الآن:', localStorage.getItem(storageKey), localStorage.getItem('payroll_draft'));

            showNotification('تم حفظ البيانات بنجاح', 'success');

            if (response.ids && response.ids.length > 0) {
                const idsString = response.ids.join(',');
                setTimeout(() => {
                    window.location.href = '/payrolls/print-multiple?ids=' + idsString;
                }, 1500);
            } else {
                setTimeout(() => {
                    window.location.href = '/payrolls';
                }, 1500);
            }
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            let detailMsg = '';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                    // إذا كانت هناك تفاصيل إضافية
                    if (xhr.responseJSON.details && Array.isArray(xhr.responseJSON.details)) {
                        detailMsg = xhr.responseJSON.details.slice(0, 5).join('\n');
                        if (xhr.responseJSON.details.length > 5) {
                            detailMsg += '\n...و ' + (xhr.responseJSON.details.length - 5) + ' أخطاء أخرى';
                        }
                    }
                } else if (xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
            }

            // عرض الخطأ مع التفاصيل
            const fullMessage = detailMsg ? errorMsg + '\n\n' + detailMsg : errorMsg;
            alert(fullMessage);

            $btn.prop('disabled', false).text(originalText);
            isSubmitting = false;
        }
    });
}

function uploadExcel(input) {
    if (!input.files.length) return;

    const file = input.files[0];
    if (!file.name.match(/\.(xlsx|xls)$/)) {
        alert('الملف يجب أن يكون بصيغة Excel (xlsx أو xls)');
        return;
    }

    const $btn = $(input).prev();
    const originalText = $btn.text();
    $btn.prop('disabled', true).text('📥 جاري الاستيراد...');

    const formData = new FormData();
    // Append common field names to be compatible with different server handlers
    formData.append('excel_file', file);
    formData.append('file', file);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
        url: '/payrolls/import-preview',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.debug('uploadExcel: raw response', response);

            // Normalize possible server response shapes: array, {data: [...]}, {rows: [...]}, {payrolls: [...]}
            let payload = null;
            if (!response) {
                payload = null;
            } else if (Array.isArray(response)) {
                payload = response;
            } else if (response.data && Array.isArray(response.data)) {
                payload = response.data;
            } else if (response.rows && Array.isArray(response.rows)) {
                payload = response.rows;
            } else if (response.payrolls && Array.isArray(response.payrolls)) {
                payload = response.payrolls;
            } else if (response.items && Array.isArray(response.items)) {
                payload = response.items;
            } else if (response.error) {
                alert('❌ ' + response.error);
            }

            if (!payload || payload.length === 0) {
                alert('⚠️ لم يتم العثور على بيانات صالحة');
            } else {
                handleExcelImport(payload);
            }
            $btn.prop('disabled', false).text(originalText);
            $(input).val('');
        },
        error: function(xhr) {
            let errorMsg = '❌ فشل الاستيراد';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg += ': ' + xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            }
            alert(errorMsg);
            $btn.prop('disabled', false).text(originalText);
        }
    });
}

/**
 * توليد رقم كشف فريد للمجموعة المستوردة
 * الصيغة: رقم سنة الآن + رقم شهر + عدد عشوائي (مثال: 202502-5847)
 */
function generateReceiptNo() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const randomNum = Math.floor(Math.random() * 10000);
    return `${year}${month}-${randomNum}`;
}

function handleExcelImport(data) {

    // normalize rows and keep original Excel row number for precise error messages
    const totalRows = data.length;
    const normalizedItems = data.map((it, idx) => {
        const item = Object.assign({}, it);
        item._origIndex = item.row_number || (idx + 1);
        if (item.name && typeof item.name === 'string') item.name = item.name.trim();

        // 🔥 DEBUG: تتبع بيانات Excel
        console.log(`📊 [Excel العنصر ${item._origIndex}]:`, {
            name: item.name,
            dept: item.dept,
            employee_id: item.employee_id,
            start_date: item.start_date,
            end_date: item.end_date
        });

        return item;
    });

    // اجمع أخطاء البيانات الناقصة مع رقم السطر بدل تجاهلها بصمت
    const initialErrors = [];
    const validItems = normalizedItems.filter(it => {
        const missing = [];
        if (!it.name) missing.push('الاسم');
        if (!it.start_date) missing.push('تاريخ البداية');
        if (!it.end_date) missing.push('تاريخ النهاية');

        if (missing.length > 0) {
            initialErrors.push(`السطر ${it._origIndex}: بيانات ناقصة (${missing.join('، ')})`);
            return false;
        }
        return true;
    });

    let importConfirm = confirm(`تم العثور على ${totalRows} سطر في الملف (${validItems.length} سطر صالح للاستيراد).\nهل تريد استيراد السطور الصالحة؟`);
    if (!importConfirm) return;

    // توليد رقم كشف موحد لهذه المجموعة المستوردة
    currentBatchReceiptNo = generateReceiptNo();

    // disable duplicate-add rate limiter during bulk import
    isBulkImporting = true;

    let importedCount = 0;
    let skippedCount = initialErrors.length;
    let errors = [...initialErrors];

    // collect existing employees from the current table to avoid false duplicates
    const existingEmployeeIds = new Set();
    const existingRecords = []; // Store full records for detailed comparison
    $('.payroll-row').each(function() {
        const id = $(this).find('.js-employee-id').val();
        const name = ($(this).find('.js-name').val() || '').trim();
        const startDate = $(this).find('.js-start-date').val();
        const endDate = $(this).find('.js-end-date').val();
        const orderNo = $(this).find('.js-order-no').val();

        if (id) existingEmployeeIds.add(String(id));
        if (name) {
            existingRecords.push({
                name: name,
                startDate: startDate,
                endDate: endDate,
                orderNo: orderNo
            });
        }
    });

    // track records imported in this batch (by name+startDate+endDate+orderNo combination)
    const importedRecords = [];

    validItems.forEach((item) => {
        const idx = item._origIndex;
        // basic validation again
        if (!item.name || !item.start_date || !item.end_date) {
            skippedCount++;
            errors.push(`السطر ${idx}: بيانات ناقصة`);
            return;
        }

        // التحقق من صحة التواريخ وترتيبها
        const startDateObj = new Date(item.start_date);
        const endDateObj = new Date(item.end_date);

        if (Number.isNaN(startDateObj.getTime()) || Number.isNaN(endDateObj.getTime())) {
            skippedCount++;
            errors.push(`السطر ${idx}: صيغة تاريخ غير صحيحة`);
            return;
        }

        if (endDateObj < startDateObj) {
            skippedCount++;
            errors.push(`السطر ${idx}: تاريخ البداية لا يمكن أن يكون بعد تاريخ النهاية`);
            return;
        }

        const normalizedName = (item.name || '').trim();

        // Check for duplicate within this batch using combination of name, dates, and order
        // Allow same name if dates or order are different ONLY
        const isDuplicateInBatch = importedRecords.some(rec => {
            // نفس الشخص (بالاسم أو المعرف)?
            const isSamePerson = rec.name === normalizedName ||
                                (item.employee_id && rec.employeeId === item.employee_id);

            if (!isSamePerson) {
                return false; // أشخاص مختلفون - لا مشكلة
            }

            // نفس الشخص - فحص إذا كانت الفترة نفسها تماماً
            const recStart = new Date(rec.startDate).toISOString().split('T')[0];
            const recEnd = new Date(rec.endDate).toISOString().split('T')[0];
            const itemStart = item.start_date;
            const itemEnd = item.end_date;

            // فترة مختلفة = سماح حتى لو نفس الشخص
            if (recStart !== itemStart || recEnd !== itemEnd) {
                return false; // فترة مختلفة - لا مشكلة
            }

            // نفس الشخص + نفس الفترة
            // تحقق من الأمر الإداري
            const recOrder = (rec.orderNo || '').trim();
            const itemOrder = (item.order_no || '').trim();

            // فترة نفسها لكن أمر مختلف = سماح
            if (recOrder !== itemOrder) {
                return false; // أمر مختلف - سماح
            }

            // نفس كل شيء = مكرر تام
            return true;
        });

        if (isDuplicateInBatch) {
            skippedCount++;
            errors.push(`السطر ${idx}: ${normalizedName} - مكرر تماماً في الملف (نفس الاسم والفترة والأمر)`);
            return;
        }

        // Check overlap with current table rows (by same person check)
        if (checkDuplicateInTable(normalizedName, item.start_date, item.end_date, item.employee_id || '', item.order_no || '')) {
            skippedCount++;
            errors.push(`السطر ${idx}: ${normalizedName} - موجود بالفعل (نفس الاسم والفترة والأمر)`);
            return;
        }

        // Passed checks — add to table
        const employeeText = item.name + (item.dept ? ' [' + item.dept + ']' : '');

        // 🔥 DEBUG: قبل إضافة الموظف للجدول
        console.log(`✅ [إضافة موظف] ${item.name}:`, {
            employeeText: employeeText,
            employee_id: item.employee_id,
            dept: item.dept
        });

        addEmployeeToTable(
            item.employee_id || '',
            employeeText,
            item.dept || '',
            item.job_title || '',
            item.city_id || '',
            item.order_no || '',
            item.order_date || '',
            item.start_date,
            item.end_date,
            item.acc_fee || 0,
            item.receipts || 0,
            item.notes || '',
            item.is_half || false,
            currentBatchReceiptNo, // استخدام رقم الكشف الموحد للدفعة
            item.mission_type || '',
            item.responsibility_level || ''
        );

        // Track this record as imported
        importedRecords.push({
            name: normalizedName,
            employeeId: item.employee_id || '',
            startDate: item.start_date,
            endDate: item.end_date,
            orderNo: item.order_no || ''
        });

        importedCount++;
    });

    // عرض النتائج
    console.debug('handleExcelImport: importedCount, skippedCount, errors', importedCount, skippedCount, errors);
    let resultMessage = `✅ تم استيراد ${importedCount} سجل من ${validItems.length} سطر صالح`;
    if (skippedCount > 0) {
        resultMessage += `\n⚠️ تم تخطي ${skippedCount} سجل (أخطاء بيانات أو تكرار)`;
        if (errors.length > 0) {
            resultMessage += `\n\nالتفاصيل:\n${errors.slice(0, 10).join('\n')}`;
            if (errors.length > 10) resultMessage += `\n...و ${errors.length - 10} تفاصيل أخرى`;
        }
    } else if (importedCount > 0) {
        resultMessage += `\n✅ جميع السجلات تم استيرادها بنجاح`;
    }

    // re-enable duplicate-add rate limiter
    isBulkImporting = false;
    lastAddedEmployeeId = null;
    lastAddedTime = 0;

    alert(resultMessage);
    saveToLocalStorage();
    updateTotals();
}

function checkDuplicateInTable(employeeName, startDate, endDate, employeeId = '', orderNo = '') {
    let isDuplicate = false;

    $('.payroll-row').each(function() {
        const $row = $(this);
        const existingName = ($row.find('.js-name').val() || '').trim();
        const existingStart = $row.find('.js-start-date').val();
        const existingEnd = $row.find('.js-end-date').val();
        const existingId = $row.find('.js-employee-id').val() || '';
        const existingOrderNo = ($row.find('.js-order-no').val() || '').trim();
        const normalizedNewName = (employeeName || '').trim();

        // ===== فحص التكرار فقط إذا كان نفس الشخص =====
        const isSamePerson = (employeeId && existingId && String(employeeId) === String(existingId)) ||
                            (existingName === normalizedNewName);

        // إذا كان شخص مختلف - لا يوجد مشكلة
        if (!isSamePerson) {
            return; // different person — skip
        }

        // ===== نفس الشخص: فحص الفترة =====
        // إذا كانت الفترة مختلفة تماماً = سماح
        if (existingStart !== startDate || existingEnd !== endDate) {
            return; // فترة مختلفة - سماح
        }

        // ===== نفس الشخص + نفس الفترة: فحص الأمر الإداري =====
        // إذا كان الأمر مختلف = سماح
        if (existingOrderNo !== (orderNo || '').trim()) {
            return; // أمر مختلف - سماح
        }

        // ===== نفس كل شيء: مكرر تام =====
        isDuplicate = true;
        $row.addClass('bg-yellow-100 border-yellow-300');
        setTimeout(() => $row.removeClass('bg-yellow-100 border-yellow-300'), 3000);
        return false; // break out early
    });

    return isDuplicate;
}

function updateCityPrice($row) {
    // يتم الحساب داخل calculateRow مع أولوية nوع الإيفاد ثم المدينة
    calculateRow($row);
}

function getMissionRate(missionType, responsibilityLevel) {
    if (!missionType || !responsibilityLevel) {
        return 0;
    }

    const missionRates = Array.isArray(window.missionRates) ? window.missionRates : [];
    const matchedRate = missionRates.find((item) =>
        item.name === missionType && item.responsibility_level === responsibilityLevel
    );

    return matchedRate ? (parseFloat(matchedRate.daily_rate) || 0) : 0;
}

function setupKeyboardShortcuts() {
    $(document).on('keydown', function(e) {
        // Ctrl+Enter لإرسال النموذج
        if (e.ctrlKey && e.key === 'Enter') {
            $('#submitBtn').click();
            e.preventDefault();
        }

        // Escape لتفريغ البحث
        if (e.key === 'Escape') {
            $('#employee_search').val(null).trigger('change');
        }

        // Ctrl+D لحذف سطر مختار
        if (e.ctrlKey && e.key === 'd') {
            const $selectedRow = $('.payroll-row.is-hovered').first();
            if ($selectedRow.length) {
                const rowId = $selectedRow.attr('id');
                removeTableRow(rowId);
                e.preventDefault();
            }
        }
    });
}

function showNotification(message, type = 'info') {
    const $notification = $(`
        <div class="fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-0 ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}">
            ${message}
        </div>
    `);

    $('body').append($notification);

    setTimeout(() => {
        $notification.addClass('translate-x-full opacity-0');
        setTimeout(() => $notification.remove(), 300);
    }, 3000);
}

function exportDraft() {
    const storageKey = getStorageKey();
    const data = JSON.parse(localStorage.getItem(storageKey) || '[]');
    if (data.length === 0) {
        alert('لا توجد بيانات للتصدير');
        return;
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `payroll_draft_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

// ============ تأشير الكل - checkbox 50% في رأس الجدول ============
$(document).on('change', '#checkAllHalf', function() {
    const isChecked = $(this).prop('checked');
    $('.js-is-half').prop('checked', isChecked);

    // إعادة حساب كل صف بعد التغيير
    $('#payrollTable tbody tr').each(function() {
        calculateRow($(this));
    });

    // عرض إشعار بسيط
    if (isChecked) {
        console.log('✅ تم تأشير جميع الصفوف بـ 50%');
    } else {
        console.log('❌ تم إلغاء تأشير جميع الصفوف');
    }
});

// تحديث checkbox الرئيسي عند تغيير أي checkbox فردي
$(document).on('change', '.js-is-half', function() {
    const totalCheckboxes = $('.js-is-half').length;
    const checkedCheckboxes = $('.js-is-half:checked').length;

    if (totalCheckboxes === 0) {
        $('#checkAllHalf').prop('checked', false).prop('indeterminate', false);
    } else if (checkedCheckboxes === 0) {
        $('#checkAllHalf').prop('checked', false).prop('indeterminate', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#checkAllHalf').prop('checked', true).prop('indeterminate', false);
    } else {
        // بعض الـ checkboxes مؤشرة وبعضها لا - حالة وسطية
        $('#checkAllHalf').prop('checked', false).prop('indeterminate', true);
    }
});

// ============ نسخ رقم وتاريخ الأمر الإداري للجميع ============
$(document).on('input change', '#masterOrderNo', function() {
    const orderNo = $(this).val();
    $('.js-order-no').val(orderNo);
    console.log('📝 تم نسخ رقم الأمر الإداري:', orderNo);
});

$(document).on('change', '#masterOrderDate', function() {
    const orderDate = $(this).val();
    $('.js-order-date').val(orderDate);
    console.log('📅 تم نسخ تاريخ الأمر الإداري:', orderDate);
});

// ============ نسخ جهة الإيفاد للجميع ============
$(document).on('change', '#masterDestination', function() {
    const destination = $(this).val();

    // نسخ الوجهة لجميع الصفوف
    $('.js-city-id').val(destination);

    // إعادة حساب كل صف بعد تغيير الوجهة
    $('#payrollTable tbody tr').each(function() {
        const $row = $(this);
        applyDestinationFieldState($row, destination);
        calculateRow($row);
    });

    updateTotals();
    saveToLocalStorage();

    console.log('🏙️ تم نسخ جهة الإيفاد للجميع:', destination);
});

// ============ نسخ فترة الإيفاد للجميع ============
$(document).on('change', '#masterStartDate', function() {
    const startDate = $(this).val();
    $('.js-start-date').val(startDate);

    // إعادة حساب كل صف بعد تغيير تاريخ البداية
    $('#payrollTable tbody tr').each(function() {
        calculateRow($(this));
    });

    console.log('📅 تم نسخ تاريخ بداية الإيفاد:', startDate);
});

$(document).on('change', '#masterEndDate', function() {
    const endDate = $(this).val();
    $('.js-end-date').val(endDate);

    // إعادة حساب كل صف بعد تغيير تاريخ النهاية
    $('#payrollTable tbody tr').each(function() {
        calculateRow($(this));
    });

    console.log('📅 تم نسخ تاريخ نهاية الإيفاد:', endDate);
});

// ============ تصدير الدوال للاستخدام في HTML ============

// ============ منطق زر الحذف المتعدد ============
function updateMultiDeleteBtn() {
    const checkedCount = $('.js-delete-row-multi:checked').length;
    if (checkedCount > 0) {
        $('#multiDeleteBtn').show();
        $('#multiDeleteCount').text(checkedCount);
    } else {
        $('#multiDeleteBtn').hide();
        $('#multiDeleteCount').text('0');
    }
}

// عند التأشير على أي checkbox للحذف المتعدد
$(document).on('change', '.js-delete-row-multi', function() {
    updateMultiDeleteBtn();
});

// عند تحميل الصفحة: تأكد من إخفاء الزر في البداية
$(document).ready(function() {
    updateMultiDeleteBtn();
});

// عند الضغط على زر الحذف المتعدد
$(document).on('click', '#multiDeleteBtn', function(e) {
    e.preventDefault();
    const checkedRows = $('.js-delete-row-multi:checked').closest('tr');
    if (checkedRows.length === 0) {
        alert('لا توجد صفوف محددة للحذف!');
        return;
    }
    if (!confirm('هل تريد حذف جميع الصفوف المؤشرة؟')) {
        return;
    }
    checkedRows.each(function() {
        $(this).remove();
    });
    updateMultiDeleteBtn();
    updateTotals();
    saveToLocalStorage();
    showNotification('تم حذف الصفوف المؤشرة بنجاح', 'success');
});

// تعريف الدوال على الكائن window ليتم الوصول إليها من HTML
window.removeTableRow = removeTableRow;
window.clearAllRows = clearAllRows;
window.exportDraft = exportDraft;
window.uploadExcel = uploadExcel;

console.log('✅ تم تصدير جميع الدوال للاستخدام العام');
