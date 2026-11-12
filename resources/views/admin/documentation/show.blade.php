
@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_company') }}
    @parent
@stop


@section('title', __('ui.documentation_for_modul') . ' ' . $unit->name)

@section('content')
    <h1>{{ __('ui.documentation_for_modul') }} {{ $unit->name }}</h1>
    
    <h2>{{ __('words.details') }}</h2>
    <p>{{ __('ui.this_section_contains_documentation_specific_to_the_unit') }}</p>

    <h2>{{ __('ui.content') }}</h2>
    <p>{{ __('ui.provide_the_details_about_the_unit_here') }}</p>

@if($canCreateDocumentations)
    <form action="{{ route('units.documentation.store', ['application' => $application->id, 'unit' => $unit->id]) }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="title">{{ __('ui.title') }}</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="documentation">{{ __('ui.documentation_content') }}</label>
        <textarea name="documentation" id="documentation" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ __('ui.save_documentation') }}</button>
</form>
@else
    <div class="alert alert-info" role="status">
        <i class="fa fa-eye" aria-hidden="true"></i>
        {{ __('documentation_workers.colleague_read_only') }}
    </div>
@endif


@endsection
