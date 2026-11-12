<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <style>
        /* Define page margins to avoid header and footer overlap */
        @page { margin: 85px 48px 70px; }

        /* Base styles for body */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #273449;
            font-size: 11pt;
            line-height: 1.55;
        }

        .pdf-emoji {
            font-family: 'Noto Emoji PDF';
            font-weight: normal;
        }

        /* Fixed header styles */
        header {
            position: fixed;
            top: -68px;
            left: -10px;
            right: -10px;
            height: 55px;
            background-color: #fff;
            text-align: center;
            line-height: 1.5;
            border-bottom: 1px solid #d8e1ed;
        }

        /* Fixed footer styles */
        footer {
            color: rgb(36, 32, 32);
            position: fixed;
            bottom: -55px;
            left: -10px;
            right: -10px;
            height: 35px;
            background-color: #fff;
            text-align: center;
            line-height: 1.5;
            border-top: 1px solid #d8e1ed;
        }
        .doc-header {
    font-size: 25px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 20px;
}


        /* Main content area styles */
        .content {
            padding: 0;
        }

        /* Header content styles */
        .header p {
            font-size: 14px;
            margin: 0;
            color: #333;
        }

        /* Footer content styles */
        .footer p {
            font-size: 12px;
            color: #666;
        }

        h1, h2, h3, h4 {
            color: #25344d;
            page-break-after: avoid;
        }
        h1 { font-size: 21pt; margin: 0 0 16px; }
        h2 { font-size: 17pt; margin: 18px 0 9px; }
        h3 { font-size: 14pt; margin: 15px 0 7px; }
        h4 { font-size: 12pt; margin: 12px 0 6px; }
        p { margin: 0 0 9px; }
        ul, ol { margin: 5px 0 12px 22px; padding: 0; }
        li { margin-bottom: 4px; }
        table {
            width: 100% !important;
            margin: 12px 0;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        thead { display: table-header-group; }
        tbody { display: table-row-group; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td {
            padding: 7px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        th { color: #25344d; background: #edf2f8; }
        img {
            max-width: 100% !important;
            height: auto !important;
            page-break-inside: avoid;
        }
        blockquote {
            margin: 12px 0;
            padding: 8px 12px;
            color: #475569;
            background: #f6f8fb;
            border-left: 3px solid #4f78c4;
        }
        pre {
            padding: 10px;
            white-space: pre-wrap;
            background: #f1f5f9;
            border: 1px solid #dbe3ee;
        }
        a { color: #4168ae; text-decoration: underline; }
        .document-title {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #4f78c4;
        }
        .document-meta {
            margin-bottom: 18px;
            padding: 10px 12px;
            background: #f4f7fb;
            border: 1px solid #dce4ef;
        }
        .document-content { overflow-wrap: break-word; }
        .pdf-unit-section { margin-bottom: 24px; }
        .pdf-documentation-entry {
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #dce4ef;
        }
        .pdf-documentation-entry h3 {
            margin-top: 8px;
            padding: 7px 9px;
            background: #f3f6fa;
            border-left: 3px solid #4f78c4;
        }
        .pdf-documentation-content {
            overflow-wrap: anywhere;
        }
        .pdf-safe-table {
            margin: 12px 0;
            border: 1px solid #cbd5e1;
            page-break-inside: auto;
        }
        .pdf-safe-table-row {
            padding: 0;
            border-bottom: 1px solid #cbd5e1;
            page-break-inside: auto;
        }
        .pdf-safe-table-row:last-child {
            border-bottom: 0;
        }
        .pdf-safe-table-cell {
            min-height: 18px;
            padding: 6px 8px;
            border-bottom: 1px dotted #d7dee8;
            overflow-wrap: anywhere;
        }
        .pdf-safe-table-cell:last-child {
            border-bottom: 0;
        }
        .pdf-safe-table-heading {
            color: #25344d;
            background: #edf2f8;
            font-weight: bold;
        }

    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="header">
            {{-- <img src="{{ asset('storage/logos/header_aldi.png') }}" alt="Company Logo" style="width: auto; height: 100px;"> --}}
            <img src="{{ public_path('frontend/assets/images/image-2.png') }}" alt="Documentation-system Logo" class="logo" style="width: auto; height: 45px;">
        </div>
    </header>

    <!-- Footer -->
    <footer>
        <div class="footer">
            {{-- <p>{{ __('words.thank_you_for_choosing') }} {{ $company->name }}!</p> --}}
            <p>© {{ date('Y') }} Documentation-system. {{ __('application_testing_pdf.rights_reserved') }}</p>
        </div>
    </footer>

    <!-- Main content area -->
    <div class="content">
        @yield('content')
    </div>
</body>

</html>
