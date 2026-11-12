@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_contract') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_contract') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.add_contract') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_contract') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.contracts.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="employee_id">{{ __('words.employee') }}</label>
                        <select name="employee_id" id="employee_id" class="form-control">
                            <option value="">{{ __('words.select_employee') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->user->full_name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('employee_id'))
                            <span class="text-danger">{{ $errors->first('employee_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="contract_type">{{ __('words.contract_type') }}</label>
                        <input type="text" name="contract_type" id="contract_type" class="form-control" value="{{ old('contract_type') }}">
                        @if($errors->has('contract_type'))
                            <span class="text-danger">{{ $errors->first('contract_type') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="start_date">{{ __('words.start_date') }}</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
                        @if($errors->has('start_date'))
                            <span class="text-danger">{{ $errors->first('start_date') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="end_date">{{ __('words.end_date') }}</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                        @if($errors->has('end_date'))
                            <span class="text-danger">{{ $errors->first('end_date') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="salary">{{ __('words.salary') }}</label>
                        <input type="number" name="salary" id="salary" class="form-control" value="{{ old('salary') }}">
                        @if($errors->has('salary'))
                            <span class="text-danger">{{ $errors->first('salary') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="details">{{ __('words.details') }}</label>
                        <textarea name="details" id="details" class="form-control">{{ old('details') }}</textarea>
                        @if($errors->has('details'))
                            <span class="text-danger">{{ $errors->first('details') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_contract_button') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
