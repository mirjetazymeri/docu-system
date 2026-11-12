@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_branch') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_branch') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.add_branch') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_branch') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.branches.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="company_id">{{ __('words.company') }}</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">{{ __('words.select_company') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('company_id'))
                            <span class="text-danger">{{ $errors->first('company_id') }}</span>
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
                        <label for="location">{{ __('words.location') }}</label>
                        <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                        @if($errors->has('location'))
                            <span class="text-danger">{{ $errors->first('location') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="manager">{{ __('words.manager') }}</label>
                        <input type="text" name="manager" id="manager" class="form-control" value="{{ old('manager') }}">
                        @if($errors->has('manager'))
                            <span class="text-danger">{{ $errors->first('manager') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_branch_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
