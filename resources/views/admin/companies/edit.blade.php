@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_company') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_company') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li><a href="{{ route('admin.companies.index') }}">{{ __('words.companies') }}</a></li>
            <li class="active">{{ __('words.edit_company') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_company') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.companies.update', $company->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">{{ __('words.name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $company->name) }}">
                        @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="description">{{ __('words.description') }}</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description', $company->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('words.email') }}</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $company->email) }}">
                        @if($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="phone_number">{{ __('words.phone_number') }}</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $company->phone_number) }}">
                        @if($errors->has('phone_number'))
                            <span class="text-danger">{{ $errors->first('phone_number') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="website">{{ __('words.website') }}</label>
                        <input type="text" name="website" id="website" class="form-control" value="{{ old('website', $company->website) }}">
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_company_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
