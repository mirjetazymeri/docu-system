@extends('admin.layouts.default')

@section('title', __('ui.edit_audit_title'))

@section('content')
<div class="container my-5">
    <h1 class="text-center">{{ __('ui.edit_audit_title') }}</h1>

    <form action="{{ route('admin.audit-titles.update', $auditTitle->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('ui.title_name') }}</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $auditTitle->name) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('ui.save_changes') }}</button>
    </form>
</div>
@endsection
