@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_unit') }} @parent
@stop

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center text-primary font-weight-bold">{{ __('words.edit') }}</h1>
    
    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('ui.oops_please_fix_the_following_errors') }}</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
        </div>
    @endif

    <!-- Form for editing the unit -->
    @if(Sentinel::getUser()->hasAccess('unit.edit'))
    <div class="card border-light shadow-lg rounded-lg">
        <div class="card-body">
            <form action="{{ route('units.update', ['application' => $application->id, 'unit' => $unit->id]) }}" method="POST">
                @csrf
                @method('PUT') <!-- Use PUT for updates -->
                
                <div class="form-group mb-4">
                    <label for="name" class="font-weight-bold">{{ __('ui.unit_name') }}</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $unit->name) }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-custom-add">{{ __('words.edit') }}</button>
                    <a href="{{ route('units.index', $application->id) }}" class="btn btn-secondary">{{ __('button.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
        color: #343a40;
    }
    .container {
        max-width: 600px; /* Set a max-width for better form alignment */
        padding: 30px;
        border-radius: 15px;
        background-color: #ffffff;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.15);
    }
    h1 {
        font-size: 2rem;
    }
    .card {
        border-radius: 10px;
    }
    .btn-custom-add {
        background-color: #10332C;
        color: white; /* Text color */
        border: none; /* No border */
        padding: 10px 20px; /* Increased padding for a better touch target */
        border-radius: 5px; /* Rounded corners */
        transition: background-color 0.3s, transform 0.3s;
    }
    .btn-custom-add:hover {
        color: white;
        background-color: #10332C; /* Darker on hover */
        transform: scale(1.05); /* Slight scale on hover */
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white; /* Text color */
        border: none; /* No border */
        padding: 10px 20px; /* Increased padding for a better touch target */
        border-radius: 5px; /* Rounded corners */
        transition: background-color 0.3s, transform 0.3s;
    }
    .btn-secondary:hover {
        color: white;
        background-color: #5a6268; /* Darker on hover */
        transform: scale(1.05); /* Slight scale on hover */
    }
</style>
@endsection
