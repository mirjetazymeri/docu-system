@extends('admin.layouts.default')

@section('title', __('ui.create_permission'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('ui.create_new_permission') }}</h3>
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

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">{{ __('ui.name') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="slug">{{ __('ui.key') }}</label>
                    <textarea id="slug" name="slug" class="form-control">{{ old('slug') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="module_id">{{ __('ui.module') }}</label>
                    <select id="module_id" name="module_id" class="form-control">
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('ui.create_permission') }}</button>
                <a href="{{ route('admin.access-levels.index') }}" class="btn btn-secondary">{{ __('button.cancel') }}</a>
            </form>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
