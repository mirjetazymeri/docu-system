@extends('admin.layouts.default')

@section('title', __('ui.add_new_testing'))

@section('content')
    @php
        $selectedApplicationId = old('application_id', '');
        $testerName = old('tester_name', optional(Sentinel::getUser())->full_name ?? '');
        $version = old('version', '');
        $testDate = old('test_date', now()->format('Y-m-d'));
        $detailRows = old('testing_details', [[
            'category' => 'UI',
            'unit_id' => '',
            'test_case' => '',
            'result' => 'passed',
            'comment' => '',
        ]]);
        $nextDetailIndex = empty($detailRows) ? 0 : max(array_map('intval', array_keys($detailRows))) + 1;
        $submitLabel = __('ui.save_testing');
    @endphp

    <main class="testing-ui">
        <header class="testing-ui__header">
            <div>
                <span class="testing-ui__eyebrow">{{ __('ui.quality_assurance') }}</span>
                <h1 class="testing-ui__title">{{ __('ui.add_new_testing') }}</h1>
                <p class="testing-ui__subtitle">{{ __('ui.record_the_basic_details_and_results_for_each_module') }}</p>
            </div>
            <a href="{{ route('admin.application_testing.index') }}" class="testing-btn testing-btn--outline">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.back') }}
            </a>
        </header>

        <form action="{{ route('admin.application_testing.store') }}" method="POST">
            @csrf
            @include('admin.application_testing._form')
        </form>
    </main>

    @include('admin.application_testing._styles')
@endsection

@section('footer_scripts')
    @parent
    @include('admin.application_testing._scripts')
@endsection
