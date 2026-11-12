<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('testing_issues.worker_email_subject_' . $reviewAction, [
        'application' => $testingIssue->applicationTesting->application->name,
        'reference' => $testingIssue->reference,
    ]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fa;color:#25344d;font-family:Arial,Helvetica,sans-serif;">
    @php
        $application = $testingIssue->applicationTesting->application;
        $recipientName = trim($recipient->first_name . ' ' . $recipient->last_name);
        $reviewerName = $testingIssue->reviewedBy?->full_name
            ?: __('testing_issues.unknown_admin');
        $followUpUrl = route('testing-issues.index', [
            'application_id' => $application->id,
            'fix_status' => $reviewAction === 'verified' ? 'all' : 'active',
        ]) . '#issue-' . $testingIssue->id;
    @endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6fa;">
        <tr>
            <td align="center" style="padding:32px 14px;">
                <table role="presentation" width="650" cellspacing="0" cellpadding="0" style="width:100%;max-width:650px;background:#fff;border:1px solid #dfe6ef;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:23px 28px;background:#25344d;color:#fff;">
                            <div style="color:#b9c9e5;font-size:11px;font-weight:700;letter-spacing:1.1px;text-transform:uppercase;">
                                {{ __('testing_issues.worker_email_kicker_' . $reviewAction) }}
                            </div>
                            <h1 style="margin:7px 0 0;font-size:24px;">{{ $application->name }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:27px 28px;">
                            <p style="margin:0 0 12px;font-size:16px;">
                                {{ __('testing_issues.email_greeting', ['name' => $recipientName ?: $recipient->email]) }}
                            </p>
                            <p style="margin:0 0 21px;color:#617086;line-height:1.6;">
                                {{ __('testing_issues.worker_email_intro_' . $reviewAction, [
                                    'admin' => $reviewerName,
                                    'reference' => $testingIssue->reference,
                                    'application' => $application->name,
                                ]) }}
                            </p>

                            <div style="padding:16px;background:#f7f9fc;border:1px solid #e1e7ef;border-radius:10px;">
                                <div style="margin-bottom:10px;font-size:17px;font-weight:700;">
                                    <span style="color:#4f78c4;">{{ $testingIssue->reference }}</span>
                                    &nbsp;&middot;&nbsp; {{ $testingIssue->test_case }}
                                </div>
                                <div style="margin-bottom:6px;color:#64748b;font-size:13px;">
                                    <strong>{{ __('testing_issues.module') }}:</strong>
                                    {{ $testingIssue->unit?->name ?? __('testing_issues.unknown_module') }}
                                    &nbsp;&middot;&nbsp;
                                    <strong>{{ __('testing_issues.category') }}:</strong>
                                    {{ $testingIssue->category }}
                                </div>
                                <div style="margin-top:12px;color:#4b5d73;font-size:13px;line-height:1.55;">
                                    <strong>{{ __('testing_issues.admin_review_note') }}:</strong><br>
                                    {{ $testingIssue->review_comment ?: __('testing_issues.no_review_comment') }}
                                </div>
                            </div>

                            <div style="margin-top:24px;text-align:center;">
                                <a href="{{ $followUpUrl }}" style="display:inline-block;padding:12px 20px;color:#fff;background:#4f78c4;border-radius:9px;font-size:14px;font-weight:700;text-decoration:none;">
                                    {{ __('testing_issues.open_follow_up') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:15px 28px;background:#edf2f8;color:#718096;font-size:12px;text-align:center;">
                            {{ __('testing_issues.email_footer') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
