@extends('admin.layouts.default')

@section('title', __('ui.create_audit_title'))

@section('content')
<div class="container my-5">
    <h1 class="text-center">{{ __('ui.create_audit_title') }}</h1>
    <form action="{{ route('admin.audit-titles.store') }}" method="POST" class="p-4 rounded shadow bg-white">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('ui.title_name') }}</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('button.save') }}</button>
    </form>
</div>
@endsection
