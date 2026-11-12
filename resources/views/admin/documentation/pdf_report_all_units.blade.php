@extends('pdf.layouts.documentation')

@section('title', __('ui.documentation_for_application') . ' ' . $application->name)

@section('content')

    <div class="content">
        <h1 class="doc-header">{{ __('ui.documentation_for_application') }} {{ $application->name }}</h1>

        @foreach($units as $unit)
            <section class="pdf-unit-section">
                <h2>{{ __('ui.module') }} {{ $unit->name }}</h2>

                @if(isset($documentations[$unit->id]) && $documentations[$unit->id]->isNotEmpty())
                    @foreach($documentations[$unit->id] as $documentation)
                        <article class="pdf-documentation-entry">
                            <h3>{{ $documentation->title }}</h3>
                            <div class="pdf-documentation-content">
                                {!! $documentation->pdf_content !!}
                            </div>
                        </article>
                    @endforeach
                @else
                    <p>{{ __('ui.no_documentation_available_for_this_unit') }}</p>
                @endif
            </section>
        @endforeach
    </div>

@endsection
