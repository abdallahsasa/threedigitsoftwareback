@extends('pdf.layout', ['lang' => 'ar', 'title' => 'Payment Receipt ' . $receipt->receipt_number])

@php
    $snap = $receipt->frozen_receipt_snapshot ?: [
        'receipt_number' => $receipt->receipt_number,
        'payment_number' => $receipt->payment->payment_number,
        'company_name' => $companySettings->legal_company_name ?: $companySettings->brand_name,
        'tax_number' => $companySettings->tax_number,
        'client_name' => $receipt->payment->client->display_name,
        'client_tax_number' => $receipt->payment->client->tax_number,
        'payment_method' => $receipt->payment->payment_method,
        'receiving_account' => $receipt->payment->receivingAccount->name_ar,
        'handled_by' => $receipt->payment->handled_by,
        'reference' => $receipt->payment->reference,
        'notes' => $receipt->payment->notes,
        'amount' => (float)$receipt->amount,
        'currency' => $receipt->currency,
        'allocated_amount' => (float)$receipt->payment->allocated_amount,
        'unallocated_amount' => (float)$receipt->payment->unallocated_amount,
        'allocations' => $receipt->payment->allocations->map(fn($a) => [
            'invoice_number' => $a->invoice->invoice_number,
            'amount' => (float)$a->amount,
        ])->toArray(),
    ];
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
                    {{ $snap['company_name'] }}
                </div>
            </td>
            <td style="width: 50%; text-align: left; vertical-align: middle;">
                <h2 style="font-size: 22px; margin: 0; color: #111827;">
                    سند قبض رسمي
                </h2>
                <div style="font-size: 13px; font-weight: bold; color: #374151; margin-top: 3px;">
                    #{{ $receipt->receipt_number }}
                </div>
                <div style="margin-top: 5px;">
                    <span class="badge badge-success">مقبوض ومعتمد / CONFIRMED</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Receipt Amount Highlight Banner -->
    <div style="background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 25px; text-align: center;">
        <div style="font-size: 12px; color: #15803d; font-weight: bold; margin-bottom: 4px;">
            المبلغ المقبوض / RECEIVED AMOUNT
        </div>
        <div style="font-size: 28px; font-weight: 900; color: #166534;">
            ${{ number_format($receipt->amount, 2) }} {{ $receipt->currency }}
        </div>
    </div>

    <!-- Metadata Table -->
    <table class="meta-box">
        <tr>
            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">بيانات القبض والتحصيل</div>
                <div class="meta-row">
                    <span class="meta-label">تاريخ القبض:</span>
                    <strong>{{ $receipt->receipt_date->format('Y-m-d') }}</strong>
                </div>
                <div class="meta-row">
                    <span class="meta-label">طريقة الدفع:</span>
                    <span>{{ strtoupper($snap['payment_method'] ?? 'CASH') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">الحساب المستلم:</span>
                    <span>{{ $snap['receiving_account'] ?? 'الصندوق' }}</span>
                </div>
                @if(!empty($snap['handled_by']))
                    <div class="meta-row">
                        <span class="meta-label">المستلِم المسؤول:</span>
                        <span>{{ $snap['handled_by'] }}</span>
                    </div>
                @endif
                @if(!empty($snap['reference']))
                    <div class="meta-row">
                        <span class="meta-label">رقم المرجع / الإيصال:</span>
                        <span>{{ $snap['reference'] }}</span>
                    </div>
                @endif
            </td>

            <td style="width: 4%;"></td>

            <td class="meta-card" style="width: 48%;">
                <div class="meta-title">وصلنا من السادة</div>
                <div class="meta-row">
                    <span class="meta-label">العميل:</span>
                    <strong style="color: #111827;">{{ $snap['client_name'] }}</strong>
                </div>
                @if(!empty($snap['client_tax_number']))
                    <div class="meta-row">
                        <span class="meta-label">الرقم الضريبي للعميل:</span>
                        <span>{{ $snap['client_tax_number'] }}</span>
                    </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">رقم حركة الدفع:</span>
                    <span>{{ $snap['payment_number'] }}</span>
                </div>
                @if(!empty($snap['notes']))
                    <div class="meta-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e5e7eb;">
                        <span class="meta-label">البيان / ملاحظات:</span>
                        <span>{{ $snap['notes'] }}</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Allocations Breakdown -->
    @if(!empty($snap['allocations']))
        <div style="margin-bottom: 25px;">
            <div style="font-weight: bold; font-size: 11px; color: #111827; margin-bottom: 8px;">
                تفاصيل تخصيص الدفعة على الفواتير المستحقة:
            </div>
            <table class="items-table" style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 50%;">رقم الفاتورة المخصصة</th>
                        <th style="width: 40%; text-align: left;">المبلغ المسدد من الفاتورة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($snap['allocations'] as $idx => $alloc)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $alloc['invoice_number'] }}</strong></td>
                            <td style="text-align: left; font-weight: bold; color: #15803d;">
                                ${{ number_format($alloc['amount'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if((float)($snap['unallocated_amount'] ?? 0) > 0)
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 14px; margin-bottom: 25px;">
            <strong style="color: #1e40af;">دفعة مقدمة / رصيد عميل غير مخصص:</strong>
            <span style="color: #1e3a8a; font-weight: bold;">
                ${{ number_format($snap['unallocated_amount'], 2) }} {{ $receipt->currency }}
            </span>
            <div style="font-size: 10px; color: #3b82f6; margin-top: 2px;">
                تم تسجيل هذا المبلغ كرصيد دائن لصالح العميل يخصم من الفواتير والمشاريع القادمة.
            </div>
        </div>
    @endif

    <!-- Signature and Stamp Box -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 40px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 11px; font-weight: bold; color: #4b5563; margin-bottom: 45px;">توقيع المستلم</div>
                <div style="border-top: 1px solid #9ca3af; width: 180px; margin: 0 auto; padding-top: 5px; font-size: 10px; color: #6b7280;">
                    قسم الحسابات والمالية
                </div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 11px; font-weight: bold; color: #4b5563; margin-bottom: 45px;">الختم المعتمد</div>
                <div style="border-top: 1px solid #9ca3af; width: 180px; margin: 0 auto; padding-top: 5px; font-size: 10px; color: #6b7280;">
                    {{ $snap['company_name'] }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $companySettings->document_footer }}
    </div>
@endsection
