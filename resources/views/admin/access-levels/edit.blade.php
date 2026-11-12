@extends('admin.layouts.default')

@section('title', __('ui.edit_access_level'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.access-levels.update', $accessLevel->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>{{ __('ui.name') }}</label>
                    <input type="text" class="form-control" name="name" value="{{ __($accessLevel->name) }}" required>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('roles/form.permissions') }}</h5>
                        <button type="button" class="btn btn-danger btn-sm deselect_all_modules float-right">
                            <i class="fa fa-times-circle"></i> {{ __('ui.deselect_all') }}
                        </button>

                        <button type="button" class="btn btn-primary btn-sm select_all_modules float-right mr-2">
                            <i class="fa fa-check-square"></i> {{ __('ui.select_all') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($modules as $module)
                                <div class="col-lg-4">
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h5 class="card-title">{{ __(ucfirst($module->name)) }}</h5>
                                            <button type="button" class="btn btn-danger btn-sm deselect_module float-right">
                                                <i class="fa fa-times-circle"></i> {{ __('ui.deselect') }}
                                            </button>

                                            <button type="button" class="btn btn-primary btn-sm select_module float-right mr-2">
                                                <i class="fas fa-check-square"></i> {{ __('ui.select') }}
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            @foreach($module->permissions as $permission)
                                                <div class="row">
                                                    <div class="col-lg-9">
                                                        <label for="{{ $permission->key }}">{{ __(ucfirst($permission->name)) }}</label>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="switch">
                                                            <input type="checkbox" name="permissions[{{ $permission->id }}]" value="{{ $permission->id }}" id="{{ $permission->key }}"
                                                                @if(in_array($permission->id, $accessLevel->permissions->pluck('id')->toArray())) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">{{ __('ui.update_access_level') }}</button>
                <a href="{{ route('admin.access-levels.index') }}" class="btn btn-secondary mt-3">{{ __('button.cancel') }}</a>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            $('.select_all_modules').on('click', function() {
                $('input[type=checkbox]').prop('checked', true);
            });

            $('.deselect_all_modules').on('click', function() {
                $('input[type=checkbox]').prop('checked', false);
            });

            $('.select_module').on('click', function() {
                $(this).closest('.card').find('input[type=checkbox]').prop('checked', true);
            });

            $('.deselect_module').on('click', function() {
                $(this).closest('.card').find('input[type=checkbox]').prop('checked', false);
            });
        });
    </script>
@endsection
