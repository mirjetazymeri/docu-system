@if($errors->any())
    <div class="testing-alert testing-alert--danger" role="alert">
        <strong>{{ __('ui.check_the_form_details') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="testing-alert testing-alert--danger" role="alert">{{ session('error') }}</div>
@endif

<div class="testing-form-grid">
    <div class="testing-field">
        <label for="application_id">{{ __('ui.application') }}</label>
        <select id="application_id" name="application_id" class="form-control" required>
            <option value="">{{ __('ui.select_application') }}</option>
            @foreach($applications as $application)
                <option value="{{ $application->id }}" @selected((string) old('application_id', $selectedApplicationId) === (string) $application->id)>
                    {{ $application->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="testing-field">
        <label for="tester_name">{{ __('ui.tester_name') }}</label>
        <input type="text" id="tester_name" name="tester_name" class="form-control"
            value="{{ old('tester_name', $testerName) }}" maxlength="255" required>
    </div>
    <div class="testing-field">
        <label for="version">{{ __('ui.version') }}</label>
        <input type="text" id="version" name="version" class="form-control"
            value="{{ old('version', $version) }}" maxlength="100" placeholder="{{ __('ui.e_g_v1_2_0') }}" required>
    </div>
    <div class="testing-field">
        <label for="test_date">{{ __('ui.test_date') }}</label>
        <input type="date" id="test_date" name="test_date" class="form-control"
            value="{{ old('test_date', $testDate) }}" required>
    </div>
</div>

<section class="testing-section">
    <div class="testing-section__header">
        <div>
            <h2>{{ __('ui.test_cases') }}</h2>
            <small class="text-muted">{{ __('ui.add_modules_and_testing_results') }}</small>
        </div>
    </div>

    <div id="testing-details-container">
        @foreach($detailRows as $key => $detail)
            <div class="testing-detail"
                data-reference="{{ $detail['reference'] ?? '' }}">
                @if(!empty($detail['id']))
                    <input type="hidden" name="testing_details[{{ $key }}][id]" value="{{ $detail['id'] }}">
                @endif
                <div class="testing-detail__header">
                    <span class="testing-detail__number">
                        {{ !empty($detail['reference'])
                            ? $detail['reference']
                            : __('ui.test_case_number_prefix') . $loop->iteration }}
                    </span>
                    <button type="button" class="testing-btn testing-btn--danger testing-btn--small js-remove-detail">
                        <i class="fas fa-times" aria-hidden="true"></i> {{ __('ui.remove') }}
                    </button>
                </div>
                <div class="testing-detail__grid">
                    <div class="testing-field">
                        <label>{{ __('ui.category') }}</label>
                        <select name="testing_details[{{ $key }}][category]" class="form-control" required>
                            @foreach(['UI', 'UX', 'FUNCTIONALITY', 'LIGHTHOUSE'] as $category)
                                <option value="{{ $category }}" @selected(($detail['category'] ?? '') === $category)>{{ __('application_testing_pdf.category_' . strtolower($category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="testing-field">
                        <label>{{ __('ui.module') }}</label>
                        <select name="testing_details[{{ $key }}][unit_id]" class="form-control js-unit-select"
                            data-selected="{{ $detail['unit_id'] ?? '' }}" required>
                            <option value="">{{ __('ui.select_module') }}</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string) ($detail['unit_id'] ?? '') === (string) $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="testing-field">
                        <label>{{ __('ui.test_case') }}</label>
                        <input type="text" name="testing_details[{{ $key }}][test_case]" class="form-control"
                            value="{{ $detail['test_case'] ?? '' }}" maxlength="255" required>
                    </div>
                    <div class="testing-field">
                        <label>{{ __('ui.result') }}</label>
                        <select name="testing_details[{{ $key }}][result]" class="form-control" required>
                            <option value="passed" @selected(($detail['result'] ?? '') === 'passed')>{{ __('ui.passed') }}</option>
                            <option value="not_passed" @selected(($detail['result'] ?? '') === 'not_passed')>{{ __('ui.not_passed') }}</option>
                            <option value="in_process" @selected(($detail['result'] ?? '') === 'in_process')>{{ __('ui.in_process') }}</option>
                        </select>
                    </div>
                    <div class="testing-field testing-detail__comment">
                        <label>{{ __('ui.comment') }}</label>
                        <textarea name="testing_details[{{ $key }}][comment]" class="form-control" rows="5" maxlength="10000">{{ $detail['comment'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="testing-add-detail">
        <button type="button" id="add-testing-detail" class="testing-btn testing-btn--outline">
            <i class="fas fa-plus" aria-hidden="true"></i> {{ __('ui.add_another_test_case') }}
        </button>
    </div>
</section>

<template id="testing-detail-template">
    <div class="testing-detail">
        <div class="testing-detail__header">
            <span class="testing-detail__number"></span>
            <button type="button" class="testing-btn testing-btn--danger testing-btn--small js-remove-detail">
                <i class="fas fa-times" aria-hidden="true"></i> {{ __('ui.remove') }}
            </button>
        </div>
        <div class="testing-detail__grid">
            <div class="testing-field">
                <label>{{ __('ui.category') }}</label>
                <select data-name="category" class="form-control" required>
                    <option value="UI">UI</option><option value="UX">UX</option><option value="FUNCTIONALITY">{{ __('ui.functionality') }}</option><option value="LIGHTHOUSE">LIGHTHOUSE</option>
                </select>
            </div>
            <div class="testing-field">
                <label>{{ __('ui.module') }}</label>
                <select data-name="unit_id" class="form-control js-unit-select" required><option value="">{{ __('ui.select_module') }}</option></select>
            </div>
            <div class="testing-field">
                <label>{{ __('ui.test_case') }}</label>
                <input type="text" data-name="test_case" class="form-control" maxlength="255" required>
            </div>
            <div class="testing-field">
                <label>{{ __('ui.result') }}</label>
                <select data-name="result" class="form-control" required>
                    <option value="passed">{{ __('ui.passed') }}</option><option value="not_passed">{{ __('ui.not_passed') }}</option><option value="in_process">{{ __('ui.in_process') }}</option>
                </select>
            </div>
            <div class="testing-field testing-detail__comment">
                <label>{{ __('ui.comment') }}</label>
                <textarea data-name="comment" class="form-control" rows="5" maxlength="10000"></textarea>
            </div>
        </div>
    </div>
</template>

<div class="testing-form-actions">
    <a href="{{ route('admin.application_testing.index') }}" class="testing-btn testing-btn--outline">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('ui.cancel') }}
    </a>
    <button type="submit" class="testing-btn testing-btn--navy">
        <i class="fas fa-save" aria-hidden="true"></i> {{ $submitLabel }}
    </button>
</div>
