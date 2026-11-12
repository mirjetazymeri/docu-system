@extends('admin.layouts.default')

@section('content')
    <h1 class="mb-4" style="font-size: 2rem; font-weight: bold;">{{ __('words.create_device_type') }}</h1>

    <form action="{{ route('admin.device-types.store') }}" method="POST" class="mx-auto" style="max-width: 600px;">
        @csrf

        <!-- Device Type Name -->
        <div class="form-group mb-4">
            <label for="name" class="font-weight-bold">{{ __('words.name') }}</label>
            <input type="text" name="name" id="name" class="form-control form-control-lg" required style="padding: 10px; font-size: 1rem;">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100px; padding: 5px 10px; background-color: #031b0b; color: white; font-size: 1.1rem; border-radius: 5px; ">
            {{ __('words.create') }}
        </button>
    </form>
@endsection
