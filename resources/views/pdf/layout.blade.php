<!DOCTYPE html>
<html lang="{{ $lang ?? 'ar' }}" dir="{{ ($lang ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: normal;
            src: url('{{ public_path("fonts/Cairo-Regular.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: bold;
            src: url('{{ public_path("fonts/Cairo-Bold.ttf") }}') format('truetype');
        }

        @page {
            margin: 15mm 12mm 20mm 12mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Cairo', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            direction: {{ ($lang ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ ($lang ?? 'ar') === 'ar' ? 'right' : 'left' }};
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .logo-img {
            max-width: 180px;
            max-height: 60px;
        }

        .company-legal-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            margin: 0 0 4px 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }

        .meta-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .meta-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            vertical-align: top;
        }

        .meta-title {
            font-size: 11px;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .meta-row {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .meta-label {
            color: #6b7280;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table th {
            background: #18181b;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 10px;
            border: 1px solid #18181b;
            text-align: {{ ($lang ?? 'ar') === 'ar' ? 'right' : 'left' }};
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            vertical-align: top;
        }

        .items-table tbody tr {
            page-break-inside: avoid;
        }

        .items-table tr:nth-child(even) td {
            background: #fbfcfd;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .totals-table td {
            padding: 6px 10px;
        }

        .grand-total-row td {
            background: #18181b;
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
        }

        .terms-box {
            background: #f9fafb;
            border-left: 3px solid #64D21E;
            padding: 10px 14px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 10px;
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .brand-green { color: #64D21E; }
    </style>
</head>
<body>

    @yield('content')

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT} | Three Digit Software ERP";
            $font = $fontMetrics->get_font("Cairo, sans-serif", "normal");
            $pdf->page_text(230, 815, $text, $font, 8, array(0.5, 0.5, 0.5));
        }
    </script>
</body>
</html>
