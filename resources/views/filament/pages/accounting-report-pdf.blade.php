<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Three Digit Software - التقرير المالي</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            direction: rtl;
            text-align: right;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #d97706;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #111;
            font-size: 18px;
        }
        .header p {
            margin: 4px 0 0;
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: right;
        }
        th {
            background-color: #f8f9fa;
            color: #111;
            font-weight: bold;
        }
        .summary-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .text-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Three Digit Software</h1>
        <p>التقرير المحاسبي الرسمي - تاريخ الاستخراج: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    @if($activeTab === 'cash_statement')
        <div class="summary-box">
            <strong>ملخص حركة الصندوق:</strong>
            وارد: ${{ number_format($cashInflows, 2) }} | صادر: ${{ number_format($cashOutflows, 2) }} | الصافي: ${{ number_format($cashNet, 2) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>رقم القيد</th>
                    <th>وارد (USD)</th>
                    <th>صادر (USD)</th>
                    <th>البيان</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashLines as $l)
                <tr>
                    <td>{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $l->journalEntry->entry_number }}</td>
                    <td class="text-emerald">{{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}</td>
                    <td class="text-rose">{{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}</td>
                    <td>{{ $l->description ?: $l->journalEntry->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($activeTab === 'partner_statement' && $selectedPartner)
        <div class="summary-box">
            <strong>كشف حساب الشريك: {{ $selectedPartner->name_ar }}</strong><br>
            رصيد الدين المؤكد عند الانتقال: ${{ number_format($selectedPartner->initial_debt_balance, 2) }} |
            صافي الدين المتبقي المستحق على الشركة: ${{ number_format($selectedPartner->calculated_debt_balance, 2) }} |
            إجمالي السحوبات والتوزيعات: ${{ number_format($selectedPartner->total_drawings, 2) }}
        </div>

        <h3>حركات دين الشريك (تمويل، مصاريف شخصية، سداد)</h3>
        <table>
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>رقم القيد</th>
                    <th>سداد مسترد (مدين)</th>
                    <th>تمويل / مصروف (دائن)</th>
                    <th>البيان</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partnerDebtLines as $l)
                <tr>
                    <td>{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $l->journalEntry->entry_number }}</td>
                    <td class="text-emerald">{{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}</td>
                    <td class="text-rose">{{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}</td>
                    <td>{{ $l->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>المشروع</th>
                    <th>العميل</th>
                    <th>قيمة العقد</th>
                    <th>المقبوض فعلياً</th>
                    <th>التكاليف المباشرة</th>
                    <th>هامش الربح</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->client?->name ?: '-' }}</td>
                    <td>${{ number_format((float)$p->total_price, 2) }}</td>
                    <td class="text-emerald">${{ number_format($p->total_collected, 2) }}</td>
                    <td class="text-rose">${{ number_format((float)$p->direct_costs, 2) }}</td>
                    <td>${{ number_format($p->net_profit, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        تم استخراج هذا التقرير آلياً من نظام المحاسبة الداخلي لشركة Three Digit Software. كافة القيود مسجلة على قاعدة البيانات.
    </div>
</body>
</html>
