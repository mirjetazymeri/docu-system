@extends('admin.layouts.default')

@section('content')
    <h1>{{ __('ui.edit_brand') }}</h1>

    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">{{ __('ui.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $brand->name }}" required>
        </div>

        <button type="submit" class="btn btn-success">{{ __('button.update') }}</button>
    </form>
@endsection
