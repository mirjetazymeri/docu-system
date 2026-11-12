@extends('admin.layouts.default')

@section('header_styles')
    <link href="{{ asset('plugins/summernote/summernote-bs4.min.css')}}" rel="stylesheet"/>
@stop

@section('title', __('ui.add_manual'))

@section('content')
    <div class="container manual-editor-page">
        <h1>{{ __('ui.add_manual_for_application') }} {{ $application->name }}</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('manuals.store', ['application' => $application->id]) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="title">{{ __('ui.title') }}</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="manual_content">{{ __('ui.content') }}</label>
                <textarea name="content" id="manual_content" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('ui.save_manual') }}</button>
            <a href="{{ route('manuals.index', ['application' => $application->id]) }}" class="btn btn-secondary">{{ __('button.cancel') }}</a>
        </form>

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    @include('admin.manuals._editor_styles')
@endsection

@section('footer_scripts')
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>

    <script>
        $(document).ready(function() {
            $('#manual_content').summernote({
                height: '300px' // Set the height of the editor to 300 pixels
            });
        });
    </script>
@stop
