@extends('pdf.layout', ['lang' => 'ar', 'title' => 'Client Statement - ' . $client->display_name])

@section('content')
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <div style="font-size: 20px; font-weight: 900; color: #111827;">
                    THREEDIGIT <span style="color: #64D21E;">SOFTWARE</span>
                </div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                    {{ $companySettings->legal_company_name ?: $companySettings->brand_name }}
                </div>
            </td>
            <td style="width: 50%; text-align: left; vertical-align: middle;">
                <h2 style="font-size: 22px; margin: 0; color: #111827;">
                    كشف حساب عميل
                </h2>
                <div style="font-size: 11px; color: #4b5563; margin-top: 3px;">
                    الفترة: {{ $fromDate ?: 'بداية التعامل' }} إلى {{ $toDate ?: now()->format('Y-m-d') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info & Summary Cards -->
    <table class="meta-box">
        <tr>
            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">بيانات العميل</div>
                <div class="meta-row">
                    <span class="meta-label">الاسم:</span>
                    <strong>{{ $client->display_name }}</strong>
                </div>
                @if(!empty($client->tax_number))
                    <div class="meta-row">
                        <span class="meta-label">الرقم الضريبي:</span>
                        <span>{{ $client->tax_number }}</span>
                    </div>
                @endif
                @if(!empty($client->billing_address))
                    <div class="meta-row">
                        <span class="meta-label">العنوان:</span>
                        <span>{{ $client->billing_address }}</span>
                    </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">الهاتف:</span>
                    <span>{{ $client->phone ?: '—' }}</span>
                </div>
            </td>

            <td style="width: 4%;"></td>

            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">ملخص الحساب المالي</div>
                <div class="meta-row">
                    <span class="meta-label">إجمالي الفواتير الصادرة:</span>
                    <strong>${{ number_format($totalInvoiced, 2) }}</strong>
                </div>
                <div class="meta-row">
                    <span class="meta-label">إجمالي المسدد والمقبوض:</span>
                    <strong style="color: #15803d;">${{ number_format($totalPaid, 2) }}</strong>
                </div>
                @if($totalCredits > 0)
                    <div class="meta-row">
                        <span class="meta-label">إجمالي الإشعارات الدائنة:</span>
                        <strong style="color: #b91c1c;">${{ number_format($totalCredits, 2) }}</strong>
                    </div>
                @endif
                <div class="meta-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #e5e7eb; font-size: 13px;">
                    <span class="meta-label">الرصيد القائم المؤكد:</span>
                    <strong style="color: {{ $outstandingBalance > 0 ? '#b91c1c' : '#15803d' }};">
                        ${{ number_format($outstandingBalance, 2) }} USD
                    </strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Chronological Transactions Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">التاريخ</th>
                <th style="width: 15%;">نوع المستند</th>
                <th style="width: 23%;">رقم المستند / البيان</th>
                <th style="width: 16%; text-align: left;">مدين (+)</th>
                <th style="width: 16%; text-align: left;">دائن (-)</th>
                <th style="width: 18%; text-align: left;">الرصيد المتحرك</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statementRows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td><span class="badge {{ $row['badge_class'] }}">{{ $row['type'] }}</span></td>
                    <td>
                        <strong>{{ $row['reference'] }}</strong>
                        @if(!empty($row['notes']))
                            <div style="font-size: 9px; color: #6b7280;">{{ $row['notes'] }}</div>
                        @endif
                    </td>
                    <td style="text-align: left; font-weight: bold; color: {{ $row['debit'] > 0 ? '#111827' : '#9ca3af' }};">
                        {{ $row['debit'] > 0 ? '$' . number_format($row['debit'], 2) : '—' }}
                    </td>
                    <td style="text-align: left; font-weight: bold; color: {{ $row['credit'] > 0 ? '#15803d' : '#9ca3af' }};">
                        {{ $row['credit'] > 0 ? '$' . number_format($row['credit'], 2) : '—' }}
                    </td>
                    <td style="text-align: left; font-weight: bold; color: {{ $row['balance'] > 0 ? '#b91c1c' : '#111827' }};">
                        ${{ number_format($row['balance'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6b7280; padding: 20px;">
                        لا توجد حركات مسجلة خلال الفترة المحددة.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $companySettings->document_footer }}
    </div>
@endsection
