@extends('admin.layouts.default')

@section('content')
    <h1>{{ __('ui.edit_software_test_data') }}</h1>

    <a href="{{ route('admin.software.index') }}" class="btn btn-secondary">{{ __('ui.back_to_list') }}</a>

    <form action="{{ route('admin.software.update', $softwareTest->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="application_id">{{ __('ui.application_name') }}</label>
            <select name="application_id" id="application_id" class="form-control">
                @foreach ($applications as $application)
                    <option value="{{ $application->id }}"
                        {{ $softwareTest->softwareCompany->application_id == $application->id ? 'selected' : '' }}>
                        {{ $application->name }}
                    </option>
                @endforeach
            </select>
            @error('application_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="category">{{ __('ui.category') }}</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', $softwareTest->category) }}">
        </div>

        <div class="form-group">
            <label for="module">{{ __('ui.module') }}</label>
            <input type="text" name="module" id="module" class="form-control" value="{{ old(__('ui.modules'), $softwareTest->module) }}">
            @error('module') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="feature">{{ __('ui.feature') }}</label>
            <input type="text" name="feature" id="feature" class="form-control" value="{{ old('feature', $softwareTest->feature) }}">
            @error('feature') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="result">{{ __('ui.result') }}</label>
            <select name="result" id="result" class="form-control">
                <option value="passed" {{ $softwareTest->result == 'passed' ? 'selected' : '' }}>{{ __('ui.passed') }}</option>
                <option value="not_passed" {{ $softwareTest->result == 'not_passed' ? 'selected' : '' }}>{{ __('ui.not_passed') }}</option>
                <option value="in_process" {{ $softwareTest->result == 'in_process' ? 'selected' : '' }}>{{ __('ui.in_process') }}</option>
            </select>
            @error('result') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="comment">{{ __('ui.comments') }}</label>
            <textarea name="comment" id="comment" class="form-control">{{ old('comment', $softwareTest->comment) }}</textarea>
            @error('comment') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-3">{{ __('ui.update_test_data') }}</button>
    </form>
@endsection
