@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_customer') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_customer') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li><a href="{{ route('admin.customers.index') }}">{{ __('words.customers') }}</a></li>
            <li class="active">{{ __('words.edit_customer') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_customer') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="first_name">{{ __('words.first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ $customer->first_name }}">
                    </div>
                    <div class="form-group">
                        <label for="last_name">{{ __('words.last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $customer->last_name }}">
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('words.email') }}</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $customer->email }}">
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('words.phone') }}</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ $customer->phone }}">
                    </div>
                    <div class="form-group">
                        <label for="address">{{ __('words.address') }}</label>
                        <input type="text" name="address" id="address" class="form-control" value="{{ $customer->address }}">
                    </div>
                    <div class="form-group">
                        <label for="city">{{ __('words.city') }}</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ $customer->city }}">
                    </div>
                    <div class="form-group">
                        <label for="state">{{ __('words.state') }}</label>
                        <input type="text" name="state" id="state" class="form-control" value="{{ $customer->state }}">
                    </div>
                    <div class="form-group">
                        <label for="postal_code">{{ __('words.postal_code') }}</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ $customer->postal_code }}">
                    </div>
                    <div class="form-group">
                        <label for="country">{{ __('words.country') }}</label>
                        <input type="text" name="country" id="country" class="form-control" value="{{ $customer->country }}">
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_customer') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
