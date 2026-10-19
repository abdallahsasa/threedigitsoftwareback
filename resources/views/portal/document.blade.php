<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $doc->title ?? 'مستند مالي' }} | Three Digit Software</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #64D21E;
            --dark-bg: #0b0f19;
            --card-bg: #111827;
            --border-color: #1f2937;
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--dark-bg);
            color: var(--text-main);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 860px;
            margin: 0 auto;
        }

        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .brand-green { color: var(--brand-green); }

        .btn-download {
            background: var(--brand-green);
            color: #000000;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .btn-download:hover { opacity: 0.9; }

        .document-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            margin-bottom: 24px;
        }

        .doc-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .doc-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 6px 0;
        }

        .doc-number {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .badge-warning { background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
        .badge-info { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .meta-box {
            background: #182234;
            border: 1px solid #1e293b;
            border-radius: 10px;
            padding: 16px;
        }

        .meta-box h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .meta-line {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .table-custom th {
            background: #182234;
            color: var(--text-muted);
            padding: 12px 16px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 1px solid var(--border-color);
        }

        .table-custom td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }

        .total-banner {
            background: #182234;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-amount {
            font-size: 26px;
            font-weight: 900;
            color: var(--brand-green);
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Navigation Bar -->
        <div class="header-bar">
            <div class="brand-title">
                THREEDIGIT <span class="brand-green">SOFTWARE</span>
            </div>
            <a href="{{ route('portal.document.download', $share->token) }}" class="btn-download">
                📥 تحميل المستند كملف PDF
            </a>
        </div>

        <!-- Main Document Container -->
        <div class="document-card">
            <div class="doc-title-row">
                <div>
                    <h1 class="doc-title">
                        @if($docType === 'invoice')
                            فاتورة مبيعات رسمية
                        @elseif($docType === 'quotation')
                            عرض سعر رسمي
                        @elseif($docType === 'receipt')
                            سند قبض معتمد
                        @else
                            كشف حساب عميل
                        @endif
                    </h1>
                    <div class="doc-number">
                        #{{ $doc->invoice_number ?? ($doc->quotation_number ?? ($doc->receipt_number ?? $doc->name)) }}
                    </div>
                </div>
                <div>
                    @if($docType === 'invoice')
                        @if($doc->payment_status === 'paid')
                            <span class="badge badge-success">مدفوعة بالكامل</span>
                        @elseif($doc->payment_status === 'partially_paid')
                            <span class="badge badge-warning">مدفوعة جزئياً</span>
                        @else
                            <span class="badge badge-info">غير مدفوعة</span>
                        @endif
                    @elseif($docType === 'receipt')
                        <span class="badge badge-success">مقبوض ومعتمد</span>
                    @endif
                </div>
            </div>

            <!-- Meta Information Grid -->
            <div class="meta-grid">
                <div class="meta-box">
                    <h4>الشركة المصدِرة</h4>
                    <div class="meta-line">
                        <strong>{{ $companySettings->legal_company_name ?: $companySettings->brand_name }}</strong>
                    </div>
                    @if(!empty($companySettings->tax_number))
                        <div class="meta-line">الرقم الضريبي: {{ $companySettings->tax_number }}</div>
                    @endif
                    <div class="meta-line">{{ $companySettings->email }} | {{ $companySettings->phone }}</div>
                </div>

                <div class="meta-box">
                    <h4>بيانات العميل</h4>
                    <div class="meta-line">
                        <strong>{{ $share->client->display_name }}</strong>
                    </div>
                    @if(!empty($share->client->tax_number))
                        <div class="meta-line">الرقم الضريبي: {{ $share->client->tax_number }}</div>
                    @endif
                    @if(!empty($doc->issue_date))
                        <div class="meta-line">تاريخ الإصدار: {{ $doc->issue_date->format('Y-m-d') }}</div>
                    @endif
                    @if(!empty($doc->due_date))
                        <div class="meta-line">تاريخ الاستحقاق: {{ $doc->due_date->format('Y-m-d') }}</div>
                    @endif
                </div>
            </div>

            <!-- Items Details (Invoices & Quotations) -->
            @if(in_array($docType, ['invoice', 'quotation']) && $doc->items)
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>الخدمة / البند</th>
                            <th style="text-align: center;">الكمية</th>
                            <th style="text-align: left;">سعر الوحدة</th>
                            <th style="text-align: left;">المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($doc->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->service_name }}</strong>
                                    @if(!empty($item->description))
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                            {{ $item->description }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                                <td style="text-align: left;">${{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: left; font-weight: bold; color: var(--text-main);">
                                    ${{ number_format($item->line_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Total Banner -->
            <div class="total-banner">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 2px;">
                        {{ $docType === 'invoice' ? 'المبلغ الإجمالي المستحق' : ($docType === 'receipt' ? 'المبلغ المقبوض' : 'إجمالي القيمة') }}
                    </div>
                    @if($docType === 'invoice' && (float)$doc->balance_due > 0)
                        <div style="font-size: 13px; color: #f87171;">
                            المتبقي للدفع: ${{ number_format($doc->balance_due, 2) }} USD
                        </div>
                    @endif
                </div>
                <div class="total-amount">
                    ${{ number_format($doc->total_amount ?? $doc->amount, 2) }} {{ $doc->currency ?? 'USD' }}
                </div>
            </div>

            <!-- Customer Facing Notes -->
            @if(!empty($doc->customer_notes))
                <div style="margin-top: 24px; padding: 14px 18px; background: #182234; border-radius: 8px; font-size: 12px; color: var(--text-muted);">
                    <strong style="color: var(--text-main);">ملاحظات:</strong> {{ $doc->customer_notes }}
                </div>
            @endif
        </div>

        <div class="footer-note">
            {{ $companySettings->document_footer }}
        </div>
    </div>
</body>
</html>
