@extends('admin.layouts.default')

@section('title')
    {{ __('words.manuals') }}: {{ $application->name }}
    @parent
@stop

@section('content')
<div class="doc-ui">
    <header class="doc-ui__header">
        <div>
            <span class="doc-ui__eyebrow">{{ __('words.application') }}</span>
            <h1 class="doc-ui__title">{{ __('words.manuals') }}: {{ $application->name }}</h1>
            <p class="doc-ui__subtitle">{{ __('ui.create_update_and_download_application_manuals') }}</p>
        </div>
        <a href="{{ route('applications.index') }}" class="doc-btn doc-btn--outline">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back_to_applications') }}
        </a>
    </header>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="doc-ui__toolbar">
        <strong>{{ $manuals->count() }} {{ __('ui.manuals') }}</strong>
        @if($canCreateManuals)
            <a href="{{ route('manuals.create', $application->id) }}" class="doc-btn doc-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('words.add_new_manual') }}
            </a>
        @endif
    </div>

    @if($manuals->isEmpty())
        <div class="doc-ui__empty">
            <i class="far fa-file-alt fa-2x mb-3" aria-hidden="true"></i>
            <p class="mb-0">{{ __('words.no_manuals_found') }}</p>
        </div>
    @else
        <div class="doc-ui__grid">
            @foreach($manuals as $manual)
                <article class="doc-card">
                    <div class="doc-card__top">
                        <span class="doc-card__icon"><i class="fas fa-book-open" aria-hidden="true"></i></span>
                        <span class="doc-card__id">ID {{ $manual->id }}</span>
                    </div>
                    <h2 class="doc-card__title">{{ $manual->title }}</h2>
                    <p class="doc-card__meta">{{ __('ui.manual_for') }} {{ $application->name }}</p>
                    <div class="doc-card__actions">
                        @if($canEditManuals)
                            <a href="{{ route('manuals.edit', ['application' => $application->id, 'manual' => $manual->id]) }}" class="doc-btn doc-btn--accent">
                                <i class="fas fa-pen" aria-hidden="true"></i> {{ __('words.edit') }}
                            </a>
                        @endif
                        <a href="{{ route('manuals.pdf', ['application' => $application->id, 'manual' => $manual->id]) }}" class="doc-btn doc-btn--blue" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('words.download_pdf') }}
                        </a>
                        @if(Sentinel::inRole('admin'))
                            <form action="{{ route('manuals.destroy', ['application' => $application->id, 'manual' => $manual->id]) }}" method="POST" onsubmit="return confirm(@js(__('words.sure_to_delete')));">
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
