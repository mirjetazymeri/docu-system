<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('application_testing_pdf.page_title', ['application' => $testing->application->name]) }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #26344d;
            background: #f4f6f9;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .header {
            width: 100%;
            padding: 10px 0;
            color: #fff;
            background: #22304d;
            text-align: center;
        }

        .logo {
            width: 100px;
        }

        .content {
            padding: 25px 30px 65px;
        }

        .report-heading {
            margin-bottom: 17px;
            text-align: center;
        }

        .report-kicker {
            margin-bottom: 5px;
            color: #567cc4;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .report-title {
            margin: 0;
            color: #22304d;
            font-size: 24px;
            font-weight: bold;
            line-height: 1.2;
        }

        .report-subtitle {
            margin-top: 5px;
            color: #718096;
            font-size: 11px;
        }

        .meta-table,
        .summary-table,
        .test-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            margin-bottom: 13px;
            background: #fff;
            border: 1px solid #dce3ed;
        }

        .meta-table td {
            width: 25%;
            padding: 9px 11px;
            border-right: 1px solid #e5eaf1;
        }

        .meta-table td:last-child {
            border-right: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 2px;
            color: #7b8799;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .meta-value {
            color: #273750;
            font-size: 11px;
            font-weight: bold;
        }

        .summary-table {
            margin-bottom: 21px;
            border-spacing: 6px 0;
            border-collapse: separate;
        }

        .summary-table td {
            width: 25%;
            padding: 9px 10px;
            background: #fff;
            border: 1px solid #dce3ed;
            text-align: center;
        }

        .summary-number {
            display: block;
            color: #22304d;
            font-size: 17px;
            font-weight: bold;
            line-height: 1.1;
        }

        .summary-label {
            display: block;
            margin-top: 3px;
            color: #7a8798;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-section {
            margin-bottom: 19px;
        }

        .category-heading {
            width: 100%;
            margin-bottom: 7px;
            padding: 8px 11px;
            color: #fff;
            page-break-after: avoid;
        }

        .category-heading table {
            width: 100%;
            border-collapse: collapse;
        }

        .category-heading td {
            color: #fff;
            vertical-align: middle;
        }

        .category-name {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: .5px;
        }

        .category-description {
            font-size: 8px;
            opacity: .9;
            text-align: right;
        }

        .category-ui {
            background: #4169a8;
        }

        .category-ux {
            background: #795b95;
        }

        .category-functionality {
            background: #2f7182;
        }

        .category-lighthouse {
            background: #b8672f;
        }

        .test-table {
            background: #fff;
            border: 1px solid #dce3ed;
        }

        .test-table thead {
            display: table-header-group;
        }

        .test-table tr {
            page-break-inside: avoid;
        }

        .test-table th {
            padding: 7px 8px;
            color: #657287;
            background: #edf1f6;
            border-right: 1px solid #dce3ed;
            border-bottom: 1px solid #d5dde8;
            font-size: 8px;
            letter-spacing: .45px;
            text-align: left;
            text-transform: uppercase;
        }

        .test-table td {
            padding: 9px 8px;
            color: #344258;
            border-right: 1px solid #e3e8ef;
            border-bottom: 1px solid #e3e8ef;
            vertical-align: top;
        }

        .test-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .test-table th:last-child,
        .test-table td:last-child {
            border-right: 0;
        }

        .test-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .column-number {
            width: 8%;
            text-align: center !important;
        }

        .column-unit {
            width: 20%;
        }

        .column-test {
            width: 24%;
        }

        .column-result {
            width: 15%;
        }

        .column-comment {
            width: 33%;
        }

        .unit-name {
            color: #263750;
            font-weight: bold;
        }

        .test-number {
            color: #8490a1;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            min-width: 67px;
            padding: 4px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .status-passed {
            color: #17614d;
            background: #dff3eb;
            border: 1px solid #b7dfd0;
        }

        .status-not-passed {
            color: #8d302b;
            background: #fae7e5;
            border: 1px solid #efc3bf;
        }

        .status-in-process {
            color: #805a16;
            background: #fff3d6;
            border: 1px solid #edd79e;
        }

        .empty-comment {
            color: #9aa4b2;
            font-style: italic;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 12px 0;
            color: #fff;
            background: #22304d;
            font-size: 9px;
            text-align: center;
        }

        .documentation-credit {
            position: fixed;
            right: 30px;
            bottom: 55px;
            width: 55%;
            padding: 0;
            color: #66748a;
            background: transparent;
            font-size: 8px;
            text-align: right;
        }

        .documentation-credit strong {
            color: #33445f;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    @php
        $passedCount = $testingDetails->where('result', 'passed')->count();
        $notPassedCount = $testingDetails->where('result', 'not_passed')->count();
        $inProcessCount = $testingDetails->where('result', 'in_process')->count();
        $categorySettings = [
            'UI' => [
                'class' => 'category-ui',
                'label' => __('application_testing_pdf.category_ui'),
                'description' => __('application_testing_pdf.category_ui_description'),
            ],
            'UX' => [
                'class' => 'category-ux',
                'label' => __('application_testing_pdf.category_ux'),
                'description' => __('application_testing_pdf.category_ux_description'),
            ],
            'FUNCTIONALITY' => [
                'class' => 'category-functionality',
                'label' => __('application_testing_pdf.category_functionality'),
                'description' => __('application_testing_pdf.category_functionality_description'),
            ],
            'LIGHTHOUSE' => [
                'class' => 'category-lighthouse',
                'label' => __('application_testing_pdf.category_lighthouse'),
                'description' => __('application_testing_pdf.category_lighthouse_description'),
            ],
        ];
        $resultLabels = [
            'passed' => __('application_testing_pdf.passed'),
            'not_passed' => __('application_testing_pdf.not_passed'),
            'in_process' => __('application_testing_pdf.in_process'),
        ];
        $assignedUsers = $testing->application->users
            ->map(fn ($user) => trim($user->first_name . ' ' . $user->last_name))
            ->filter()
            ->implode(', ');
    @endphp

    <div class="header">
        <img src="{{ public_path('frontend/assets/images/2logo.png') }}"
            alt="{{ __('application_testing_pdf.logo_alt') }}" class="logo">
    </div>

    <div class="content">
        <div class="report-heading">
            <div class="report-kicker">{{ __('application_testing_pdf.report_kicker') }}</div>
            <h1 class="report-title">{{ $testing->application->name }}</h1>
            <div class="report-subtitle">{{ __('application_testing_pdf.report_subtitle') }}</div>
        </div>

        <table class="meta-table">
            <tr>
                <td>
                    <span class="meta-label">{{ __('application_testing_pdf.application') }}</span>
                    <span class="meta-value">{{ $testing->application->name }}</span>
                </td>
                <td>
                    <span class="meta-label">{{ __('application_testing_pdf.version') }}</span>
                    <span class="meta-value">{{ $testing->version }}</span>
                </td>
                <td>
                    <span class="meta-label">{{ __('application_testing_pdf.tester') }}</span>
                    <span class="meta-value">{{ $testing->tester_name }}</span>
                </td>
                <td>
                    <span class="meta-label">{{ __('application_testing_pdf.test_date') }}</span>
                    <span class="meta-value">{{ optional($testing->test_date)->format('d.m.Y') }}</span>
                </td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td>
                    <span class="summary-number">{{ $testingDetails->count() }}</span>
                    <span class="summary-label">{{ __('application_testing_pdf.total') }}</span>
                </td>
                <td>
                    <span class="summary-number">{{ $passedCount }}</span>
                    <span class="summary-label">{{ __('application_testing_pdf.passed') }}</span>
                </td>
                <td>
                    <span class="summary-number">{{ $notPassedCount }}</span>
                    <span class="summary-label">{{ __('application_testing_pdf.not_passed') }}</span>
                </td>
                <td>
                    <span class="summary-number">{{ $inProcessCount }}</span>
                    <span class="summary-label">{{ __('application_testing_pdf.in_process') }}</span>
                </td>
            </tr>
        </table>

        @foreach($categorySettings as $category => $settings)
            @php
                $categoryDetails = $testingDetails->where('category', $category);
            @endphp

            @if($categoryDetails->isNotEmpty())
                <section class="category-section">
                    <div class="category-heading {{ $settings['class'] }}">
                        <table>
                            <tr>
                                <td class="category-name">{{ $settings['label'] }}</td>
                                <td class="category-description">
                                    {{ $settings['description'] }} ·
                                    {{ trans_choice('application_testing_pdf.test_cases_count', $categoryDetails->count(), ['count' => $categoryDetails->count()]) }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="test-table">
                        <thead>
                            <tr>
                                <th class="column-number">#</th>
                                <th class="column-unit">{{ __('application_testing_pdf.module') }}</th>
                                <th class="column-test">{{ __('application_testing_pdf.test_case') }}</th>
                                <th class="column-result">{{ __('application_testing_pdf.result') }}</th>
                                <th class="column-comment">{{ __('application_testing_pdf.comment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryDetails as $detail)
                                <tr>
                                    <td class="column-number test-number">{{ $detail->reference }}</td>
                                    <td class="column-unit">
                                        <span class="unit-name">
                                            {{ optional($detail->unit)->name ?? __('application_testing_pdf.unknown_module') }}
                                        </span>
                                    </td>
                                    <td class="column-test">{{ $detail->test_case }}</td>
                                    <td class="column-result">
                                        <span class="status status-{{ str_replace('_', '-', $detail->result) }}">
                                            {{ $resultLabels[$detail->result] ?? ucfirst($detail->result) }}
                                        </span>
                                    </td>
                                    <td class="column-comment">
                                        @if(filled($detail->comment))
                                            {{ $detail->comment }}
                                        @else
                                            <span class="empty-comment">{{ __('application_testing_pdf.no_comment') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif
        @endforeach
    </div>

    @if($assignedUsers)
        <div class="documentation-credit">
            {{ __('application_testing_pdf.documented_by') }}
            <strong>{{ $assignedUsers }}</strong>
        </div>
    @endif

    <div class="footer">
        <p>© {{ now()->year }} Documentation-system. {{ __('application_testing_pdf.rights_reserved') }}</p>
    </div>
</body>
</html>
