@extends('admin.layouts.default')

@section('title', __('ui.edit_testing'))

@section('content')
    @php
        $storedDetails = $application_testing->testingDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'sequence_number' => $detail->sequence_number,
                'reference' => $detail->reference,
                'category' => $detail->category,
                'unit_id' => $detail->unit_id,
                'test_case' => $detail->test_case,
                'result' => $detail->result,
                'comment' => $detail->comment,
            ];
        })->all();
        $selectedApplicationId = old('application_id', $application_testing->application_id);
        $testerName = old('tester_name', $application_testing->tester_name);
        $version = old('version', $application_testing->version);
        $testDate = old('test_date', optional($application_testing->test_date)->format('Y-m-d'));
        $detailRows = old('testing_details', $storedDetails);
        $nextDetailIndex = empty($detailRows) ? 0 : max(array_map('intval', array_keys($detailRows))) + 1;
        $submitLabel = __('ui.save_changes');
    @endphp

    <main class="testing-ui">
        <header class="testing-ui__header">
            <div>
                <span class="testing-ui__eyebrow">{{ __('ui.quality_assurance') }}</span>
                <h1 class="testing-ui__title">{{ __('ui.edit_testing') }}</h1>
                <p class="testing-ui__subtitle">
                    {{ optional($application_testing->application)->name }} · {{ $application_testing->version }}
                </p>
            </div>
            <a href="{{ route('admin.application_testing.index') }}" class="testing-btn testing-btn--outline">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back') }}
            </a>
        </header>

        <form action="{{ route('admin.application_testing.update', $application_testing) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.application_testing._form')
        </form>
    </main>

    @include('admin.application_testing._styles')
@endsection

@section('footer_scripts')
    @parent
    @include('admin.application_testing._scripts')
@endsection
