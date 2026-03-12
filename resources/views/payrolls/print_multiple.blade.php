@php
function tafqit($number) {
    if (!is_numeric($number)) return "";

    $units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    $tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانمئة', 'تسعمئة'];

    if ($number == 0) return "صفر";

    $res = "";

    // المليارات
    if (floor($number / 1000000000) > 0) {
        $b = floor($number / 1000000000);
        if ($b == 1) $res .= "مليار";
        elseif ($b == 2) $res .= "ملياران";
        elseif ($b >= 3 && $b <= 10) $res .= tafqit_small($b, $units, $tens, $hundreds) . " مليارات";
        else $res .= tafqit_small($b, $units, $tens, $hundreds) . " مليار";
        $number %= 1000000000;
        if ($number > 0) $res .= " و ";
    }

    // الملايين
    if (floor($number / 1000000) > 0) {
        $m = floor($number / 1000000);
        $res .= ($m == 1 ? "مليون" : ($m == 2 ? "مليونان" : tafqit_small($m, $units, $tens, $hundreds) . " مليون"));
        $number %= 1000000;
        if ($number > 0) $res .= " و ";
    }

    // الآلاف
    if (floor($number / 1000) > 0) {
        $th = floor($number / 1000);
        if ($th == 1) $res .= "ألف";
        elseif ($th == 2) $res .= "ألفان";
        elseif ($th >= 3 && $th <= 10) $res .= tafqit_small($th, $units, $tens, $hundreds) . " آلاف";
        else $res .= tafqit_small($th, $units, $tens, $hundreds) . " ألف";
        $number %= 1000;
        if ($number > 0) $res .= " و ";
    }

    // المتبقي (المئات والآحاد)
    if ($number > 0) {
        $res .= tafqit_small($number, $units, $tens, $hundreds);
    }

    return "فقط " . $res . " دينار عراقي لا غير";
}

function tafqit_small($num, $units, $tens, $hundreds) {
    $res = "";
    if (floor($num / 100) > 0) {
        $res .= $hundreds[floor($num / 100)];
        $num %= 100;
        if ($num > 0) $res .= " و ";
    }
    if ($num > 0) {
        if ($num < 20) {
            $res .= $units[$num];
        } else {
            $res .= $units[$num % 10] . ($num % 10 > 0 ? " و " : "") . $tens[floor($num / 10)];
        }
    }
    return $res;
}

// دالة تحويل الأرقام من إنجليزي إلى عربي
function arabicNumbers($num) {
    $en = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
    $ar = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    return str_replace($en, $ar, $num);
}

// دالة تنسيق التاريخ بالصيغة العربية (DD/MM/YYYY)
function formatDateArabic($date) {
    if (!$date) return '';
    $timestamp = strtotime($date);
    if (!$timestamp) return arabicNumbers($date);
    $formatted = date('Y/m/d', $timestamp);
    return arabicNumbers($formatted);
}
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=1, user-scalable=no">
    <title>طباعة كشف الإيفاد</title>
    <style>
        @font-face {
            font-family: 'SultanBold';
            src: local('Sultan Bold'), url("{{ asset('fonts/sultan.ttf') }}") format('truetype');
        }

        @page {
            size: A4 landscape;
            margin: 0.5cm 1cm !important; /* هوامش 1سم على الجانبين */
        }

        * {
            font-family: 'SultanBold', Arial, sans-serif !important;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body { margin: 0; padding: 0; background: white; width: 100%; overflow: hidden; }

        .report-content {
            width: 100%;
            margin: 0 auto;
            padding: 5px 0; /* إزالة padding الجانبي لكسب مساحة */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
            table-layout: fixed; /* يمنع الجدول من الزحف خارج الورقة */
            word-wrap: break-word;
        }

        thead { display: table-row-group !important; } /* لا تتكرر رؤوس الأعمدة في كل صفحة */

        tbody tr.empty-spacer {
            height: auto;
        }

        th, td {
            border: 1px solid black;
            padding: 0px 1px; /* حشوة صغيرة جداً */
            font-size: 22px; /* تكبير 4 درجات إضافية */
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            line-height: 1; /* بدون مسافات */
            height: 20px; /* تقليل الارتفاع من 24px إلى 20px */
        }

        th {
            background-color: #e5e5e5 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 17px; /* تصغير بـ 5 درجات عن البيانات */
            font-weight: normal !important; /* إزالة البولد من عنوان الجدول فقط */
        }
        tbody tr.expand-row {
            font-size: 13px; /* حجم خط معقول */
            line-height: 1.2; /* مسافة قليلة بين الأسطر */
        }

        tbody tr.expand-row td {
            padding: 2px 1px !important; /* حشوة صغيرة لكن كافية */
            height: 24px !important; /* ارتفاع مقروء - نفس الصفوف العادية */
            overflow: hidden; /* إخفاء أي نص يتجاوز */
            font-size: 13px !important;
        }

        /* تمييز الإيفاد بنسبة 50% */
        .half-allowance-row {
            background-color: #e8e8e8 !important;
        }
        .half-allowance-row td {
            background-color: #e8e8e8 !important;
        }
        tbody tr:not(.half-allowance-row) td:nth-child(11) {
            background-color: #f9f9f9 !important;
        }

        /* منطقة الأمان: لحام المجموع بالتواقيع */
        .footer-safe-zone {
            display: block;
            width: 100%;
            page-break-inside: avoid; /* أهم خاصية لمنع انفصال التواقيع عن المجموع */
            background-color: white !important;
        }

        .total-strip {
            background-color: #d1d1d1 !important;
            padding: 8px 15px;
            font-weight: normal;
            display: flex;
            justify-content: space-between;
        }

        .sigs-area {
            display: flex;
            justify-content: space-around;
            padding: 16mm 5px 3px 5px; /* مضاعفة الفراغ العلوي */
            background: #fff !important;
        }

        .sig-col { text-align: center; width: 19%; }
        .sig-label { font-weight: normal; margin-bottom: 4px; display: block; font-size: 16px; }
        .sig-name { font-size: 16px; display: block; }

        .print-date-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            font-size: 11px;
            color: #aaa;
            opacity: 0.5;
            z-index: 9999;
            padding-left: 2px;
        }

        @media print {
            .no-print {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body { -webkit-print-color-adjust: exact; margin: 0; padding: 0; }

            /* الاعتماد على هوامش @page فقط */
            .report-content {
                margin: 0;
                padding: 1px 0;
            }

            /* منع نقل العنوان والجدول لصفحة جديدة */
            .report-content > div:first-child {
                page-break-after: avoid;
            }

            /* تجنب بدء الجدول في صفحة جديدة بدون سبب */
            table {
                page-break-before: avoid;
            }

            /* منع انقسام المجموع والتواقيع */
            .footer-safe-zone {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
            }

            .sigs-area {
                page-break-inside: avoid !important;
            }

            tbody tr:last-child {
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

<!-- شريط التحكم (لا يطبع) -->
<div class="no-print" style="background-color: #f8f9fa; padding: 12px; border-bottom: 2px solid #007bff; display: flex; gap: 10px; justify-content: flex-start; direction: rtl;">
    <button onclick="goBackAndClear()" class="btn" style="background-color: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
        ✅ العودة إلى صفحة الإنشاء (مع حذف البيانات)
    </button>
    <button onclick="confirmAndPrint()" class="btn" style="background-color: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
        🖨️ تأكيد الطباعة والأرشفة
    </button>
    <button onclick="window.close()" class="btn" style="background-color: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
        ❌ إغلاق الصفحة
    </button>
</div>

<div class="report-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 0 5px; direction: ltr;">
        <div style="flex: 1; text-align: left; font-size: 18px; direction: rtl;">رقم الكشف: {{ arabicNumbers($reportNumber ?? ($payrolls->first()->kashf_no ?? $payrolls->first()->receipt_no ?? '-')) }}</div>
        <div style="flex: 1; text-align: center; font-size: 24px;">كشف صرف الإيفادات</div>
        <div style="flex: 1;"></div>
    </div>

    {{-- تاريخ الطباعة في الهامش السفلي --}}
    <div class="print-date-footer">تأريخ الطباعة: {{ formatDateArabic(date('Y-m-d')) }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">ت</th>
                <th style="width: 180px;">الاسم والقسم</th>
                <th style="width: 100px;">العنوان الوظيفي</th>
                <th style="width: 120px;">جهة الإيفاد</th>
                <th style="width: 100px;"> رقم وتاريخ الأمر الإداري </th>
                <th style="width: 80px;">فترة الإيفاد</th>
                <th style="width: 35px;">عدد الأيام</th>
                <th style="width: 70px;">مبلغ إيفاد اليوم الواحد</th>
                <th style="width: 70px;">مبلغ المبيت</th>
                <th style="width: 70px;">مبالغ الوصولات</th>
                <th style="width: 90px;">المجموع</th>
                <th style="width: 80px;">التوقيع</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSum = 0;
                $totalDataRows = $payrolls->count();
                $firstPageDataSlots = 20; // الصفة الأولى: 22 - 1 عنوان - 1 headers = 20 بيانة

                // حساب إذا كانت التواقيع ستنقسم عن الجدول
                $needsExpansion = false;
                $expandFromRow = -1;

                if ($totalDataRows > $firstPageDataSlots) {
                    // التواقيع ستكون في صفحة منفصلة
                    $remainingAfterFirstPage = $totalDataRows - $firstPageDataSlots;
                    $otherPagesSlots = 21; // الصفحات التالية: 22 - 1 headers = 21 بيانة

                    $dataRowsInLastPage = $remainingAfterFirstPage % $otherPagesSlots;
                    if ($dataRowsInLastPage == 0) {
                        $dataRowsInLastPage = $otherPagesSlots;
                    }

                    // الصفحة الأخيرة: بيانات + مجموع + فراغ(1) + توقيع(4) = ≤ 22
                    $totalRowsLastPage = $dataRowsInLastPage + 1 + 1 + 4;

                    if ($totalRowsLastPage > 22) {
                        // التواقيع ستنقسم - نوسع الصفوف
                        $needsExpansion = true;
                        $expandFromRow = $totalDataRows - $dataRowsInLastPage + 1;
                    }
                }
            @endphp
            @foreach($payrolls as $index => $p)
                @php $totalSum += $p->total_amount; @endphp
                <tr class="{{ $p->is_half_allowance ? 'half-allowance-row' : '' }} {{ $needsExpansion && ($index + 1) >= $expandFromRow ? 'expand-row' : '' }}">
                    <td style="background-color: #e5e5e5 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">{{ arabicNumbers($index + 1) }}</td>
                    <td style="text-align: right; padding-right: 5px; font-size: 16px;">
                        {{ $p->name }}<br>
                        <small>{{ $p->department }}</small>
                    </td>
                    <td style="font-size: 16px;">{{ $p->job_title }}</td>
                    <td style="font-size: 16px;">{{ $p->destination }}</td>
                    <td style="font-size: 16px;">{{ arabicNumbers($p->admin_order_no) }}<br>{{ formatDateArabic($p->admin_order_date) }}</td>
                    <td style="font-size: 16px;">{{ formatDateArabic($p->start_date) }}<br>{{ formatDateArabic($p->end_date) }}</td>
                    <td>{{ arabicNumbers($p->days_count) }}</td>
                    <td>{{ arabicNumbers(number_format($p->daily_allowance)) }}</td>
                    <td>{{ arabicNumbers(number_format($p->accommodation_fee * ($p->days_count > 1 ? $p->days_count-1 : 0))) }}</td>
                    <td>{{ arabicNumbers(number_format($p->receipts_amount)) }}</td>
                    <td style="">{{ arabicNumbers(number_format($p->total_amount)) }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="height: 36px !important;">
                <td style="background-color: #e5e5e5 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; text-align: right; padding-right: 10px; font-size: 17px;" colspan="10">المجموع الكلي: {{ tafqit($totalSum) }}</td>
                <td style="background-color: #e5e5e5 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; font-size: 22px;">{{ arabicNumbers(number_format($totalSum)) }}</td>
                <td style="background-color: #e5e5e5 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; font-size: 17px;"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-safe-zone" style="page-break-inside: avoid;">
        <div class="sigs-area">
            <div class="sig-col">
                <span class="sig-name">{{ $currentUserName ?? '................' }}</span>
                <span class="sig-label" style="font-size: 0.9em;">منظم الكشف</span>
            </div>

            @foreach($signatures ?? [] as $signature)
                <div class="sig-col">
                    <span class="sig-name">{{ $signature->name ?? '................' }}</span>
                    <span class="sig-label" style="font-size: 0.9em;">{{ $signature->title }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const confirmPrintEndpoint = "{{ route('payrolls.confirm_print') }}";
    const csrfToken = "{{ csrf_token() }}";
    const payload = {
        kashf_no: "{{ $kashfNo ?? '' }}",
        ids: "{{ $ids ?? '' }}"
    };

    let isPrintConfirmed = false;

    // حذف البيانات والعودة إلى صفحة الإنشاء
    function goBackAndClear() {
        // حذف البيانات المحفوظة من localStorage
        localStorage.removeItem('payroll_draft');
        console.log('🗑️ تم حذف البيانات المحفوظة');

        // العودة إلى صفحة الإنشاء
        window.location.href = "{{ route('payrolls.create') }}";
    }

    // حذف البيانات المحفوظة بعد تحميل الطباعة بنجاح
    window.addEventListener('load', function() {
        // لا نحذف هنا، بل ننتظر حتى يضغط المستخدم على الزر
    });

    async function confirmAndPrint() {
        if (isPrintConfirmed) {
            window.print();
            return;
        }

        try {
            const response = await fetch(confirmPrintEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errorJson = await response.json().catch(function () { return null; });
                const errorMessage = errorJson && errorJson.message ? errorJson.message : 'تعذر تأكيد الطباعة.';
                alert(errorMessage);
                return;
            }

            const result = await response.json();
            if (!result.success) {
                alert(result.message || 'تعذر تأكيد الطباعة.');
                return;
            }

            isPrintConfirmed = true;
            window.print();
        } catch (error) {
            alert('فشل الاتصال بالخادم أثناء تأكيد الطباعة.');
        }
    }

    // عند إغلاق الـ tab أو الرجوع
    window.addEventListener('beforeunload', function() {
        // وضع علامة أن المستخدم يرجع (في الـ parent window)
        if (window.opener && window.opener !== window) {
            window.opener.sessionStorage.setItem('returning_from_print', 'true');
        }
    });
</script>

</body>
</html>
