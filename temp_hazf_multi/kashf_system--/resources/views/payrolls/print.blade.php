
@php
function tafqit($number) {
    if (!is_numeric($number)) return "";

    $units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    $tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانمئة', 'تسعمئة'];

    if ($number == 0) return "صفر";

    $res = "";

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
@endphp




<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 40px; }
        .official-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 80px; height: auto; }
        .content-table { width: 100%; border-collapse: collapse; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 12px; text-align: right; }
        .total-box { background-color: #f2f2f2; font-weight: bold; font-size: 1.1em; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; margin-bottom: 20px;">
            🖨️ طباعة الكشف الرسمي
        </button>
    </div>

    <div class="official-header">
        <div>
            <p>جمهورية العراق</p>
            <p>وزارة: .................</p>
            <p>الدائرة: .................</p>
        </div>
        <div style="text-align: center;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Coat_of_arms_of_Iraq.svg/1200px-Coat_of_arms_of_Iraq.svg.png" class="logo">
            <h4>كشف مخصصات إيفاد</h4>
        </div>
        <div>
            <p>العدد: {{ $payroll->id }} / إيفاد</p>
            <p>التاريخ: {{ date('Y/m/d') }}</p>
        </div>
    </div>

    <table class="content-table">
        <tr>
            <th width="20%">اسم الموفد</th>
            <td width="30%">{{ $payroll->name }}</td>
            <th width="20%">جهة الإيفاد</th>
            <td width="30%">{{ $payroll->destination }}</td>
        </tr>
        <tr>
            <th>عدد الأيام</th>
            <td>{{ $payroll->days_count }}</td>
            <th>المبلغ اليومي</th>
            <td>{{ number_format($payroll->daily_allowance) }} د.ع</td>
        </tr>
        <tr class="total-box">
            <th>المبلغ الإجمالي</th>
            <td colspan="3">
                <strong>{{ tafqit($payroll->total_amount) }}</strong>
                <br>
                <small>(مبلغ إجمالي قدره: {{ number_format($payroll->total_amount) }} دينار)</small>
            </td>
        </tr>
    </table>

    <div style="margin-top: 30px; display: flex; justify-content: space-around; text-align: center; direction: rtl;">
        <!-- التوقيع الأول: المستخدم الحالي - منظم الكشف -->
        <div>
            <p style="margin-bottom: 4px;">
                @isset($currentUserName)
                    {{ $currentUserName }}
                @else
                    ................
                @endisset
            </p>
            <p style="margin-top: 6px; min-width: 150px; font-size: 0.9em;">منظم الكشف</p>
        </div>

        <!-- التواقيع الأربعة من قاعدة البيانات بالترتيب المحدد -->
        @foreach($signatures ?? [] as $signature)
            <div>
                <p style="margin-bottom: 4px;">{{ $signature->name ?? '................' }}</p>
                <p style="margin-top: 6px; min-width: 150px; font-size: 0.9em;">
                    {{ $signature->title }}
                </p>
            </div>
        @endforeach
    </div>

</body>
</html>
