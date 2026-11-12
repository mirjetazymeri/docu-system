<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('application_testing_report.email_subject', ['application' => $testing->application->name]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fa;color:#25344d;font-family:Arial,Helvetica,sans-serif;">
    @php
        $failedItems = $actionItems->where('result', 'not_passed');
        $inProcessItems = $actionItems->where('result', 'in_process');
        $recipientName = trim($recipient->first_name . ' ' . $recipient->last_name);
    @endphp

    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        {{ __('application_testing_report.preheader', ['application' => $testing->application->name]) }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6fa;">
        <tr>
            <td align="center" style="padding:32px 14px;">
                <table role="presentation" width="680" cellspacing="0" cellpadding="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #dfe6ef;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 30px;background:#25344d;color:#ffffff;">
                            <div style="font-size:12px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#b9c9e5;">
                                {{ __('application_testing_report.report_label') }}
                            </div>
                            <h1 style="margin:8px 0 0;font-size:26px;line-height:1.25;">
                                {{ $testing->application->name }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 30px;">
                            <p style="margin:0 0 12px;font-size:16px;">
                                {{ __('application_testing_report.greeting', ['name' => $recipientName ?: $recipient->email]) }}
                            </p>
                            <p style="margin:0 0 22px;color:#5f6f84;line-height:1.65;">
                                {{ __('application_testing_report.intro', ['application' => $testing->application->name]) }}
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:24px;background:#f7f9fc;border:1px solid #e1e7ef;border-radius:10px;">
                                <tr>
                                    <td style="padding:14px 16px;color:#64748b;font-size:12px;">
                                        {{ __('application_testing_report.version') }}
                                        <strong style="display:block;margin-top:4px;color:#25344d;font-size:15px;">{{ $testing->version }}</strong>
                                    </td>
                                    <td style="padding:14px 16px;color:#64748b;font-size:12px;">
                                        {{ __('application_testing_report.test_date') }}
                                        <strong style="display:block;margin-top:4px;color:#25344d;font-size:15px;">{{ optional($testing->test_date)->format('d.m.Y') }}</strong>
                                    </td>
                                    <td style="padding:14px 16px;color:#64748b;font-size:12px;">
                                        {{ __('application_testing_report.tester') }}
                                        <strong style="display:block;margin-top:4px;color:#25344d;font-size:15px;">{{ $testing->tester_name }}</strong>
                                    </td>
                                </tr>
                            </table>

                            @if($failedItems->isNotEmpty())
                                @include('emails.partials.application-testing-items', [
                                    'items' => $failedItems,
                                    'title' => __('application_testing_report.not_passed_title'),
                                    'titleColor' => '#9a312b',
                                    'titleBackground' => '#fdeeed',
                                ])
                            @endif

                            @if($inProcessItems->isNotEmpty())
                                @include('emails.partials.application-testing-items', [
                                    'items' => $inProcessItems,
                                    'title' => __('application_testing_report.in_process_title'),
                                    'titleColor' => '#895f16',
                                    'titleBackground' => '#fff6dd',
                                ])
                            @endif

                            <div style="margin:24px 0 4px;text-align:center;">
                                <a href="{{ route('testing-issues.index', ['application_id' => $testing->application_id]) }}"
                                    style="display:inline-block;padding:12px 20px;color:#fff;background:#4f78c4;border-radius:9px;font-size:14px;font-weight:700;text-decoration:none;">
                                    {{ __('application_testing_report.open_testing_issues') }}
                                </a>
                            </div>

                            <p style="margin:26px 0 0;padding-top:20px;border-top:1px solid #e6ebf2;color:#65748a;font-size:13px;line-height:1.6;">
                                {{ __('application_testing_report.next_step') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 30px;background:#edf2f8;color:#718096;font-size:12px;text-align:center;">
                            {{ __('application_testing_report.footer') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
