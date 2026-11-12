@extends('admin.layouts.default')

@section('title')
    {{ __('words.title') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_vehicle') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.edit_vehicle') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_vehicle') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="make">{{ __('words.make') }}</label>
                        <input type="text" name="make" id="make" class="form-control" value="{{ old('make', $vehicle->make) }}">
                        @if($errors->has('make'))
                            <span class="text-danger">{{ $errors->first('make') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="model">{{ __('words.model') }}</label>
                        <input type="text" name="model" id="model" class="form-control" value="{{ old('model', $vehicle->model) }}">
                        @if($errors->has('model'))
                            <span class="text-danger">{{ $errors->first('model') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="year">{{ __('words.year') }}</label>
                        <input type="number" name="year" id="year" class="form-control" value="{{ old('year', $vehicle->year) }}">
                        @if($errors->has('year'))
                            <span class="text-danger">{{ $errors->first('year') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="vin">{{ __('words.vin') }}</label>
                        <input type="text" name="vin" id="vin" class="form-control" value="{{ old('vin', $vehicle->vin) }}">
                        @if($errors->has('vin'))
                            <span class="text-danger">{{ $errors->first('vin') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="price">{{ __('words.price') }}</label>
                        <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $vehicle->price) }}">
                        @if($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="color">{{ __('words.color') }}</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $vehicle->color) }}">
                        @if($errors->has('color'))
                            <span class="text-danger">{{ $errors->first('color') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="registration_number">{{ __('words.registration_number') }}</label>
                        <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number', $vehicle->registration_number) }}">
                        @if($errors->has('registration_number'))
                            <span class="text-danger">{{ $errors->first('registration_number') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_vehicle') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
