@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_supplier') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_supplier') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.add_supplier') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_supplier') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.suppliers.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">{{ __('words.name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                        @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="nui">{{ __('words.nui') }}</label>
                        <input type="text" name="nui" id="nui" class="form-control" value="{{ old('nui') }}">
                        @if($errors->has('nui'))
                            <span class="text-danger">{{ $errors->first('nui') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="contact_person">{{ __('words.contact_person') }}</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person') }}">
                        @if($errors->has('contact_person'))
                            <span class="text-danger">{{ $errors->first('contact_person') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('words.phone') }}</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                        @if($errors->has('phone'))
                            <span class="text-danger">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('words.email') }}</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                        @if($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="city">{{ __('words.city') }}</label>
                        <textarea name="city" id="city" class="form-control">{{ old('city') }}</textarea>
                        @if($errors->has('city'))
                            <span class="text-danger">{{ $errors->first('city') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="address">{{ __('words.address') }}</label>
                        <textarea name="address" id="address" class="form-control">{{ old('address') }}</textarea>
                        @if($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_supplier') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
