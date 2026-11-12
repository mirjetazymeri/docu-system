@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_unit') }}
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
        <li>
            <a href="{{ route('applications.index') }}">
                {{ __('words.applications') }}
            </a>
        </li>
        <li class="active">{{ __('words.add_unit') }}</li>
    </ol>
</section>

<section class="content unit-create-page">
    <div class="container">
        <div class="card shadow-lg rounded">
            <div class="card-header unit-create-page__header">
                <h3 class="card-title unit-create-page__title font-weight-bold">{{ __('words.add_unit_for_application', ['application' => $application->name]) }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('units.store', ['application' => $application->id]) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">{{ __('words.module_name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('ui.enter_module_name') }}" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-custom mt-3">{{ __('words.create_unit') }}</button>
                        <a href="{{ route('units.index', ['application' => $application->id]) }}" class="btn btn-secondary mt-3">{{ __('words.cancel') }}</a>
                    </div>
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
    .unit-create-page .unit-create-page__header {
        background-color: #334a6a !important;
    }
    .unit-create-page .unit-create-page__title,
    .unit-create-page .unit-create-page__title:hover,
    .unit-create-page .unit-create-page__title:focus {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1 !important;
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
</style>
@endsection
