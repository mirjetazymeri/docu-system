@extends('admin.layouts.default')

@section('content')
    <h1 class="mb-4" style="font-size: 2rem; font-weight: bold;">{{ __('ui.add_brand') }}</h1>

    <form action="{{ route('admin.brands.store') }}" method="POST" class="mx-auto" style="max-width: 600px;">
        @csrf

        <div class="form-group mb-4">
            <label for="name" class="font-weight-bold">{{ __('ui.name') }}</label>
            <input type="text" name="name" id="name" class="form-control form-control-lg" required style="padding: 10px;">
        </div>

        <button type="submit" class="btn btn-success btn-lg" style="width: 100px; padding: 8px 16px; background-color: #091b0f; color: white; font-size: 1.1rem; border-radius: 5px; ">
            {{ __('button.save') }}
        </button>
    </form>
@endsection
