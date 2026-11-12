@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_workplace') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_workplace') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li><a href="{{ route('admin.workplaces.index') }}">{{ __('words.workplaces') }}</a></li>
            <li class="active">{{ __('words.edit_workplace') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_workplace') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.workplaces.update', $workplace->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="department_id">{{ __('words.department') }}</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">{{ __('words.select_department') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $workplace->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                @endforeach
                        </select>
                        @if($errors->has('department_id'))
                            <span class="text-danger">{{ $errors->first('department_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="name">{{ __('words.name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $workplace->name) }}">
                        @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="location">{{ __('words.location') }}</label>
                        <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $workplace->location) }}">
                        @if($errors->has('location'))
                            <span class="text-danger">{{ $errors->first('location') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_workplace_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
