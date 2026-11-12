@extends('admin.layouts.default')
@section('header_styles')
    <link href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet" />
@stop

@section('title')
    {{ __('words.audit_details') }}
    @parent
@stop

@section('content')
    <div class="container my-5">
        <h1 class="mb-4 text-center text-primary font-weight-bold">{{ $audit->title }}</h1>
        <div class="card shadow-lg p-4">
            <h5><strong>{{ __('words.company') }}:</strong> {{ $audit->company->name ?? __('words.not_available') }}</h5>
            <h5><strong>{{ __('words.branch') }}:</strong> {{ $audit->branch->name ?? __('words.not_available') }}</h5>
            <p><strong>{{ __('words.description') }}:</strong> {!! $audit->content !!}</p>
            <p><strong>{{ __('words.created_at') }}:</strong> {{ $audit->created_at->format('d M Y') }}</p>
        </div>
    </div>
@endsection
@section('footer_scripts')
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(document).ready(function() {
            // Initialize summernote for Audit Description
            $('#content').summernote({
                height: '300px' // Set the height of the editor to 300 pixels
            });
        });
    </script>
@stop
