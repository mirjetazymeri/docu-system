@extends('admin.layouts.default')

@section('title', __('testing_issues.title'))

@section('content')
<main class="issue-ui">
    <header class="issue-ui__header">
        <div>
            <span class="issue-ui__eyebrow">{{ __('ui.quality_assurance') }}</span>
            <h1>{{ __('testing_issues.title') }}</h1>
            <p>{{ $isAdmin ? __('testing_issues.admin_subtitle') : __('testing_issues.worker_subtitle') }}</p>
        </div>
        <a href="{{ $isAdmin ? route('admin.application_testing.index') : route('applications.index', ['scope' => 'assigned']) }}"
            class="issue-btn issue-btn--outline">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('testing_issues.back') }}
        </a>
    </header>

    @if(session('success'))
        <div class="issue-alert issue-alert--success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('mail_success'))
        <div class="issue-alert issue-alert--info"><i class="fas fa-envelope"></i> {{ session('mail_success') }}</div>
    @endif
    @if(session('warning'))
        <div class="issue-alert issue-alert--warning"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="issue-alert issue-alert--danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        </div>
    @endif

    <form method="GET" class="issue-filters">
        <label>
            <span>{{ __('testing_issues.project') }}</span>
            <select name="application_id" class="form-control">
                <option value="">{{ __('testing_issues.all_projects') }}</option>
                @foreach($applicationOptions as $application)
                    <option value="{{ $application->id }}" @selected(($filters['application_id'] ?? '') == $application->id)>{{ $application->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>{{ __('testing_issues.fix_status') }}</span>
            <select name="fix_status" class="form-control">
                @foreach([
                    'active' => __('testing_issues.active'),
                    'open' => __('testing_issues.open'),
                    'pending_review' => __('testing_issues.pending_review'),
                    'reopened' => __('testing_issues.reopened'),
                    'verified' => __('testing_issues.verified'),
                    'all' => __('testing_issues.all_statuses'),
                ] as $value => $label)
                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="issue-filter-actions">
            <a href="{{ route('testing-issues.index') }}" class="issue-btn issue-btn--outline">{{ __('testing_issues.clear') }}</a>
            <button type="submit" class="issue-btn">{{ __('testing_issues.filter') }}</button>
        </div>
    </form>

    <div class="issue-ui__count">{{ trans_choice('testing_issues.issues_count', $issues->total(), ['count' => $issues->total()]) }}</div>

    @forelse($issues as $issue)
        @php
            $testing = $issue->applicationTesting;
            $application = $testing?->application;
        @endphp
        <article class="issue-card" id="issue-{{ $issue->id }}">
            <div class="issue-card__top">
                <div>
                    <span class="issue-card__project">{{ $application?->name ?? '—' }}</span>
                    <h2><span class="issue-reference">{{ $issue->reference }}</span> {{ $issue->test_case }}</h2>
                </div>
                <span class="issue-status issue-status--{{ $issue->fix_status }}">
                    {{ __('testing_issues.' . $issue->fix_status) }}
                </span>
            </div>

            <div class="issue-card__meta">
                <span><i class="fas fa-cubes"></i> {{ $issue->unit?->name ?? __('testing_issues.unknown_module') }}</span>
                <span><i class="fas fa-tag"></i> {{ $issue->category }}</span>
                <span><i class="fas fa-code-branch"></i> {{ $testing?->version }}</span>
                <span class="qa-result qa-result--{{ str_replace('_', '-', $issue->result) }}">
                    {{ __('application_testing_pdf.' . $issue->result) }}
                </span>
            </div>

            <div class="issue-card__problem">
                <strong>{{ __('testing_issues.original_problem') }}</strong>
                <p>{{ $issue->comment ?: __('testing_issues.no_comment') }}</p>
            </div>

            @if($issue->fix_comment)
                <div class="issue-card__fix">
                    <strong>{{ __('testing_issues.worker_fix_note') }}</strong>
                    <p>{{ $issue->fix_comment }}</p>
                    <small>
                        {{ $issue->fixedBy?->full_name }}
                        @if($issue->fixed_at) · {{ $issue->fixed_at->format('d.m.Y H:i') }} @endif
                    </small>
                </div>
            @endif

            @if($issue->review_comment)
                <div class="issue-card__review">
                    <strong>{{ __('testing_issues.admin_review_note') }}</strong>
                    <p>{{ $issue->review_comment }}</p>
                    <small>
                        {{ $issue->reviewedBy?->full_name }}
                        @if($issue->reviewed_at) · {{ $issue->reviewed_at->format('d.m.Y H:i') }} @endif
                    </small>
                </div>
            @endif

            @if(!$isAdmin && in_array($issue->fix_status, ['open', 'reopened'], true))
                <form action="{{ route('testing-issues.submit-fix', $issue) }}" method="POST" class="issue-action-form">
                    @csrf
                    <label for="fix-comment-{{ $issue->id }}">{{ __('testing_issues.describe_fix') }}</label>
                    <textarea id="fix-comment-{{ $issue->id }}" name="fix_comment" rows="3" maxlength="2000" required
                        placeholder="{{ __('testing_issues.fix_placeholder') }}">{{ old('fix_comment') }}</textarea>
                    <button type="submit" class="issue-btn">
                        <i class="fas fa-paper-plane"></i> {{ __('testing_issues.submit_for_review') }}
                    </button>
                </form>
            @elseif(!$isAdmin && $issue->fix_status === 'pending_review')
                <div class="issue-pending-note"><i class="fas fa-clock"></i> {{ __('testing_issues.waiting_for_admin') }}</div>
            @endif

            @if($isAdmin && $issue->fix_status === 'pending_review')
                <div class="admin-review-grid">
                    <form action="{{ route('testing-issues.verify', $issue) }}" method="POST" class="issue-action-form">
                        @csrf
                        @method('PATCH')
                        <label for="verify-comment-{{ $issue->id }}">{{ __('testing_issues.optional_review_note') }}</label>
                        <textarea id="verify-comment-{{ $issue->id }}" name="review_comment" rows="2" maxlength="2000"></textarea>
                        <button type="submit" class="issue-btn issue-btn--success">
                            <i class="fas fa-check"></i> {{ __('testing_issues.confirm_passed') }}
                        </button>
                    </form>
                    <form action="{{ route('testing-issues.reopen', $issue) }}" method="POST" class="issue-action-form">
                        @csrf
                        @method('PATCH')
                        <label for="reopen-comment-{{ $issue->id }}">{{ __('testing_issues.reopen_reason') }}</label>
                        <textarea id="reopen-comment-{{ $issue->id }}" name="review_comment" rows="2" maxlength="2000" required></textarea>
                        <button type="submit" class="issue-btn issue-btn--danger">
                            <i class="fas fa-undo"></i> {{ __('testing_issues.reopen_issue') }}
                        </button>
                    </form>
                </div>
            @endif

            @if($issue->activities->isNotEmpty())
                <details class="issue-history">
                    <summary>{{ __('testing_issues.activity_history') }} ({{ $issue->activities->count() }})</summary>
                    @foreach($issue->activities as $activity)
                        <div class="issue-history__item">
                            <strong>{{ __('testing_issues.event_' . $activity->event) }}</strong>
                            <span>{{ $activity->user?->full_name ?? '—' }} · {{ $activity->occurred_at->format('d.m.Y H:i') }}</span>
                            @if($activity->comment)<p>{{ $activity->comment }}</p>@endif
                        </div>
                    @endforeach
                </details>
            @endif
        </article>
    @empty
        <div class="issue-empty"><i class="fas fa-check-circle"></i><p>{{ __('testing_issues.no_issues') }}</p></div>
    @endforelse

    {{ $issues->links() }}
</main>

<style>
    .issue-ui{max-width:1240px;margin:2rem auto;padding:0 1rem;color:#25344d}.issue-ui__header,.issue-filters,.issue-card,.issue-empty{background:#fff;border:1px solid #dfe6ef;border-radius:17px;box-shadow:0 12px 34px rgba(30,41,59,.07)}
    .issue-ui__header{display:flex;padding:27px 29px;border-left:5px solid #5c84d1;align-items:flex-start;justify-content:space-between;gap:18px}.issue-ui__eyebrow{color:#557cc5;font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase}.issue-ui__header h1{margin:5px 0;font-size:30px;font-weight:800}.issue-ui__header p{margin:0;color:#748196}
    .issue-btn{display:inline-flex;padding:9px 13px;color:#fff;background:#4f78c4;border:1px solid transparent;border-radius:8px;font-size:12px;font-weight:750;cursor:pointer;align-items:center;justify-content:center;gap:6px}.issue-btn:hover{color:#fff;background:#4269b0}.issue-btn--outline{color:#42536a;background:#fff;border-color:#ccd6e3}.issue-btn--outline:hover{color:#315b9f;background:#edf3fc}.issue-btn--success{background:#28745f}.issue-btn--success:hover{background:#205e4d}.issue-btn--danger{background:#b83a32}.issue-btn--danger:hover{background:#972e28}
    .issue-alert{display:flex;margin-top:15px;padding:12px 14px;border:1px solid;border-radius:10px;align-items:flex-start;gap:9px}.issue-alert--success{color:#176b51;background:#edf9f4;border-color:#bce4d5}.issue-alert--info{color:#315b9f;background:#edf3fc;border-color:#c9d8ee}.issue-alert--warning{color:#805b16;background:#fff8e7;border-color:#efdca9}.issue-alert--danger{color:#8c2e2a;background:#fff1f0;border-color:#efc1bd}
    .issue-filters{display:grid;margin-top:17px;padding:18px;grid-template-columns:1fr 1fr auto;align-items:end;gap:12px}.issue-filters label span{display:block;margin-bottom:5px;color:#64748b;font-size:11px;font-weight:700}.issue-filters .form-control{height:42px;border-color:#d7e0eb;border-radius:8px}.issue-filter-actions{display:flex;gap:7px}.issue-ui__count{margin:18px 2px 10px;color:#64748b;font-size:12px;font-weight:700}
    .issue-card{margin-bottom:15px;padding:21px}.issue-card__top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.issue-card__project{color:#557cc5;font-size:11px;font-weight:800;text-transform:uppercase}.issue-card h2{margin:4px 0 0;font-size:19px;font-weight:800}.issue-reference{display:inline-flex;margin-right:6px;padding:4px 7px;color:#315b9f;background:#e9f0fc;border-radius:7px;font-size:11px;vertical-align:middle}.issue-status,.qa-result{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800;white-space:nowrap}.issue-status--open{color:#8a5f16;background:#fff5da}.issue-status--pending_review{color:#315b9f;background:#e9f0fc}.issue-status--reopened{color:#98372f;background:#fdecea}.issue-status--verified{color:#1f6b55;background:#e5f5ef}.qa-result--not-passed{color:#98372f;background:#fdecea}.qa-result--in-process{color:#8a5f16;background:#fff5da}.qa-result--passed{color:#1f6b55;background:#e5f5ef}
    .issue-card__meta{display:flex;margin:13px 0;flex-wrap:wrap;align-items:center;gap:8px 14px;color:#718096;font-size:11px}.issue-card__meta i{margin-right:4px}.issue-card__problem,.issue-card__fix,.issue-card__review{padding:13px 14px;border-radius:10px}.issue-card__problem{background:#f8fafc;border:1px solid #e3e8ef}.issue-card__fix{margin-top:9px;background:#edf3fc;border:1px solid #ccdbee}.issue-card__review{margin-top:9px;background:#fff7e5;border:1px solid #ead7a8}.issue-card__problem strong,.issue-card__fix strong,.issue-card__review strong{font-size:11px}.issue-card__problem p,.issue-card__fix p,.issue-card__review p{margin:5px 0 0;white-space:pre-line}.issue-card__fix small,.issue-card__review small{display:block;margin-top:7px;color:#748196}
    .issue-action-form{display:flex;margin-top:13px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:11px;flex-direction:column;align-items:flex-start;gap:7px}.issue-action-form label{font-size:11px;font-weight:800}.issue-action-form textarea{width:100%;padding:9px 11px;border:1px solid #d4deea;border-radius:8px;resize:vertical}.admin-review-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.issue-pending-note{margin-top:12px;padding:10px 12px;color:#315b9f;background:#edf3fc;border-radius:9px;font-size:12px}
    .issue-history{margin-top:13px;padding-top:12px;border-top:1px solid #e5eaf1}.issue-history summary{color:#4f78c4;font-size:11px;font-weight:800;cursor:pointer}.issue-history__item{margin-top:9px;padding-left:11px;border-left:2px solid #ccd9ec}.issue-history__item strong,.issue-history__item span{display:block}.issue-history__item span{margin-top:2px;color:#7a8798;font-size:10px}.issue-history__item p{margin:4px 0 0;font-size:11px}.issue-empty{padding:45px;text-align:center}.issue-empty i{color:#2a745f;font-size:28px}.issue-empty p{margin:9px 0 0;color:#718096}
    @media(max-width:760px){.issue-ui__header{flex-direction:column}.issue-filters{grid-template-columns:1fr}.issue-filter-actions .issue-btn{flex:1}.admin-review-grid{grid-template-columns:1fr}.issue-card__top{flex-direction:column}}
</style>
@endsection
