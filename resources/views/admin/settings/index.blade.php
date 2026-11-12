@extends('admin.layouts.default')

@section('title')
    {{ __('words.system_settings') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.system_settings') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.system_settings') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.system_settings') }}</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="app_name">{{ __('words.app_name') }}</label>
                        <input type="text" name="app_name" id="app_name" class="form-control" value="{{ old('app_name', $settings['app_name'] ?? '') }}">
                        @if($errors->has('app_name'))
                            <span class="text-danger">{{ $errors->first('app_name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="logo">{{ __('words.logo') }}</label>
                        <input type="file" name="logo" id="logo" class="form-control">
                        @if($errors->has('logo'))
                            <span class="text-danger">{{ $errors->first('logo') }}</span>
                        @endif
                        @if(isset($settings['logo']))
                            <div class="mt-2">
                                <img src="{{ Storage::url($settings['logo']) }}" alt="Logo" style="max-height: 100px;">
                            </div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_settings') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
