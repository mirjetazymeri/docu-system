@extends('admin.layouts.default')
@section('content')
    <h1>{{ __('ui.add_software_test_data') }}</h1>
    <form action="{{ route('admin.software.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="application_id">{{ __('ui.application_name') }}</label>
            <select name="application_id" id="application_id" class="form-control" required>
                <option value="">{{ __('ui.select_application') }}</option>
                @foreach($applications as $application)
                    <option value="{{ $application->id }}">{{ $application->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="tests-container">
            <!-- Dynamic test fields will go here -->

            <div class="test-fields">
                <div class="form-group">
                    <label for="category">{{ __('ui.category') }}</label>
                    <input type="text" name="tests[0][category]" class="form-control">
                </div>
                <div class="form-group">
                    <label for="module">{{ __('ui.module') }}</label>
                    <input type="text" name="tests[0][module]" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="feature">{{ __('ui.feature') }}</label>
                    <input type="text" name="tests[0][feature]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="result">{{ __('ui.result') }}</label>
                    <select name="tests[0][result]" class="form-control" required>
                        <option value="in_process">{{ __('ui.in_process') }}</option>
                        <option value="passed">{{ __('ui.passed') }}</option>
                        <option value="not_passed">{{ __('ui.not_passed') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comment">{{ __('ui.comment') }}</label>
                    <textarea name="tests[0][comment]" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-info" id="add-test">{{ __('ui.add_another_test') }}</button>
        <button type="submit" class="btn btn-success">{{ __('button.save') }}</button>
    </form>

    <script>
        let testCount = 1;

        document.getElementById('add-test').addEventListener('click', function () {
            const container = document.getElementById('tests-container');
            const newTest = document.createElement('div');
            newTest.classList.add('test-fields');
            newTest.innerHTML = `
                <div class="form-group">
                    <label for="module">Module</label>
                    <input type="text" name="tests[${testCount}][module]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="feature">Feature</label>
                    <input type="text" name="tests[${testCount}][feature]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="result">Result</label>
                    <select name="tests[${testCount}][result]" class="form-control" required>
                        <option value="in_process">In Process</option>
                        <option value="passed">Passed</option>
                        <option value="not_passed">Not Passed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comment">Comment</label>
                    <textarea name="tests[${testCount}][comment]" class="form-control"></textarea>
                </div>
            `;
            container.appendChild(newTest);
            testCount++;
        });
    </script>
@endsection
