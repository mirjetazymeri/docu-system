@extends('admin.layouts.default')
@section('content')
    <h1>{{ __('ui.edit_device_type') }}</h1>

    <form action="{{ route('admin.device-types.update', $deviceType->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">{{ __('words.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $deviceType->name }}" required>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('words.update') }}</button>
    </form>
@endsection
