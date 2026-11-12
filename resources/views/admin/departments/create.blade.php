@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_department') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_department') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.add_department') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_department') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="branch_id">{{ __('words.branch') }}</label>
                        <select name="branch_id" id="branch_id" class="form-control">
                            <option value="">{{ __('words.select_branch') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('branch_id'))
                            <span class="text-danger">{{ $errors->first('branch_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="name">{{ __('words.name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                        @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="description">{{ __('words.description') }}</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="head">{{ __('words.head') }}</label>
                        <input type="text" name="head" id="head" class="form-control" value="{{ old('head') }}">
                        @if($errors->has('head'))
                            <span class="text-danger">{{ $errors->first('head') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_department_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
