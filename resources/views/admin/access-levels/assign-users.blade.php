@extends('admin.layouts.default')

@section('title', __('ui.assign_users_to_access_level'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('ui.assign_users_to') }} {{ __($accessLevel->name) }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.access-levels.assign-users', $accessLevel->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="users">{{ __('ui.select_users') }}</label>
                    <select name="users[]" id="users" class="form-control" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('ui.assign_users') }}</button>
                <a href="{{ route('admin.access-levels.index') }}" class="btn btn-secondary">{{ __('button.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#users').select2(); // Use select2 or other similar library for better user selection experience
        });
    </script>
@endsection
