@extends('admin.layouts.default')

@section('header_styles')
    <link href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet" />
    <style>
        /* Ensure dropdown text is visible */
        .custom-dropdown {
            color: #333;
            /* Dark text color for better contrast */
        }

        .custom-dropdown option {
            color: #333;
            /* Ensuring options have dark text color */
        }
    </style>
@stop

@section('title', __('ui.add_new_audit'))

@section('content')
    <div class="container">
        <h1>{{ __('ui.add_new_audit') }}</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.audits.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="audit_title_id" class="font-weight-bold">{{ __('ui.audit_title') }}</label>
                <select name="audit_title_id" id="audit_title_id" class="form-control" required>
                    <option value="">{{ __('ui.select_audit_title') }}</option>
                    @foreach ($auditTitles as $auditTitle)
                        <option value="{{ $auditTitle->id }}"
                            {{ old('audit_title_id') == $auditTitle->id ? 'selected' : '' }}>
                            {{ $auditTitle->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Created Date Field -->
            <div class="form-group">
                <label for="created_date" class="font-weight-bold">{{ __('ui.created_date') }}</label>
                <input type="date" name="created_date" id="created_date" class="form-control"
                    value="{{ old('created_date') }}">
            </div>

            <!-- Company Field -->
            <div class="form-group">
                <label for="company_id" class="font-weight-bold">{{ __('ui.company') }}</label>
                <select name="company_id" id="company_id" class="form-control custom-dropdown" required>
                    <option value="">{{ __('ui.select_company') }}</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch Field -->
            <div class="form-group">
                <label for="branch_id" class="font-weight-bold">{{ __('ui.branch') }}</label>
                <select name="branch_id" id="branch_id" class="form-control custom-dropdown">
                    <option value="">{{ __('ui.none') }}</option> <!-- "None" option for no branch -->
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Audit Description Field (with Summernote Editor) -->
            <div class="form-group">
                <label for="content" class="font-weight-bold">{{ __('words.audit_description') }}</label>
                <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit" class="btn"
                    style="background-color: #10332C; color: white; border-radius: 5px; padding: 10px 20px; border: none; transition: background-color 0.3s, transform 0.3s;">
                    {{ __('ui.save_audit') }}
                </button>
            </div>

        </form>

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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
