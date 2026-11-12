@extends('admin.layouts.default')

@section('title', __('documentation_workers.section_title'))

@section('content')
<main class="workers-page">
    <header class="workers-page__hero">
        <div>
            <span>{{ __('documentation_workers.worker_role_label') }}</span>
            <h1>{{ __('documentation_workers.section_title') }}</h1>
            <p>{{ __('documentation_workers.section_subtitle') }}</p>
        </div>
        <i class="fas fa-history" aria-hidden="true"></i>
    </header>

    <section class="workers-panel">
        <form method="GET" action="{{ route('admin.documentation-workers.index') }}" class="workers-filters">
            <label>
                <span>{{ __('documentation_workers.worker') }}</span>
                <select name="worker_id" class="form-control">
                    <option value="">{{ __('documentation_workers.all_workers') }}</option>
                    @foreach($workerOptions as $option)
                        <option value="{{ $option->id }}" @selected(($workerFilters['worker_id'] ?? '') == $option->id)>
                            {{ $option->full_name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('documentation_workers.project') }}</span>
                <select name="application_id" class="form-control">
                    <option value="">{{ __('documentation_workers.all_projects') }}</option>
                    @foreach($applicationOptions as $option)
                        <option value="{{ $option->id }}" @selected(($workerFilters['application_id'] ?? '') == $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('documentation_workers.status') }}</span>
                <select name="status" class="form-control">
                    <option value="">{{ __('documentation_workers.all_statuses') }}</option>
                    <option value="in_process" @selected(($workerFilters['status'] ?? '') === 'in_process')>{{ __('documentation_workers.in_process') }}</option>
                    <option value="completed" @selected(($workerFilters['status'] ?? '') === 'completed')>{{ __('documentation_workers.completed') }}</option>
                </select>
            </label>
            <label>
                <span>{{ __('documentation_workers.date_from') }}</span>
                <input type="date" name="date_from" class="form-control" value="{{ $workerFilters['date_from'] ?? '' }}">
            </label>
            <label>
                <span>{{ __('documentation_workers.date_to') }}</span>
                <input type="date" name="date_to" class="form-control" value="{{ $workerFilters['date_to'] ?? '' }}">
            </label>
            <div class="workers-filter-actions">
                <a href="{{ route('admin.documentation-workers.index') }}" class="workers-btn workers-btn--light">{{ __('documentation_workers.clear') }}</a>
                <button type="submit" class="workers-btn"><i class="fas fa-filter"></i> {{ __('documentation_workers.filter') }}</button>
            </div>
        </form>

        @if($documentationWorkers->isEmpty())
            <div class="workers-empty">{{ __('documentation_workers.no_workers') }}</div>
        @else
            <div class="workers-table-wrap">
                <table class="workers-table">
                    <thead><tr>
                        <th>{{ __('documentation_workers.worker') }}</th>
                        <th>{{ __('documentation_workers.assigned_projects') }}</th>
                        <th>{{ __('documentation_workers.completed_documents') }}</th>
                        <th>{{ __('documentation_workers.in_process_documents') }}</th>
                        <th>{{ __('documentation_workers.last_activity') }}</th>
                        <th>{{ __('documentation_workers.last_project') }}</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    @foreach($documentationWorkers as $worker)
                        @php($lastActivity = $worker->last_worker_activity)
                        <tr>
                            <td><strong>{{ $worker->full_name }}</strong><small>{{ $worker->email }}</small></td>
                            <td class="workers-number">{{ $worker->assigned_projects_count }}</td>
                            <td class="workers-number">{{ $worker->completed_documentations_count }}</td>
                            <td class="workers-number">{{ $worker->in_process_documentations_count }}</td>
                            <td>{{ $lastActivity?->occurred_at?->format('d.m.Y H:i') ?? __('documentation_workers.no_activity') }}</td>
                            <td>{{ $lastActivity?->application?->name ?? '—' }}</td>
                            <td><a href="{{ route('admin.documentation-workers.show', $worker) }}" class="workers-btn workers-btn--light">{{ __('documentation_workers.view_profile') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>

<style>
    .workers-page{max-width:1380px;margin:2rem auto;padding:0 1rem;color:#25344d}.workers-page__hero,.workers-panel{background:#fff;border:1px solid #e0e6ef;border-radius:18px;box-shadow:0 13px 36px rgba(30,41,59,.07)}
    .workers-page__hero{display:flex;padding:28px 30px;border-left:5px solid #5c84d1;align-items:center;justify-content:space-between}.workers-page__hero span{color:#557cc5;font-size:11px;font-weight:800;letter-spacing:1.3px;text-transform:uppercase}.workers-page__hero h1{margin:6px 0;color:#25344d;font-size:30px;font-weight:800}.workers-page__hero p{margin:0;color:#7a8799}.workers-page__hero>i{display:flex;width:58px;height:58px;color:#557cc5;background:#edf3fc;border-radius:15px;font-size:22px;align-items:center;justify-content:center}
    .workers-panel{margin-top:18px;padding:24px}.workers-filters{display:grid;margin-bottom:20px;padding:16px;background:#f7f9fc;border:1px solid #e3e8f0;border-radius:13px;grid-template-columns:repeat(5,minmax(120px,1fr));gap:12px}.workers-filters label>span{display:block;margin-bottom:5px;color:#56657a;font-size:11px;font-weight:750}.workers-filters .form-control{height:40px;border-color:#d7e0eb;border-radius:8px;font-size:12px}.workers-filter-actions{display:flex;grid-column:1/-1;justify-content:flex-end;gap:8px}
    .workers-btn{display:inline-flex;min-height:38px;padding:8px 13px;color:#fff;background:#4f78c4;border:1px solid transparent;border-radius:8px;font-size:12px;font-weight:750;align-items:center;justify-content:center;gap:6px}.workers-btn:hover{color:#fff;background:#4269b0}.workers-btn--light{color:#46566d;background:#fff;border-color:#ccd6e3}.workers-btn--light:hover{color:#315b9f;background:#edf3fc}
    .workers-table-wrap{overflow-x:auto;border:1px solid #e1e7ef;border-radius:12px}.workers-table{width:100%;border-collapse:collapse}.workers-table th{padding:11px 12px;color:#657287;background:#edf2f8;border-bottom:1px solid #d9e1ec;font-size:10px;letter-spacing:.35px;text-align:left;text-transform:uppercase;white-space:nowrap}.workers-table td{padding:13px 12px;color:#46546a;border-bottom:1px solid #edf1f5;font-size:12px;vertical-align:middle}.workers-table tr:last-child td{border-bottom:0}.workers-table td strong{display:block;color:#263750}.workers-table td small{display:block;margin-top:2px;color:#8a96a7}.workers-number{color:#263750!important;font-size:17px!important;font-weight:800}.workers-empty{padding:35px;color:#7b8798;text-align:center}
    @media(max-width:1000px){.workers-filters{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.workers-page__hero>i{display:none}.workers-filters{grid-template-columns:1fr}.workers-panel{padding:15px}}
</style>
@endsection
