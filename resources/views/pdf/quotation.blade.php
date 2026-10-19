@extends('pdf.layout', ['lang' => $quotation->document_language ?? 'ar', 'title' => 'Quotation ' . $quotation->quotation_number])

@php
    $isRtl = ($quotation->document_language ?? 'ar') === 'ar';
@endphp

@section('content')
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <div style="font-size: 20px; font-weight: 900; color: #111827;">
                    THREEDIGIT <span style="color: #64D21E;">SOFTWARE</span>
                </div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                    {{ $companySettings->brand_name }}
                </div>
            </td>
            <td style="width: 50%; text-align: {{ $isRtl ? 'left' : 'right' }}; vertical-align: middle;">
                <h2 style="font-size: 22px; margin: 0; color: #111827; text-transform: uppercase;">
                    {{ $isRtl ? 'عرض سعر رسمي' : 'FORMAL QUOTATION' }}
                </h2>
                <div style="font-size: 13px; font-weight: bold; color: #374151; margin-top: 3px;">
                    #{{ $quotation->quotation_number }}
                </div>
                <div style="margin-top: 5px;">
                    <span class="badge badge-info">{{ strtoupper($quotation->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details -->
    <table class="meta-box">
        <tr>
            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">{{ $isRtl ? 'مقدَّم من' : 'PREPARED BY' }}</div>
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'الشركة:' : 'Company:' }}</span>
                    <strong>{{ $companySettings->legal_company_name ?: $companySettings->brand_name }}</strong>
                </div>
                @if(!empty($companySettings->tax_number))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'الرقم الضريبي:' : 'Tax Number:' }}</span>
                        <span>{{ $companySettings->tax_number }}</span>
                    </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'التواصل:' : 'Contact:' }}</span>
                    <span>{{ $companySettings->email }} | {{ $companySettings->phone }}</span>
                </div>
            </td>

            <td style="width: 4%;"></td>

            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">{{ $isRtl ? 'مقدَّم إلى' : 'PREPARED FOR' }}</div>
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'العميل:' : 'Client:' }}</span>
                    <strong>{{ $quotation->client->display_name }}</strong>
                </div>
                @if(!empty($quotation->client->tax_number))
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'الرقم الضريبي:' : 'Client Tax ID:' }}</span>
                        <span>{{ $quotation->client->tax_number }}</span>
                    </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">{{ $isRtl ? 'تاريخ العرض:' : 'Date:' }}</span>
                    <span>{{ $quotation->issue_date->format('Y-m-d') }}</span>
                    &nbsp;|&nbsp;
                    <span class="meta-label">{{ $isRtl ? 'صالح حتى:' : 'Valid Until:' }}</span>
                    <strong style="color: #111827;">{{ $quotation->valid_until->format('Y-m-d') }}</strong>
                </div>
                @if($quotation->project)
                    <div class="meta-row">
                        <span class="meta-label">{{ $isRtl ? 'المشروع:' : 'Project:' }}</span>
                        <span>{{ $quotation->project->name }}</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Project Scope & Deliverables (if present) -->
    @if(!empty($quotation->scope) || !empty($quotation->deliverables))
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px; margin-bottom: 20px;">
            @if(!empty($quotation->scope))
                <div style="margin-bottom: 8px;">
                    <strong style="color: #111827; font-size: 11px;">{{ $isRtl ? 'نطاق العمل (Project Scope):' : 'Project Scope:' }}</strong>
                    <div style="font-size: 10px; color: #4b5563; margin-top: 2px;">{!! nl2br(e($quotation->scope)) !!}</div>
                </div>
            @endif
            @if(!empty($quotation->deliverables))
                <div>
                    <strong style="color: #111827; font-size: 11px;">{{ $isRtl ? 'المخرجات والتسليمات (Deliverables):' : 'Deliverables:' }}</strong>
                    <div style="font-size: 10px; color: #4b5563; margin-top: 2px;">{!! nl2br(e($quotation->deliverables)) !!}</div>
                </div>
            @endif
        </div>
    @endif

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">{{ $isRtl ? 'الخدمة / النطاق' : 'Service / Scope' }}</th>
                <th style="width: 10%; text-align: center;">{{ $isRtl ? 'الكمية' : 'Qty' }}</th>
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'سعر الوحدة' : 'Unit Price' }}</th>
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'الخصم' : 'Discount' }}</th>
                <th style="width: 15%; text-align: {{ $isRtl ? 'left' : 'right' }};">{{ $isRtl ? 'المجموع' : 'Total' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #111827;">{{ $item->service_name }}</strong>
                        @if(!empty($item->description))
                            <div style="font-size: 10px; color: #4b5563; margin-top: 2px;">
                                {!! nl2br(e($item->description)) !!}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                        {{ (float)$item->discount_amount > 0 ? '-$' . number_format($item->discount_amount, 2) : '—' }}
                    </td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; color: #111827;">
                        ${{ number_format($item->line_total, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                @if(!empty($quotation->payment_schedule))
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px;">
                        <strong style="color: #374151; font-size: 11px;">{{ $isRtl ? 'جدول الدفعات المقترح:' : 'Payment Schedule:' }}</strong>
                        <div style="font-size: 10px; color: #4b5563; margin-top: 3px; white-space: pre-line;">
                            {{ $quotation->payment_schedule }}
                        </div>
                    </div>
                @endif
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 40%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td style="color: #6b7280;">{{ $isRtl ? 'المجموع الفرعي:' : 'Subtotal:' }}</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold;">
                            ${{ number_format($quotation->subtotal, 2) }}
                        </td>
                    </tr>
                    @if((float)$quotation->discount_amount > 0)
                        <tr>
                            <td style="color: #b91c1c;">{{ $isRtl ? 'الخصم الترويجي:' : 'Discount:' }}</td>
                            <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; color: #b91c1c;">
                                -${{ number_format($quotation->discount_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td>{{ $isRtl ? 'إجمالي العرض المقترح:' : 'Total Quotation:' }}</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                            ${{ number_format($quotation->total_amount, 2) }} {{ $quotation->currency }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Terms -->
    @if(!empty($quotation->terms_and_conditions))
        <div class="terms-box">
            <strong style="color: #111827;">{{ $isRtl ? 'الشروط والأحكام:' : 'Terms & Conditions:' }}</strong>
            <span style="color: #4b5563;">{{ $quotation->terms_and_conditions }}</span>
        </div>
    @endif

    <div class="footer">
        {{ $companySettings->document_footer }}
    </div>
@endsection
