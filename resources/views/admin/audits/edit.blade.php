@extends('admin.layouts.default')

@section('header_styles')
    <link href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet" />
@stop

@section('title')
    {{ __('words.edit_audit') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14"
                        data-color="#000"></i>{{ __('admin_dashboard.page_title') }}</a></li>
            <li><a href="{{ route('admin.audits.index') }}">{{ __('words.audit_list') }}</a></li>
            <li class="active">{{ __('words.edit_audit') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="container">
            <div class="card shadow-lg rounded">
                <div class="card-header bg-light">
                    <h3 class="card-title text-dark font-weight-bold">{{ __('words.edit_audit') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.audits.update', $audit->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Audit Name Field -->
                        <div class="form-group">
                            <label for="audit_name" class="font-weight-bold">{{ __('words.audit_name') }}</label>
                            <input type="text" id="audit_name" name="title" class="form-control"
                                value="{{ old('title', $audit->title) }}" required>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Company Field -->
                        <div class="form-group">
                            <label for="company_id" class="font-weight-bold">{{ __('words.company') }}</label>
                            <select id="company_id" name="company_id" class="form-control" required>
                                <option value="">{{ __('words.select_company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $audit->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Branch Field -->
                        <div class="form-group">
                            <label for="branch_id" class="font-weight-bold">{{ __('words.branch') }}</label>
                            <select id="branch_id" name="branch_id" class="form-control" required>
                                <option value="">{{ __('words.select_branch') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $audit->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Audit Description Field with Summernote Editor -->
                        <div class="form-group">
                            <label for="audit_description"
                                class="font-weight-bold">{{ __('words.audit_description') }}</label>
                            <textarea id="audit_description" name="content" class="form-control" rows="5" required>{{ old('content', $audit->content) }}</textarea>
                            @error('content')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">{{ __('words.update') }}</button>
                        <a href="{{ route('admin.audits.index') }}" class="btn btn-secondary">{{ __('words.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer_scripts')
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(document).ready(function() {
            // Initialize Summernote for Audit Description
            $('#audit_description').summernote({
                height: '300px', // Set the height of the editor to 300 pixels
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ] // Customize toolbar as needed
            });
        });
    </script>
@stop
