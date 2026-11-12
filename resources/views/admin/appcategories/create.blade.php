@extends('admin.layouts.default')

@section('content')
    <div class="container">
        <h1>{{ __('ui.create_app_category') }}</h1>
        <a href="{{ route('admin.appcategories.index') }}" class="btn btn-secondary mb-3">{{ __('ui.back_to_categories') }}</a>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.appcategories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="category_name">{{ __('ui.category_name') }}</label>
                <input type="text" name="category_name" id="category_name" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">{{ __('ui.save_category') }}</button>
        </form>
    </div>
@endsection
