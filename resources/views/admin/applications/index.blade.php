@extends('admin.layouts.default')

@section('title')
    {{ __('words.applications') }}
    @parent
@stop

@section('content')
<div class="doc-ui">
    <header class="doc-ui__header">
        <div>
            <span class="doc-ui__eyebrow">{{ __('ui.documentation_hub') }}</span>
            <h1 class="doc-ui__title">{{ __('words.applications') }}</h1>
            <p class="doc-ui__subtitle">{{ __('ui.manage_applications_modules_manuals_and_documentation_in_one_place') }}</p>
        </div>
    </header>

    <div class="doc-ui__filters" aria-label="{{ __('ui.application_filters') }}">
        <input type="search" id="search" class="form-control" placeholder="{{ __('ui.search_applications') }}" oninput="applyFilters()" aria-label="{{ __('ui.search_applications') }}">
        <select id="dateFilter" class="form-control" onchange="applyFilters()" aria-label="{{ __('ui.filter_by_date') }}">
            <option value="">{{ __('ui.all_dates') }}</option>
            <option value="today">{{ __('ui.today') }}</option>
            <option value="week">{{ __('ui.last_7_days') }}</option>
            <option value="month">{{ __('ui.last_30_days') }}</option>
            <option value="year">{{ __('ui.last_year') }}</option>
        </select>
        <select id="categoryFilter" class="form-control" onchange="applyFilters()" aria-label="{{ __('ui.filter_by_category') }}">
            <option value="">{{ __('ui.all_categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="doc-ui__toolbar">
        <strong>
            @if($isDocumentationWorker)
                {{ $applicationScope === 'assigned'
                    ? __('documentation_workers.my_applications')
                    : __('documentation_workers.all_applications') }}:
                <span id="visibleApplicationsCount">{{ $applications->count() }}</span>
            @else
                <span id="visibleApplicationsCount">{{ $applications->count() }}</span>
                {{ __('words.applications') }}
            @endif
        </strong>
        @if($canManageApplications)
            <a href="{{ route('applications.create') }}" class="doc-btn doc-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('words.create_applications') }}
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if($applications->isEmpty())
        <div class="doc-ui__empty">
            <i class="far fa-folder-open fa-2x mb-3" aria-hidden="true"></i>
            <p class="mb-0">{{ __('ui.no_applications_found') }}</p>
        </div>
    @else
        <div class="doc-ui__grid" id="applicationsContainer">
            @foreach($applications as $application)
                @php($isAssignedApplication = $assignedApplicationIds->contains($application->id))
                <article class="doc-card application-card"
                    data-created-at="{{ $application->created_at->format('Y-m-d') }}"
                    data-category-id="{{ $application->category_id }}">
                    <div class="doc-card__top">
                        <span class="doc-card__icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                        <span class="doc-card__id">ID {{ $application->id }}</span>
                    </div>
                    <h2 class="doc-card__title">{{ $application->name }}</h2>
                    @if($isDocumentationWorker)
                        <span class="project-access-badge project-access-badge--{{ $isAssignedApplication ? 'assigned' : 'readonly' }}">
                            <i class="fas {{ $isAssignedApplication ? 'fa-user-check' : 'fa-eye' }}" aria-hidden="true"></i>
                            {{ $isAssignedApplication
                                ? __('documentation_workers.assigned_edit_access')
                                : __('documentation_workers.colleague_read_only') }}
                        </span>
                    @endif
                    <p class="doc-card__meta">
                        <i class="far fa-calendar-alt mr-1" aria-hidden="true"></i>
                        {{ __('words.created_at') }}: {{ $application->created_at->format('Y-m-d') }}
                    </p>
                    <p class="doc-card__meta">
                        <i class="far fa-folder mr-1" aria-hidden="true"></i>
                        {{ __('ui.category') }}: {{ $application->category->category_name ?? __('words.not_available') }}
                    </p>
                    @if($canUpdateDocumentationStatus && $isAssignedApplication)
                    <div class="project-doc-status project-doc-status--{{ $application->documentation_status }}">
                        <div class="project-doc-status__info">
                            <span class="project-doc-status__icon">
                                <i class="fas {{ $application->documentation_status === 'completed' ? 'fa-check' : 'fa-hourglass-half' }}"></i>
                            </span>
                            <div>
                                <small>{{ __('documentation_workers.project_documentation_status') }}</small>
                                <strong>
                                    {{ $application->documentation_status === 'completed'
                                        ? __('documentation_workers.completed')
                                        : __('documentation_workers.in_process') }}
                                </strong>
                                @if($application->documentation_completed_at)
                                    <em>{{ __('documentation_workers.completed_on', [
                                        'date' => $application->documentation_completed_at->format('d.m.Y H:i')
                                    ]) }}</em>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('applications.documentation-status', $application) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="documentation_status"
                                value="{{ $application->documentation_status === 'completed' ? 'in_process' : 'completed' }}">
                            <button type="submit" class="project-doc-status__button">
                                <i class="far {{ $application->documentation_status === 'completed' ? 'fa-square' : 'fa-check-square' }}"></i>
                                {{ $application->documentation_status === 'completed'
                                    ? __('documentation_workers.mark_in_process')
                                    : __('documentation_workers.mark_completed') }}
                            </button>
                        </form>
                    </div>
                    @endif
                    <div class="app-card__pdf">
                        <a href="{{ route('documentations.generatePdfAll', ['application' => $application->id]) }}" target="_blank" rel="noopener" class="doc-btn doc-btn--outline">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('words.download_all_pdf') }}
                        </a>
                    </div>
                    <div class="app-card__footer">
                        <div class="app-card__navigation" aria-label="{{ __('ui.application_content') }}">
                            <a href="{{ route('units.index', ['application' => $application->id]) }}" class="doc-btn doc-btn--blue">
                                <i class="fas fa-cubes" aria-hidden="true"></i> {{ __('words.modules') }}
                            </a>
                            @if($canManageApplications || Sentinel::getUser()->hasAccess('manuals.view'))
                                <a href="{{ route('manuals.index', $application->id) }}" class="doc-btn doc-btn--primary">
                                    <i class="fas fa-book-open" aria-hidden="true"></i> {{ __('words.manuals') }}
                                </a>
                            @endif
                        </div>
                        @if($canManageApplications)
                        <div class="app-card__management" aria-label="{{ __('ui.application_actions') }}">
                            <a href="{{ route('applications.edit', $application->id) }}" class="doc-btn doc-btn--accent" aria-label="{{ __('words.edit') }} {{ $application->name }}" title="{{ __('words.edit') }}">
                                <i class="fas fa-pen" aria-hidden="true"></i>
                            </a>
                                <form action="{{ route('applications.destroy', $application->id) }}" method="POST" onsubmit="return confirm(@js(__('words.sure_to_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="doc-btn doc-btn--danger" aria-label="{{ __('words.delete') }} {{ $application->name }}" title="{{ __('words.delete') }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </form>
                        </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div id="noFilterResults" class="doc-ui__empty" style="display:none;">
            {{ __('ui.no_applications_match_the_filters') }}
        </div>
    @endif
</div>

@include('admin.partials.documentation-ui-styles')

<style>
    .project-doc-status{display:flex;margin:18px 0 14px;padding:13px 14px;background:#f7f9fc;border:1px solid #dce4ef;border-radius:12px;align-items:center;justify-content:space-between;gap:14px}
    .project-doc-status__info{display:flex;min-width:0;align-items:center;gap:11px}.project-doc-status__icon{display:inline-flex;width:39px;height:39px;color:#8a661d;background:#fff3d7;border-radius:10px;align-items:center;justify-content:center;flex:0 0 39px}
    .project-doc-status small,.project-doc-status strong,.project-doc-status em{display:block}.project-doc-status small{color:#7c899c;font-size:10px}.project-doc-status strong{color:#5f4a1f;font-size:13px}.project-doc-status em{margin-top:2px;color:#8290a3;font-size:9px;font-style:normal}
    .project-doc-status__button{display:inline-flex;padding:8px 11px;color:#4c5d74;background:#fff;border:1px solid #ccd7e5;border-radius:8px;font-size:11px;font-weight:750;cursor:pointer;align-items:center;gap:6px}
    .project-doc-status__button:hover{color:#315b9f;background:#edf3fc}.project-doc-status--completed{background:#edf8f4;border-color:#c6e6da}.project-doc-status--completed .project-doc-status__icon{color:#23705a;background:#dff3eb}.project-doc-status--completed strong{color:#24624f}
    .project-access-badge{display:inline-flex;width:max-content;margin:2px 0 10px;padding:6px 9px;border:1px solid;border-radius:999px;font-size:10px;font-weight:750;align-items:center;gap:6px}.project-access-badge--assigned{color:#315b9f;background:#edf3fc;border-color:#cad9f1}.project-access-badge--readonly{color:#64748b;background:#f8fafc;border-color:#dbe3ed}
    @media(max-width:600px){.project-doc-status{align-items:stretch;flex-direction:column}.project-doc-status__button{width:100%;justify-content:center}}
</style>

<script>
    function applyFilters() {
        const search = document.getElementById('search').value.trim().toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        let visibleCount = 0;

        document.querySelectorAll('.application-card').forEach(function (card) {
            const title = card.querySelector('.doc-card__title').textContent.toLowerCase();
            const createdAt = new Date(card.dataset.createdAt + 'T00:00:00');
            let visible = !search || title.includes(search);

            if (visible && category) {
                visible = card.dataset.categoryId === category;
            }

            if (visible && dateFilter) {
                const startDate = new Date(today);
                if (dateFilter === 'today') {
                    startDate.setHours(0, 0, 0, 0);
                } else if (dateFilter === 'week') {
                    startDate.setDate(today.getDate() - 7);
                } else if (dateFilter === 'month') {
                    startDate.setDate(today.getDate() - 30);
                } else if (dateFilter === 'year') {
                    startDate.setFullYear(today.getFullYear() - 1);
                }
                visible = createdAt >= startDate && createdAt <= today;
            }

            card.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        document.getElementById('visibleApplicationsCount').textContent = visibleCount;
        const noResults = document.getElementById('noFilterResults');
        if (noResults) noResults.style.display = visibleCount === 0 ? '' : 'none';
    }
</script>
@endsection
