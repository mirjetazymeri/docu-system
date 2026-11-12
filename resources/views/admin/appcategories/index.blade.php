@extends('admin.layouts.default')

@section('content')
    <div class="container">
        <h1>{{ __('ui.app_categories') }}</h1>
        <a href="{{ route('admin.appcategories.create') }}" class="btn btn-primary">{{ __('ui.create_category') }}</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('ui.category_name') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->category_name }}</td>
                        <td>
                            <a href="{{ route('admin.appcategories.edit', $category->id) }}" class="btn btn-warning btn-sm">{{ __('button.edit') }}</a>
                            <form action="{{ route('admin.appcategories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(@js(__('ui.are_you_sure')))">{{ __('button.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
