@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_translation') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_translation') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li>
                <a href="{{ route('admin.translations.index') }}">{{ __('words.translations') }}</a>
            </li>
            <li class="active">{{ __('words.add_translation') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_translation') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.translations.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="key">{{ __('words.translation_key') }}</label>
                        <input type="text" id="key" name="key" class="form-control" required>
                    </div>

                    @foreach ($languages as $lang)
                        <div class="form-group">
                            <label for="{{ $lang }}_translation">{{ strtoupper($lang) }} {{ __('words.translation') }}</label>
                            <input type="text" id="{{ $lang }}_translation" name="translations[{{ $lang }}]" class="form-control" required>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary">{{ __('words.add_translation') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
