@extends('pdf.layouts.documentation')

@section('content')
    <div class="header">
        <h1>{{ __('ui.audit_report') }}</h1>
    </div>
    <div class="details">
        <p><strong>{{ __('ui.title') }}</strong> {{ $audit->auditTitle->name }}</p>
        <p><strong>{{ __('ui.company') }}</strong> {{ $audit->company->name ?? 'Not Available' }}</p>
        <p><strong>{{ __('ui.branch') }}</strong> {{ $audit->branch->name ?? 'Not Available' }}</p>
        <p><strong>{{ __('ui.created_at') }}</strong> {{ $audit->created_at->format('d M Y') }}</p>
    </div>
    <div class="content">
        <h3>{{ __('ui.content') }}</h3>
        <p>{!! $audit->content !!}</p>
    </div>


@endsection
