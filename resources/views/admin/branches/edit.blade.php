@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_branch') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_branch') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li><a href="{{ route('admin.branches.index') }}">{{ __('words.branches') }}</a></li>
            <li class="active">{{ __('words.edit_branch') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_branch') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.branches.update', $branch->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="company_id">{{ __('words.company') }}</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">{{ __('words.select_company') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $branch->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('company_id'))
                            <span class="text-danger">{{ $errors->first('company_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="name">{{ __('words.name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $branch->name) }}">
                        @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="location">{{ __('words.location') }}</label>
                        <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $branch->location) }}">
                        @if($errors->has('location'))
                            <span class="text-danger">{{ $errors->first('location') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="manager">{{ __('words.manager') }}</label>
                        <input type="text" name="manager" id="manager" class="form-control" value="{{ old('manager', $branch->manager) }}">
                        @if($errors->has('manager'))
                            <span class="text-danger">{{ $errors->first('manager') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_branch_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
