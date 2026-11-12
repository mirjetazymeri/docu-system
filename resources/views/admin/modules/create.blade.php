@extends('admin.layouts.default')

@section('title', __('ui.create_module'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('ui.create_new_module') }}</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.modules.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">{{ __('ui.name') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="description">{{ __('words.description') }}</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('ui.create_module') }}</button>
                <a href="{{ route('admin.access-levels.index') }}" class="btn btn-secondary">{{ __('button.cancel') }}</a>
            </form>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
