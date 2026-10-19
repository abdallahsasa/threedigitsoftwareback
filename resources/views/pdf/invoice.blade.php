@extends('pdf.layout', ['lang' => $invoice->document_language ?? 'ar', 'title' => 'Invoice ' . $invoice->invoice_number])

@php
    $isRtl = ($invoice->document_language ?? 'ar') === 'ar';
    $company = $invoice->frozen_company_snapshot ?: [
        'brand_name' => $companySettings->brand_name,
        'legal_company_name' => $companySettings->legal_company_name,
        'tax_number' => $companySettings->tax_number,
        'commercial_registration_number' => $companySettings->commercial_registration_number,
        'registered_address' => $companySettings->registered_address,
        'city' => $companySettings->city,
        'country' => $companySettings->country,
        'phone' => $companySettings->phone,
        'email' => $companySettings->email,
        'website' => $companySettings->website,
        'logo_path' => $companySettings->logo_path,
        'payment_methods_text' => $companySettings->payment_methods_text,
        'default_payment_terms' => $companySettings->default_payment_terms,
        'document_footer' => $companySettings->document_footer,
    ];

    $client = $invoice->frozen_client_snapshot ?: [
        'name' => $invoice->client->name,
        'legal_name' => $invoice->client->legal_name,
        'company_name' => $invoice->client->company_name,
        'tax_number' => $invoice->client->tax_number,
        'billing_address' => $invoice->client->billing_address,
        'city' => $invoice->client->city,
        'country' => $invoice->client->country,
        'phone' => $invoice->client->phone,
        'email' => $invoice->client->email,
    ];

    $items = $invoice->frozen_items_snapshot ?: $invoice->items->map(fn($item) => [
        'service_name' => $item->service_name,
        'description' => $item->description,
        'quantity' => (float)$item->quantity,
        'unit' => $item->unit,
        'unit_price' => (float)$item->unit_price,
        'discount_amount' => (float)$item->discount_amount,
        'tax_rate' => (float)$item->tax_rate,
        'tax_amount' => (float)$item->tax_amount,
        'line_total' => (float)$item->line_total,
    ]);
@endphp

@section('content')
    <!-- Document Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <div style="font-size: 20px; font-weight: 900; color: #111827; letter-spacing: 0.5px;">
                    THREEDIGIT <span style="color: #64D21E;">SOFTWARE</span>
                </div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                    {{ $company['brand_name'] ?? 'Three Digit Software' }}
                </div>
            </td>
            <td style="width: 50%; text-align: {{ $isRtl ? 'left' : 'right' }}; vertical-align: middle;">
                <h2 style="font-size: 22px; margin: 0; color: #111827; text-transform: uppercase;">
                    {{ $isRtl ? 'فاتورة مبيعات' : 'TAX INVOICE' }}
                </h2>
                <div style="font-size: 13px; font-weight: bold; color: #374151; margin-top: 3px;">
                    #{{ $invoice->invoice_number }}
                </div>
                <div style="margin-top: 5px;">
                    @if($invoice->payment_status === 'paid')
                        <span class="badge badge-success">{{ $isRtl ? 'مدفوعة بالكامل' : 'PAID' }}</span>
                    @elseif($invoice->payment_status === 'partially_paid')
                        <span class="badge badge-warning">{{ $isRtl ? 'مدفوعة جزئياً' : 'PARTIALLY PAID' }}</span>
                    @else
                        <span class="badge badge-danger">{{ $isRtl ? 'غير مدفوعة' : 'UNPAID' }}</span>
                    @endif

                    @if($invoice->is_overdue)
                        <span class="badge badge-danger">{{ $isRtl ? 'متأخرة' : 'OVERDUE' }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Prominent Company Legal & Tax Box & Client Info -->
    <table class="meta-box">
        <tr>
            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">{{ $isRtl ? 'بيانات الشركة المصدِرة' : 'ISSUED BY' }}</div>
                
                <!-- Mandatory Dedicated Legal Company Name -->
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'الاسم القانوني للشركة:' : 'Full Legal Company Name:' }}</span>
                    <strong style="color: #111827;">{{ $company['legal_company_name'] ?: ($company['brand_name'] ?? 'Three Digit Software') }}</strong>
                </div>

                <!-- Mandatory Dedicated Company Tax Number -->
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'الرقم الضريبي للشركة (Tax ID):' : 'Company Tax Number / Tax ID:' }}</span>
                    <strong style="color: #111827;">{{ $company['tax_number'] ?: '—' }}</strong>
                </div>

                @if(!empty($company['commercial_registration_number']))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'السجل التجاري:' : 'Commercial Reg No:' }}</span>
                        <span>{{ $company['commercial_registration_number'] }}</span>
                    </div>
                @endif

                @if(!empty($company['registered_address']))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'العنوان المسجل:' : 'Registered Address:' }}</span>
                        <span>{{ $company['registered_address'] }}, {{ $company['city'] ?? '' }} {{ $company['country'] ?? '' }}</span>
                    </div>
                @endif

                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'التواصل:' : 'Contact:' }}</span>
                    <span>{{ $company['email'] ?? '' }} | {{ $company['phone'] ?? '' }}</span>
                </div>
            </td>

            <td style="width: 4%;"></td>

            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">{{ $isRtl ? 'بيانات العميل والفاتورة' : 'BILLED TO & DETAILS' }}</div>
                
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'العميل:' : 'Client:' }}</span>
                    <strong>{{ $client['legal_name'] ?: ($client['company_name'] ?: $client['name']) }}</strong>
                </div>

                @if(!empty($client['tax_number']))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'الرقم الضريبي للعميل:' : 'Client Tax Number:' }}</span>
                        <span>{{ $client['tax_number'] }}</span>
                    </div>
                @endif

                @if(!empty($client['billing_address']))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'عنوان الفوترة:' : 'Billing Address:' }}</span>
                        <span>{{ $client['billing_address'] }}, {{ $client['city'] ?? '' }} {{ $client['country'] ?? '' }}</span>
                    </div>
                @endif

                <div class="meta-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e5e7eb;">
                    <span class="meta-label">{{ $isRtl ? 'تاريخ الإصدار:' : 'Issue Date:' }}</span>
                    <span>{{ $invoice->issue_date->format('Y-m-d') }}</span>
                    &nbsp;|&nbsp;
                    <span class="meta-label">{{ $isRtl ? 'تاريخ الاستحقاق:' : 'Due Date:' }}</span>
                    <span style="{{ $invoice->is_overdue ? 'color: #b91c1c; font-weight: bold;' : '' }}">{{ $invoice->due_date->format('Y-m-d') }}</span>
                </div>

                @if(!empty($invoice->reference))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'مرجع الفاتورة:' : 'Reference / PO:' }}</span>
                        <span>{{ $invoice->reference }}</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Services / Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">{{ $isRtl ? 'الخدمة / الوصف' : 'Service / Description' }}</th>
                <th style="width: 10%; text-align: center;">{{ $isRtl ? 'الكمية' : 'Qty' }}</th>
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'سعر الوحدة' : 'Unit Price' }}</th>
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'الخصم' : 'Discount' }}</th>
                @if((float)$invoice->tax_amount > 0)
                    <th style="width: 10%; text-align: center;">{{ $isRtl ? 'الضريبة' : 'Tax' }}</th>
                @endif
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'المجموع' : 'Total' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #111827;">{{ $item['service_name'] }}</strong>
                        @if(!empty($item['description']))
                            <div style="font-size: 10px; color: #4b5563; margin-top: 3px;">
                                {!! nl2br(e($item['description'])) !!}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($item['quantity'], 2) }} {{ $item['unit'] ?? '' }}</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">${{ number_format($item['unit_price'], 2) }}</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                        @if((float)$item['discount_amount'] > 0)
                            -${{ number_format($item['discount_amount'], 2) }}
                        @else
                            —
                        @endif
                    </td>
                    @if((float)$invoice->tax_amount > 0)
                        <td style="text-align: center;">{{ number_format($item['tax_rate'], 1) }}%</td>
                    @endif
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; color: #111827;">
                        ${{ number_format($item['line_total'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals & Payment Balance Breakdown -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <!-- Payment Instructions -->
                @if(!empty($invoice->payment_instructions) || !empty($company['payment_methods_text']))
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px;">
                        <strong style="color: #374151; font-size: 11px;">{{ $isRtl ? 'تعليمات وبيانات الدفع:' : 'Payment Instructions:' }}</strong>
                        <div style="font-size: 10px; color: #4b5563; margin-top: 4px; white-space: pre-line;">
                            {{ $invoice->payment_instructions ?: ($company['payment_methods_text'] ?? '') }}
                        </div>
                    </div>
                @endif

                @if(!empty($invoice->customer_notes))
                    <div style="font-size: 10px; color: #4b5563; margin-top: 6px;">
                        <strong>{{ $isRtl ? 'ملاحظات:' : 'Notes:' }}</strong> {{ $invoice->customer_notes }}
                    </div>
                @endif
            </td>

            <td style="width: 5%;"></td>

            <td style="width: 40%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td style="color: #6b7280;">{{ $isRtl ? 'المجموع الفرعي:' : 'Subtotal:' }}</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold;">
                            ${{ number_format($invoice->subtotal, 2) }}
                        </td>
                    </tr>

                    @if((float)$invoice->discount_amount > 0)
                        <tr>
                            <td style="color: #b91c1c;">{{ $isRtl ? 'إجمالي الخصم:' : 'Total Discount:' }}</td>
                            <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; color: #b91c1c;">
                                -${{ number_format($invoice->discount_amount, 2) }}
                            </td>
                        </tr>
                    @endif

                    @if((float)$invoice->tax_amount > 0)
                        <tr>
                            <td style="color: #6b7280;">{{ $isRtl ? 'المبلغ الخاضع للضريبة:' : 'Taxable Amount:' }}</td>
                            <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                                ${{ number_format($invoice->taxable_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #6b7280;">{{ $isRtl ? 'ضريبة القيمة المضافة:' : 'Tax / VAT:' }}</td>
                            <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold;">
                                ${{ number_format($invoice->tax_amount, 2) }}
                            </td>
                        </tr>
                    @endif

                    <tr class="grand-total-row">
                        <td>{{ $isRtl ? 'المجموع النهائي:' : 'Final Total:' }}</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                            ${{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}
                        </td>
                    </tr>

                    <tr>
                        <td style="color: #15803d; padding-top: 8px;">{{ $isRtl ? 'المدفوع / المقبوض:' : 'Payments Applied:' }}</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; color: #15803d; padding-top: 8px;">
                            ${{ number_format($invoice->paid_amount, 2) }}
                        </td>
                    </tr>

                    <tr style="border-top: 2px solid #111827;">
                        <td style="font-size: 13px; font-weight: 900; color: #111827; padding-top: 6px;">
                            {{ $isRtl ? 'الرصيد المتبقي:' : 'Balance Due:' }}
                        </td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-size: 13px; font-weight: 900; color: #111827; padding-top: 6px;">
                            ${{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Payment Terms -->
    @if(!empty($invoice->payment_terms) || !empty($company['default_payment_terms']))
        <div class="terms-box">
            <strong style="color: #111827;">{{ $isRtl ? 'شروط السداد والأحكام:' : 'Terms & Conditions:' }}</strong>
            <span style="color: #4b5563;">{{ $invoice->payment_terms ?: ($company['default_payment_terms'] ?? '') }}</span>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        {{ $company['document_footer'] ?? 'Three Digit Software — Web Development, Software Engineering & Digital Solutions' }}
    </div>
@endsection
