@extends('admin.layouts.default')

@section('title', __('ui.documentation_list'))

@section('content')
<div class="doc-ui">
    <header class="doc-ui__header">
        <div>
            <span class="doc-ui__eyebrow">{{ $application->name }}</span>
            <h1 class="doc-ui__title">{{ __('words.documentation_for_module') }}: {{ $unit->name }}</h1>
            <p class="doc-ui__subtitle">{{ __('ui.manage_documents_and_pdf_versions_for_this_module') }}</p>
        </div>
        <a href="{{ route('units.index', ['application' => $application->id]) }}" class="doc-btn doc-btn--outline">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back_to_modules') }}
        </a>
    </header>

    @if(session('success'))
        <div class="doc-alert doc-alert--success" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <div>
                <strong>{{ __('ui.completed_successfully') }}</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="doc-alert doc-alert--danger" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div><strong>{{ __('ui.an_error_occurred') }}</strong><p>{{ session('error') }}</p></div>
        </div>
    @endif

    @if(session('warning'))
        <div class="doc-alert doc-alert--warning" role="alert">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <div><strong>{{ __('ui.warning') }}</strong><p>{{ session('warning') }}</p></div>
        </div>
    @endif

    <div class="doc-ui__toolbar">
        <strong>{{ $documentations->count() }} {{ __('ui.documents') }}</strong>
        @if($canCreateDocumentations)
            <a href="{{ route('documentation.create', ['application' => $application->id, 'unit' => $unit->id]) }}" class="doc-btn doc-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('words.add_documentations') }}
            </a>
        @endif
    </div>

    @if($documentations->isEmpty())
        <div class="doc-ui__empty">
            <i class="far fa-file-alt fa-2x mb-3" aria-hidden="true"></i>
            <p class="mb-0">{{ __('ui.no_documents_found_for_this_module') }}</p>
        </div>
    @else
        <div class="doc-ui__grid">
            @foreach($documentations as $documentation)
                <article class="doc-card">
                    <div class="doc-card__top">
                        <span class="doc-card__icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                        <span class="doc-card__id">ID {{ $documentation->id }}</span>
                    </div>
                    <h2 class="doc-card__title">{{ $documentation->title }}</h2>
                    <p class="doc-card__meta">{{ __('ui.module_documentation') }} {{ $unit->name }}</p>
                    <div class="doc-card__actions">
                        @if($canEditDocumentations)
                            <a href="{{ route('documentation.edit', ['application' => $application->id, 'unit' => $unit->id, 'documentation' => $documentation->id]) }}" class="doc-btn doc-btn--accent">
                                <i class="fas fa-pen" aria-hidden="true"></i> {{ __('words.edit') }}
                            </a>
                        @endif
                        <a href="{{ route('documentations.generatePdf', ['application' => $application->id, 'unit' => $unit->id, 'documentation' => $documentation->id]) }}" target="_blank" rel="noopener" class="doc-btn doc-btn--blue">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('words.dowwnload_pdf') }}
                        </a>
                        @if(Sentinel::inRole('admin'))
                            <form action="{{ route('documentation.destroy', ['application' => $application->id, 'unit' => $unit->id, 'documentation' => $documentation->id]) }}" method="POST" onsubmit="return confirm(@js(__('ui.are_you_sure_you_want_to_delete_this_documentation')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="doc-btn doc-btn--danger">
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
