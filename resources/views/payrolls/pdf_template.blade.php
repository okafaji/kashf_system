<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; direction: rtl; font-size: 10px; margin: 0; padding: 0; position: relative; }
        @page { margin: 10mm; }

        .header { width: 100%; margin-bottom: 20px; text-align: center; }
        .header table { width: 100%; border: none; }

        table.main-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.main-table th, table.main-table td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }

        .footer-section { margin-top: 10px; width: 100%; }
        .total-box { border: 2px solid #000; background: #eee; padding: 8px; font-weight: bold; margin-bottom: 20px; }

        .sigs-table { width: 100%; margin-top: 60px; border: none; }
        .sigs-table td { border: none !important; text-align: center; width: 20%; }
        .sig-line { border-top: 1px dashed #000; margin-top: 40px; display: block; width: 80%; margin-left: auto; margin-right: auto; }

        .print-date-footer { position: fixed; bottom: 5mm; left: 0mm; font-size: 8px; color: #999; opacity: 0.6; }
    </style>
</head>
<body>

    {{-- الترويسة تظهر مرة واحدة فقط في بداية الـ PDF --}}
    <div class="header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div style="flex: 1; text-align: right; font-weight: bold;">رقم الكشف: {{ 100 + ($payrolls->first()->id ?? 0) }}</div>
            <div style="flex: 1; text-align: center; font-size: 20px; font-weight: bold;">كشف صرف الإيفادات</div>
            <div style="flex: 1;"></div>
        </div>
    </div>

    {{-- تاريخ الطباعة في الهامش السفلي --}}
    <div class="print-date-footer">تأريخ الطباعة: {{ date('Y/m/d') }}</div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 25px;">ت</th>
                <th style="width: 140px;">الاسم والقسم</th>
                <th>العنوان</th>
                <th>الجهة</th>
                <th style="width: 90px;">الأمر</th>
                <th style="width: 80px;">الفترة</th>
                <th style="width: 30px;">أيام</th>
                <th>يومية</th>
                <th>مبيت</th>
                <th>وصل</th>
                <th style="width: 80px;">المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: right;">{{ $p->name }}</td>
                    <td>{{ $p->job_title }}</td>
                    <td>{{ $p->destination }}</td>
                    <td style="font-size: 8px;">{{ $p->admin_order_no }}</td>
                    <td style="font-size: 8px;">{{ \Carbon\Carbon::parse($p->start_date)->format('Y/m/d') }}</td>
                    <td>{{ $p->days_count }}</td>
                    <td>{{ number_format($p->daily_allowance) }}</td>
                    <td>{{ number_format($p->accommodation_fee) }}</td>
                    <td>{{ number_format($p->receipts_amount) }}</td>
                    <td style="font-weight: bold;">{{ number_format($p->total_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- المجموع والتواقيع --}}
    <div class="footer-section">
        <div class="total-box">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="text-align: right; border: none;">المجموع النهائي للكشف:</td>
                    <td style="text-align: left; border: none;">{{ number_format($totalSum) }} دينار عراقي</td>
                </tr>
            </table>
        </div>

        <table class="sigs-table">
            <tr>
                @foreach(['منظم الكشف', 'مسؤول الوحدة', 'مسؤول الشعبة', 'دائرة التدقيق', 'رئيس القسم المالي'] as $title)
                    <td>
                        <b style="text-decoration: underline;">{{ $title }}</b><br>
                        <span class="sig-line"></span>
                        <span>{{ $sigs[$title]->name ?? '' }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

</body>
</html>
