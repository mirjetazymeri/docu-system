@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_subject') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_subject') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li><a href="{{ route('admin.subjects.index') }}">{{ __('words.subjects') }}</a></li>
            <li class="active">{{ __('words.add_subject') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_subject') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.subjects.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">{{ __('words.full_name') }}</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name') }}" placeholder="{{ __('words.full_name') }}">
                            </div>

                            <div class="form-group">
                                <label for="email">{{ __('words.email') }}</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="{{ __('words.email') }}">
                            </div>

                            <div class="form-group">
                                <label for="phone">{{ __('words.phone') }}</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="{{ __('words.phone') }}">
                            </div>

                            <div class="form-group">
                                <label for="address">{{ __('words.address') }}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}" placeholder="{{ __('words.address') }}">
                            </div>

                            <div class="form-group">
                                <label for="nui">{{ __('words.nui') }}</label>
                                <input type="text" name="nui" id="nui" class="form-control" value="{{ old('nui') }}" placeholder="{{ __('words.nui') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">{{ __('words.city') }}</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}" placeholder="{{ __('words.city') }}">
                            </div>

                            <div class="form-group">
                                <label for="state">{{ __('words.state') }}</label>
                                <input type="text" name="state" id="state" class="form-control" value="{{ old('state') }}" placeholder="{{ __('words.state') }}">
                            </div>

                            <div class="form-group">
                                <label for="postal_code">{{ __('words.postal_code') }}</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code') }}" placeholder="{{ __('words.postal_code') }}">
                            </div>

                            <div class="form-group">
                                <label for="country">{{ __('words.country') }}</label>
                                <input type="text" name="country" id="country" class="form-control" value="{{ old('country') }}" placeholder="{{ __('words.country') }}">
                            </div>

                            <div class="form-group">
                                <label for="contact_person">{{ __('words.contact_person') }}</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="{{ __('words.contact_person') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="hidden" name="is_buyer" value="0">
                            <input type="checkbox" name="is_buyer" id="is_buyer" class="form-check-input" value="1" {{ old('is_buyer') ? 'checked' : '' }}>
                            <label for="is_buyer" class="form-check-label">{{ __('words.is_buyer') }}</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_supplier" value="0">
                            <input type="checkbox" name="is_supplier" id="is_supplier" class="form-check-input" value="1" {{ old('is_supplier') ? 'checked' : '' }}>
                            <label for="is_supplier" class="form-check-label">{{ __('words.is_supplier') }}</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_both" value="0">
                            <input type="checkbox" name="is_both" id="is_both" class="form-check-input" value="1" {{ old('is_both') ? 'checked' : '' }}>
                            <label for="is_both" class="form-check-label">{{ __('words.is_both') }}</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('words.add_subject') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
