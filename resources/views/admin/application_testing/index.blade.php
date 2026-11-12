@extends('admin.layouts.default')

@section('title', __('ui.application_testing'))

@section('content')
    @php
        $totalCases = $testings->sum('testing_details_count');
        $totalPassed = $testings->sum('passed_count');
        $totalFailed = $testings->sum('failed_count');
        $pendingReviews = $testings->sum('pending_review_count');
    @endphp

    <main class="testing-ui">
        <header class="testing-ui__header">
            <div>
                <span class="testing-ui__eyebrow">{{ __('ui.quality_assurance') }}</span>
                <h1 class="testing-ui__title">{{ __('ui.application_testing') }}</h1>
                <p class="testing-ui__subtitle">{{ __('ui.manage_tests_checked_modules_and_their_results') }}</p>
            </div>
            <div class="testing-actions">
            <a href="{{ route('testing-issues.index', ['fix_status' => 'pending_review']) }}"
                class="testing-btn testing-btn--outline">
                <i class="fas fa-clipboard-check" aria-hidden="true"></i>
                {{ __('testing_issues.review_fixes') }}
                @if($pendingReviews > 0)<span class="testing-btn__count">{{ $pendingReviews }}</span>@endif
            </a>
            <a href="{{ route('admin.application_testing.create') }}" class="testing-btn">
                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('ui.add_new_testing') }}
            </a>
            </div>
        </header>

        @if(session('success'))
            <div class="testing-alert testing-alert--success" role="alert">
                <i class="fas fa-check-circle" aria-hidden="true"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="testing-alert testing-alert--danger" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="testing-alert testing-alert--warning" role="alert">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ session('warning') }}
            </div>
        @endif

        <section class="testing-stats" aria-label="{{ __('ui.testing_summary') }}">
            <div class="testing-stat"><span>{{ __('ui.testing_records') }}</span><strong>{{ $testings->count() }}</strong></div>
            <div class="testing-stat"><span>{{ __('ui.test_cases') }}</span><strong>{{ $totalCases }}</strong></div>
            <div class="testing-stat"><span>{{ __('ui.passed') }}</span><strong>{{ $totalPassed }}</strong></div>
            <div class="testing-stat"><span>{{ __('ui.failed') }}</span><strong>{{ $totalFailed }}</strong></div>
        </section>

        @if($testings->isEmpty())
            <div class="testing-empty">
                <i class="fas fa-clipboard-check fa-2x mb-3" aria-hidden="true"></i>
                <h3>{{ __('ui.no_testing_records_yet') }}</h3>
                <p>{{ __('ui.create_the_first_testing_record_for_an_application') }}</p>
                <a href="{{ route('admin.application_testing.create') }}" class="testing-btn">{{ __('ui.add_testing') }}</a>
            </div>
        @else
            <div class="testing-table-wrap">
                <table class="testing-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.application') }}</th>
                            <th>{{ __('ui.tester') }}</th>
                            <th>{{ __('ui.version') }}</th>
                            <th>{{ __('ui.date') }}</th>
                            <th>{{ __('ui.status') }}</th>
                            <th>{{ __('ui.results') }}</th>
                            <th>{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testings as $testing)
                            <tr>
                                <td><strong>{{ optional($testing->application)->name ?? __('ui.deleted_application') }}</strong></td>
                                <td>{{ $testing->tester_name }}</td>
                                <td>{{ $testing->version }}</td>
                                <td>{{ optional($testing->test_date)->format('d.m.Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.application_testing.completion', $testing) }}" method="POST" class="testing-completion-form">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_completed" value="{{ $testing->is_completed ? 0 : 1 }}">
                                        <button type="submit" class="testing-completion {{ $testing->is_completed ? 'testing-completion--checked' : '' }}"
                                            title="{{ $testing->is_completed ? __('application_status.remove_testing_completed_title') : __('application_status.mark_testing_completed_title') }}">
                                            <i class="far {{ $testing->is_completed ? 'fa-check-square' : 'fa-square' }}" aria-hidden="true"></i>
                                            {{ $testing->is_completed ? __('application_status.testing_completed') : __('application_status.mark_testing_completed') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <span class="testing-badge testing-badge--passed">
                                        {{ $testing->passed_count }} {{ __('application_testing_pdf.passed') }}
                                    </span>
                                    <span class="testing-badge testing-badge--failed">
                                        {{ $testing->failed_count }} {{ __('application_testing_pdf.not_passed') }}
                                    </span>
                                    @if($testing->in_process_count)
                                        <span class="testing-badge testing-badge--progress">
                                            {{ $testing->in_process_count }} {{ __('application_testing_pdf.in_process') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="testing-actions">
                                        <a href="{{ route('admin.application_testing.edit', $testing) }}"
                                            class="testing-btn testing-btn--outline testing-btn--small">
                                            <i class="fas fa-pen" aria-hidden="true"></i> {{ __('ui.edit') }}
                                        </a>
                                        <a href="{{ route('admin.application_testing.generatePdf', $testing) }}"
                                            class="testing-btn testing-btn--navy testing-btn--small" target="_blank">
                                            <i class="fas fa-file-pdf" aria-hidden="true"></i> PDF
                                        </a>
                                        @php
                                            $actionItemsCount = $testing->failed_count + $testing->in_process_count;
                                            $assignedRecipientsCount = $testing->application?->users
                                                ->filter(
                                                    fn ($user) =>
                                                        $user->email &&
                                                        filter_var(
                                                            $user->email,
                                                            FILTER_VALIDATE_EMAIL
                                                        )
                                                )
                                                ->count() ?? 0;
                                        @endphp
                                        @if($actionItemsCount > 0 && $assignedRecipientsCount > 0)
                                            <form action="{{ route('admin.application_testing.sendReport', $testing) }}"
                                                method="POST"
                                                data-confirm="{{ __('application_testing_report.confirm_send') }}"
                                                onsubmit="return confirm(this.dataset.confirm);">
                                                @csrf
                                                <button type="submit"
                                                    class="testing-btn testing-btn--mail testing-btn--small"
                                                    title="{{ __('application_testing_report.sending_hint') }}">
                                                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                                    {{ __('application_testing_report.send_report') }}
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                class="testing-btn testing-btn--disabled testing-btn--small"
                                                disabled
                                                title="{{ $actionItemsCount === 0
                                                    ? __('application_testing_report.nothing_to_send')
                                                    : __('application_testing_report.no_assigned_users') }}">
                                                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                                {{ __('application_testing_report.send_report') }}
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.application_testing.destroy', $testing) }}" method="POST"
                                            onsubmit="return confirm(@js(__('ui.are_you_sure_you_want_to_delete_this_testing_record')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="testing-btn testing-btn--danger testing-btn--small">
                                                <i class="fas fa-trash" aria-hidden="true"></i> {{ __('ui.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </main>

    @include('admin.application_testing._styles')
@endsection
