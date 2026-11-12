@extends('admin.layouts.default')

@section('title', __('ui.add_documentation'))

@section('header_styles')
    <link href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="doc-ui documentation-editor-page">
    <header class="doc-ui__header">
        <div>
            <span class="doc-ui__eyebrow">{{ $application->name }} / {{ $unit->name }}</span>
            <h1 class="doc-ui__title">{{ __('ui.create_documentation') }}</h1>
            <p class="doc-ui__subtitle">{{ __('ui.format_the_content_as_it_should_appear_in_the_pdf') }}</p>
        </div>
        <a href="{{ route('units.documentation.index', ['application' => $application->id, 'unit' => $unit->id]) }}" class="doc-btn doc-btn--outline">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back') }}
        </a>
    </header>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('units.documentation.store', ['application' => $application->id, 'unit' => $unit->id]) }}" method="POST">
        @csrf
        <div class="form-group mb-4">
            <label for="title" class="font-weight-bold">{{ __('ui.title') }}</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
        </div>
        <div class="form-group mb-4">
            <label for="documentation" class="font-weight-bold">{{ __('ui.content') }}</label>
            <textarea name="documentation" id="documentation" class="form-control" required>{{ old('documentation') }}</textarea>
            <p class="editor-hint">{{ __('ui.use_headings_lists_and_simple_tables_for_the_best_pdf_results') }}</p>
        </div>
        <div class="d-flex flex-wrap justify-content-between" style="gap: 10px;">
            <button type="submit" class="doc-btn doc-btn--primary">
                <i class="fas fa-save" aria-hidden="true"></i> {{ __('ui.save_documentation') }}
            </button>
            <a href="{{ route('units.documentation.index', ['application' => $application->id, 'unit' => $unit->id]) }}" class="doc-btn doc-btn--outline">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>

@include('admin.partials.documentation-ui-styles')
@endsection

@section('footer_scripts')
    @include('admin.documentation._editor')
@stop
