@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_vehicle') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        {{-- <h1>{{ __('words.add_vehicle') }}</h1> --}}
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.add_vehicle') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_vehicle') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vehicles.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="make">{{ __('words.make') }}</label>
                        <input type="text" name="make" id="make" class="form-control" value="{{ old('make') }}">
                        @if($errors->has('make'))
                            <span class="text-danger">{{ $errors->first('make') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="model">{{ __('words.model') }}</label>
                        <input type="text" name="model" id="model" class="form-control" value="{{ old('model') }}">
                        @if($errors->has('model'))
                            <span class="text-danger">{{ $errors->first('model') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="year">{{ __('words.year') }}</label>
                        <input type="number" name="year" id="year" class="form-control" value="{{ old('year') }}">
                        @if($errors->has('year'))
                            <span class="text-danger">{{ $errors->first('year') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="vin">{{ __('words.vin') }}</label>
                        <input type="text" name="vin" id="vin" class="form-control" value="{{ old('vin') }}">
                        @if($errors->has('vin'))
                            <span class="text-danger">{{ $errors->first('vin') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="price">{{ __('words.price') }}</label>
                        <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}">
                        @if($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="color">{{ __('words.color') }}</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}">
                        @if($errors->has('color'))
                            <span class="text-danger">{{ $errors->first('color') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="registration_number">{{ __('words.registration_number') }}</label>
                        <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number') }}">
                        @if($errors->has('registration_number'))
                            <span class="text-danger">{{ $errors->first('registration_number') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_vehicle_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
