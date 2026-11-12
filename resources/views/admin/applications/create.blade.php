@extends('admin.layouts.default')

@section('title')
    {{ __('ui.add_application') }}
    @parent
@stop

@section('content')
<section class="content-header">
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                {{ __('words.dashboard') }}
            </a>
        </li>
        <li class="active">{{ __('words.add_company') }}</li>
    </ol>
</section>

<section class="content">
    <div class="container">
        <div class="card shadow-lg rounded">
            <div class="card-header bg-light">
                <h3 class="card-title text-dark font-weight-bold">{{ __('words.create_applications') }}</h3>
            </div>
            <div class="card-body">
                    <form action="{{ route('applications.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">{{ __('words.application_name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('ui.enter_application_name') }}" required>
                        </div>
                        <!-- Dropdown for Categories -->
                        <div class="form-group">
                            <label for="category" class="font-weight-bold">{{ __('words.select_category') }}</label>
                            <select name="category_id" id="category" class="form-control" required>
                                <option value="">{{ __('ui.select_category') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('ui.assign_workers') }}</label>
                            <div class="worker-picker">
                                @forelse($users as $user)
                                    <label class="worker-picker__item" for="assigned_user_{{ $user->id }}">
                                        <input type="checkbox" name="assigned_user_ids[]"
                                            id="assigned_user_{{ $user->id }}" value="{{ $user->id }}"
                                            @checked(in_array($user->id, old('assigned_user_ids', [])))>
                                        <span>
                                            <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                                            <small>{{ $user->email }}</small>
                                        </span>
                                    </label>
                                @empty
                                    <p class="worker-picker__empty">{{ __('ui.there_are_no_users_with_the_documentation_worker_access_level_yet') }}</p>
                                @endforelse
                            </div>
                            <small class="form-text text-muted">
                                {{ __('ui.no_workers_are_assigned_automatically_only_the_people_you_select_will_have_access_to_this_project') }}
                            </small>
                        </div>

                        <button type="submit" class="btn btn-custom mt-3">{{ __('words.save') }}</button>
                    </form>
            </div>
        </div>
    </div>
</section>

<style>
    body {
        background-color: #e9ecef; /* Light gray background for a fresh look */
        font-family: 'Arial', sans-serif;
        color: #333;
    }
    .content-header h1 {
        font-size: 2rem;
        margin-bottom: 15px;
    }
    .container {
        max-width: 600px; /* Center the form */
        margin: 0 auto; /* Center alignment */
    }
    .card {
        border-radius: 10px; /* Rounded corners for the card */
        margin-top: 20px; /* Space above the card */
        transition: box-shadow 0.3s ease; /* Smooth shadow transition */
    }
    .card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Elevation on hover */
    }
    .card-header {
        border-bottom: none;
        background-color: #f8f9fa; /* Light background for header */
    }
    .card-title {
        font-size: 1.5rem;
        color: #495057; /* Darker title color */
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
        color: white;
        background-color: #10332C; /* Darker on hover */
        transform: scale(1.05); /* Slight scale effect */
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
