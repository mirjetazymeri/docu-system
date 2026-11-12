@extends('pdf.layouts.documentation')

@section('content')
    <h1 class="document-title">{{ $documentation->title }}</h1>

    <div class="document-meta">
        <strong>{{ __('ui.application') }}</strong> {{ $application->name }}<br>
        <strong>{{ __('ui.module') }}</strong> {{ $unit->name }}
    </div>

    <div class="document-content">
        {!! $pdfContent ?? $documentation->content !!}
    </div>

    <div class="signature-section">
        <div class="signature">
            <p>{{ __('words.authorized_by') }}:</p>
            <p>{{ __('words.name') }}:</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature">
            <p>{{ __('words.signature') }}:</p>
            <div class="signature-line"></div>
        </div>
    </div>
@endsection
