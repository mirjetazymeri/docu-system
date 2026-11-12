@extends('admin.layouts.default')

@section('title')
    {{ __('words.modules_for') }}: {{ $application->name }}
    @parent
@stop

@section('content')
<div class="doc-ui">
    <header class="doc-ui__header">
        <div>
            <span class="doc-ui__eyebrow">{{ __('words.application') }}</span>
            <h1 class="doc-ui__title">{{ __('words.modules_for') }}: {{ $application->name }}</h1>
            <p class="doc-ui__subtitle">{{ __('ui.manage_modules_and_their_documentation') }}</p>
        </div>
        <a href="{{ route('applications.index') }}" class="doc-btn doc-btn--outline">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back_to_applications') }}
        </a>
    </header>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="doc-ui__toolbar">
        @if($canImportTestDocu)
            <form method="POST" action="{{ route('applications.import-TestDocu-documentation', $application) }}">
                @csrf
                <button type="submit" class="doc-btn doc-btn--primary">{{ __('ui.import_remaining_modules') }}</button>
                <small>{{ __('ui.19_modules_203_documents_existing_entries_are_preserved') }}</small>
            </form>
        @endif
        <strong>{{ $units->count() }} {{ __('ui.modules') }}</strong>
        @if($canCreateUnits)
            <a href="{{ route('units.create', ['application' => $application->id]) }}" class="doc-btn doc-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('words.add_modules') }}
            </a>
        @endif
    </div>

    @if($units->isEmpty())
        <div class="doc-ui__empty">
            <i class="far fa-folder-open fa-2x mb-3" aria-hidden="true"></i>
            <p class="mb-0">{{ __('ui.no_modules_found_for_this_application') }}</p>
        </div>
    @else
        <div class="doc-ui__grid">
            @foreach ($units as $unit)
                <article class="doc-card">
                    <div class="doc-card__top">
                        <span class="doc-card__icon"><i class="fas fa-cubes" aria-hidden="true"></i></span>
                        <span class="doc-card__id">ID {{ $unit->id }}</span>
                    </div>
                    <h2 class="doc-card__title">{{ $unit->name }}</h2>
                    <p class="doc-card__meta">{{ __('ui.application_module') }} {{ $application->name }}</p>
                    <div class="doc-card__actions">
                        @if($canEditUnits)
                            <a href="{{ route('units.edit', ['application' => $application->id, 'unit' => $unit->id]) }}" class="doc-btn doc-btn--accent">
                                <i class="fas fa-pen" aria-hidden="true"></i> {{ __('words.edit') }}
                            </a>
                        @endif
                        <a href="{{ route('admin.documentation.index', ['application' => $application->id, 'unit' => $unit->id]) }}" class="doc-btn doc-btn--blue">
                            <i class="fas fa-file-alt" aria-hidden="true"></i> {{ __('words.documentation') }}
                        </a>
                        @if(Sentinel::inRole('admin'))
                            <form action="{{ route('units.destroy', ['application' => $application->id, 'unit' => $unit->id]) }}" method="POST" onsubmit="return confirm(@js(__('ui.are_you_sure_you_want_to_delete_this_module')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="doc-btn doc-btn--danger" aria-label="{{ __('ui.delete') }} {{ $unit->name }}">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i> {{ __('words.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('admin.partials.documentation-ui-styles')
@endsection
