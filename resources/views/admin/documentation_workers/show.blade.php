@extends('admin.layouts.default')

@section('title', __('documentation_workers.profile_title', ['name' => $worker->full_name]))

@section('content')
<main class="worker-profile">
    <header class="worker-profile__hero">
        <div>
            <span class="worker-profile__eyebrow">{{ __('documentation_workers.worker_role_label') }}</span>
            <h1>{{ __('documentation_workers.profile_title', ['name' => $worker->full_name]) }}</h1>
            <p>{{ $worker->email }} · {{ __('documentation_workers.profile_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.documentation-workers.index') }}" class="wp-btn wp-btn--light">
            <i class="fas fa-arrow-left"></i> {{ __('documentation_workers.back_dashboard') }}
        </a>
    </header>

    <section class="worker-profile__stats">
        <div><span>{{ __('documentation_workers.assigned_projects') }}</span><strong>{{ $assignedProjectsCount }}</strong></div>
        <div><span>{{ __('documentation_workers.completed_documents') }}</span><strong>{{ $completedCount }}</strong></div>
        <div><span>{{ __('documentation_workers.in_process_documents') }}</span><strong>{{ $inProcessCount }}</strong></div>
    </section>

    <form method="GET" class="wp-filters">
        <label>
            <span>{{ __('documentation_workers.project') }}</span>
            <select name="application_id" class="form-control">
                <option value="">{{ __('documentation_workers.all_projects') }}</option>
                @foreach($applicationOptions as $application)
                    <option value="{{ $application->id }}" @selected(($filters['application_id'] ?? '') == $application->id)>
                        {{ $application->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label>
            <span>{{ __('documentation_workers.status') }}</span>
            <select name="status" class="form-control">
                <option value="">{{ __('documentation_workers.all_statuses') }}</option>
                <option value="in_process" @selected(($filters['status'] ?? '') === 'in_process')>{{ __('documentation_workers.in_process') }}</option>
                <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('documentation_workers.completed') }}</option>
            </select>
        </label>
        <label>
            <span>{{ __('documentation_workers.date_from') }}</span>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </label>
        <label>
            <span>{{ __('documentation_workers.date_to') }}</span>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </label>
        <div class="wp-filter-actions">
            <a href="{{ route('admin.documentation-workers.show', $worker) }}" class="wp-btn wp-btn--light">{{ __('documentation_workers.clear') }}</a>
            <button class="wp-btn" type="submit">{{ __('documentation_workers.filter') }}</button>
        </div>
    </form>

    <section class="worker-timeline">
        <div class="worker-timeline__header">
            <h2>{{ __('documentation_workers.timeline') }}</h2>
            <a href="{{ route('admin.documentation-workers.index') }}"
                class="wp-btn wp-btn--light"
                onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                {{ __('documentation_workers.back') }}
            </a>
        </div>
        @forelse($histories as $history)
            @php
                $eventKey = 'documentation_workers.event_' . $history->event;
                $eventLabel = \Illuminate\Support\Facades\Lang::has($eventKey)
                    ? __($eventKey)
                    : __('documentation_workers.unknown_event');
            @endphp
            <article class="worker-event worker-event--{{ $history->event }}">
                <span class="worker-event__dot"></span>
                <div class="worker-event__body">
                    <div class="worker-event__top">
                        <strong>{{ $eventLabel }}</strong>
                        <time>{{ $history->occurred_at->format('d.m.Y H:i') }}</time>
                    </div>
                    <p>
                        <b>{{ __('documentation_workers.project') }}:</b> {{ $history->application?->name ?? '—' }}
                        @if($history->unit)
                            · <b>{{ __('documentation_workers.unit') }}:</b> {{ $history->unit->name }}
                        @endif
                        @if($history->documentation)
                            · <b>{{ __('documentation_workers.documentation') }}:</b> {{ $history->documentation->title }}
                        @endif
                    </p>
                </div>
            </article>
        @empty
            <div class="wp-empty">{{ __('documentation_workers.no_history') }}</div>
        @endforelse

        {{ $histories->links() }}
    </section>
</main>

<style>
    .worker-profile{max-width:1280px;margin:2rem auto;padding:0 1rem;color:#25344d}
    .worker-profile__hero,.worker-profile__stats,.wp-filters,.worker-timeline{background:#fff;border:1px solid #e0e6ef;border-radius:17px;box-shadow:0 12px 34px rgba(30,41,59,.07)}
    .worker-profile__hero{display:flex;padding:28px 30px;align-items:flex-start;justify-content:space-between;gap:18px;border-left:5px solid #5c84d1}
    .worker-profile__eyebrow{color:#557cc5;font-size:11px;font-weight:800;letter-spacing:1.3px;text-transform:uppercase}
    .worker-profile__hero h1{margin:6px 0;color:#25344d;font-size:30px;font-weight:800}.worker-profile__hero p{margin:0;color:#7a8799}
    .worker-profile__stats{display:grid;margin-top:18px;padding:18px;grid-template-columns:repeat(3,1fr);gap:12px}
    .worker-profile__stats div{padding:15px;background:#f7f9fc;border-radius:11px}.worker-profile__stats span{display:block;color:#718096;font-size:11px}.worker-profile__stats strong{font-size:25px}
    .wp-filters{display:grid;margin-top:18px;padding:18px;grid-template-columns:repeat(4,1fr);gap:12px}.wp-filters label span{display:block;margin-bottom:5px;color:#59687d;font-size:11px;font-weight:700}
    .wp-filter-actions{display:flex;grid-column:1/-1;justify-content:flex-end;gap:8px}.wp-btn{display:inline-flex;padding:9px 13px;color:#fff;background:#4f78c4;border:1px solid transparent;border-radius:8px;font-size:12px;font-weight:700;align-items:center;gap:6px}.wp-btn:hover{color:#fff;background:#4269b0}.wp-btn--light{color:#43536a;background:#fff;border-color:#ccd6e3}.wp-btn--light:hover{color:#315b9f;background:#edf3fc}
    .worker-timeline{margin-top:18px;padding:25px}.worker-timeline__header{display:flex;margin-bottom:20px;align-items:center;justify-content:space-between;gap:14px}.worker-timeline h2{margin:0;font-size:20px;font-weight:800}
    .worker-event{position:relative;margin-left:8px;padding:0 0 20px 27px;border-left:2px solid #dde5f0}.worker-event__dot{position:absolute;top:4px;left:-7px;width:12px;height:12px;background:#5c84d1;border:3px solid #edf3fc;border-radius:50%}
    .worker-event__body{padding:13px 15px;background:#f8fafc;border:1px solid #e4e9f0;border-radius:11px}.worker-event__top{display:flex;justify-content:space-between;gap:12px}.worker-event__top time{color:#8793a5;font-size:11px}.worker-event__body p{margin:7px 0 0;color:#647286;font-size:12px}.wp-empty{padding:30px;color:#7b8798;text-align:center}
    @media(max-width:800px){.wp-filters{grid-template-columns:repeat(2,1fr)}.worker-profile__hero{flex-direction:column}.worker-profile__stats{grid-template-columns:1fr}}
    @media(max-width:520px){.wp-filters{grid-template-columns:1fr}.worker-event__top{flex-direction:column}.worker-timeline__header{align-items:stretch;flex-direction:column}.worker-timeline__header .wp-btn{justify-content:center}}
</style>
@endsection
