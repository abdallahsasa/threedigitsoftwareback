<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Three Digit Software - Financial Statement</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            direction: ltr;
            text-align: left;
            color: #1f2937;
            margin: 25px;
            line-height: 1.4;
        }
        .header {
            text-align: left;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #111827;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
        }
        .summary-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 12px;
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
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Three Digit Software — Official Financial Statement</h1>
        <p>Report Category: {{ ucwords(str_replace('_', ' ', $activeTab)) }} | Generated on: {{ now()->format('Y-m-d H:i') }} | Currency: USD</p>
    </div>

    @if($activeTab === 'cash_statement')
        <div class="summary-box">
            <strong>Treasury Overview:</strong> Total Inflows: <span class="text-emerald">${{ number_format($cashInflows, 2) }}</span> | 
            Total Outflows: <span class="text-rose">${{ number_format($cashOutflows, 2) }}</span> | 
            <strong>Net Treasury Balance: ${{ number_format($cashNet, 2) }}</strong> (Cutover Baseline: $250.00)
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Debit (Inflow)</th>
                    <th>Credit (Outflow)</th>
                    <th>Description</th>
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
            <strong>Partner: {{ $selectedPartner->name_en }} ({{ $selectedPartner->name_ar }})</strong><br>
            Cutover Verified Debt (13 Oct 2026): <strong>${{ number_format($selectedPartner->initial_debt_balance, 2) }}</strong> | 
            Current Outstanding Balance: <strong>${{ number_format($selectedPartner->calculated_debt_balance, 2) }}</strong> | 
            Total Drawings / Distributions: <strong>${{ number_format($selectedPartner->total_drawings, 2) }}</strong>
        </div>

        <h3>1. Debt & Out-of-Pocket Funding Ledger</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Debit (Repayment)</th>
                    <th>Credit (Funding / Out-of-Pocket)</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partnerDebtLines as $l)
                <tr>
                    <td>{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $l->journalEntry->entry_number }}</td>
                    <td>{{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}</td>
                    <td class="text-bold">{{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}</td>
                    <td>{{ $l->description ?: $l->journalEntry->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Client / Project</th>
                    <th>Total Collected</th>
                    <th>Potential Receivable</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $c)
                <tr>
                    <td class="text-bold">{{ $c->name }}</td>
                    <td class="text-emerald">${{ number_format($c->total_collected, 2) }}</td>
                    <td>${{ number_format($c->potential_receivable_amount, 2) }}</td>
                    <td>{{ $c->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Three Digit Software Internal Accounting System • Confirmed Cutover October 13, 2026 • Confidential Document
    </div>
</body>
</html>
