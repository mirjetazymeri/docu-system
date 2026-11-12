@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_application') }}
    @parent
@stop

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center text-primary font-weight-bold">{{ __('words.edit_application') }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('ui.whoops') }}</strong> {{ __('ui.there_were_some_problems_with_your_input') }}<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
        </div>
    @endif

    <form action="{{ route('applications.update', $application->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This specifies the HTTP method to be PUT -->

        <div class="form-group">
            <label for="name" class="font-weight-bold">{{ __('words.application_name') }}</label>
            <input type="text" name="name" value="{{ old('name', $application->name) }}" class="form-control" required>
        </div>

        <!-- Category Dropdown -->
        <div class="form-group mt-3">
            <label for="category" class="font-weight-bold">{{ __('words.category') }}</label>
            <select name="category_id" class="form-control" required>
                <option value="" disabled {{ is_null($application->category) ? 'selected' : '' }}>
                    {{ __('words.select_category') }}
                </option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ $category->id == old('category_id', optional($application->category)->id) ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-3">
            <label class="font-weight-bold">{{ __('ui.assigned_workers') }}</label>
            <div class="worker-picker">
                @forelse($users as $user)
                    <label class="worker-picker__item" for="assigned_user_{{ $user->id }}">
                        <input type="checkbox" name="assigned_user_ids[]"
                            id="assigned_user_{{ $user->id }}" value="{{ $user->id }}"
                            @checked(in_array($user->id, old('assigned_user_ids', $assignedUserIds)))>
                        <span>
                            <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                            <small>{{ $user->email }}</small>
                        </span>
                    </label>
                @empty
                    <p class="worker-picker__empty">{{ __('ui.there_are_no_users_with_the_documentation_worker_access_level') }}</p>
                @endforelse
            </div>
            <small class="form-text text-muted">
                {{ __('ui.only_selected_workers_have_access_to_this_project_uncheck_a_worker_to_remove_their_access') }}
            </small>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-custom">{{ __('words.edit') }}</button>
            <a href="{{ route('applications.index') }}" class="btn btn-back" style="width: 127px; height: 44px;">{{ __('words.back') }}</a>
        </div>
    </form>
</div>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Arial', sans-serif;
        color: #333;
    }
    .container {
        max-width: 600px;
        margin: 0 auto; /* Center alignment */
        padding: 20px;
        background-color: #ffffff; /* White background */
        border-radius: 10px; /* Rounded corners */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }
    h1 {
        font-size: 2rem;
    }
    .form-group label {
        color: #495057; /* Dark label color */
    }
    .form-control {
        border-radius: 5px; /* Rounded corners for input */
        border: 1px solid #ced4da; /* Light border */
        transition: border-color 0.3s; /* Smooth border transition */
    }
    .form-control:focus {
        border-color: #10332C; /* Custom focus border color */
        box-shadow: 0 0 0 0.2rem rgba(75, 128, 60, 0.25); /* Focus shadow */
    }
    .btn-custom {
        background-color: #10332C; /* Custom button color */
        color: white; /* Text color */
        border: none; /* No border */
        padding: 12px 30px; /* Padding for better touch area */
        border-radius: 5px; /* Rounded corners */
        transition: background-color 0.3s, transform 0.2s; /* Smooth transitions */
    }
    .btn-custom:hover {
        background-color: #10332C; /* Darker on hover */
        transform: scale(1.05); /* Slight scale effect */
        color: white; /* Keep text white on hover */
    }
    .btn-back {
        background-color: #706C6C; /* New color for Back button */
        color: white; /* Text color */
        border: none; /* No border */
        border-radius: 5px; /* Rounded corners */
        transition: background-color 0.3s, transform 0.2s; /* Smooth transitions */
        text-align: center; /* Center text */
    }
    .btn-back:hover {
        background-color: #737373; /* Slightly darker on hover */
        transform: scale(1.05); /* Slight scale effect */
        color: white; /* Keep text white on hover */
    }
    .alert {
        border-radius: 5px; /* Rounded corners for alert */
        margin-top: 15px; /* Space above alert */
    }
    .worker-picker {
        max-height: 260px;
        overflow-y: auto;
        padding: 8px;
        background: #f7f9fc;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
    }
    .worker-picker__item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 6px;
        padding: 11px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        cursor: pointer;
    }
    .worker-picker__item:last-child { margin-bottom: 0; }
    .worker-picker__item:hover { border-color: #9fb4d5; background: #edf3fc; }
    .worker-picker__item input { width: 18px; height: 18px; }
    .worker-picker__item span { display: flex; flex-direction: column; }
    .worker-picker__item small { color: #718096; }
    .worker-picker__empty { margin: 0; padding: 12px; color: #718096; text-align: center; }
</style>
@endsection
