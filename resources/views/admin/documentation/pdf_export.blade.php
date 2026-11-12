<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDF — {{ $application->name }}</title>
    <style>
        body { margin: 0; background: #f4f7fb; color: #25344d; font: 16px/1.6 system-ui, sans-serif; }
        main { max-width: 650px; margin: 10vh auto; padding: 32px; background: white; border: 1px solid #dce4ef; border-radius: 14px; }
        h1 { margin-top: 0; font-size: 26px; } progress { width: 100%; height: 20px; accent-color: #4168ae; }
        #pdf-status { overflow-wrap: anywhere; } .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .button { padding: 10px 18px; border: 0; border-radius: 7px; background: #4168ae; color: white; font: inherit; text-decoration: none; cursor: pointer; }
        [hidden] { display: none !important; } .muted { color: #64748b; } #pdf-error { color: #a12727; }
        @media(max-width: 700px) { main { margin: 20px; padding: 22px; } }
    </style>
</head>
<body>
<main>
    <p class="muted">{{ __('ui.complete_documentation') }}</p>
    <h1>{{ $application->name }}</h1>
    <progress id="pdf-progress" value="0" max="{{ count($manifest['parts']) }}" aria-label="{{ __('ui.pdf_progress') }}"></progress>
    <p id="pdf-status" role="status" aria-live="polite">{{ __('ui.preparing_the_pdf') }}</p>
    <p id="pdf-hint" class="muted">{{ __('ui.keep_this_page_open_the_documents_will_be_merged_into_a_single_pdf') }}</p>
    <p id="pdf-error" role="alert" hidden></p>
    <div class="actions">
        <a id="pdf-open" class="button" target="_blank" rel="noopener" hidden>{{ __('ui.open_pdf') }}</a>
        <a id="pdf-download" class="button" hidden>{{ __('ui.download_pdf') }}</a>
        <button id="pdf-retry" class="button" type="button" hidden>{{ __('ui.try_again') }}</button>
        <a href="{{ route('units.index', $application) }}">{{ __('ui.back_to_modules') }}</a>
    </div>
    <noscript>{{ __('ui.enable_javascript_to_prepare_the_complete_pdf') }}</noscript>
</main>
<script id="pdf-export-config" type="application/json">{!! json_encode([
    'manifest' => $manifest,
    'messages' => __('pdf_export'),
    'manifestUrl' => route('documentations.generatePdfAll', ['application' => $application->id, 'manifest' => 1]),
    'workerUrl' => asset('js/application-pdf-worker.js?v=locale-1'),
    'filename' => (\Illuminate\Support\Str::slug($application->name) ?: 'application').'-documentation.pdf',
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/application-pdf-export.js?v=locale-1') }}" defer></script>
</body>
</html>
