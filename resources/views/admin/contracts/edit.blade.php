@extends('admin.layouts.default')

@section('title')
    {{ __('words.edit_contract') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.edit_contract') }}t</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('admin_dashboard.page_title') }}
                </a>
            </li>
            <li class="active">{{ __('words.edit_contract') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.edit_contract') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.contracts.update', $contract->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="employee_id">{{ __('words.employee') }}</label>
                        <select name="employee_id" id="employee_id" class="form-control">
                            <option value="">{{ __('words.select_employee') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $contract->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->user->full_name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('employee_id'))
                            <span class="text-danger">{{ $errors->first('employee_id') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="contract_type">{{ __('words.contract_type') }}</label>
                        <input type="text" name="contract_type" id="contract_type" class="form-control" value="{{ old('contract_type', $contract->contract_type) }}">
                        @if($errors->has('contract_type'))
                            <span class="text-danger">{{ $errors->first('contract_type') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="start_date">{{ __('words.start_date') }}</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $contract->start_date) }}">
                        @if($errors->has('start_date'))
                            <span class="text-danger">{{ $errors->first('start_date') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="end_date">{{ __('words.end_date') }}</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $contract->end_date) }}">
                        @if($errors->has('end_date'))
                            <span class="text-danger">{{ $errors->first('end_date') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="salary">{{ __('words.salary') }}</label>
                        <input type="number" name="salary" id="salary" class="form-control" value="{{ old('salary', $contract->salary) }}">
                        @if($errors->has('salary'))
                            <span class="text-danger">{{ $errors->first('salary') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="details">{{ __('words.details') }}</label>
                        <textarea name="details" id="details" class="form-control">{{ old('details', $contract->details) }}</textarea>
                        @if($errors->has('details'))
                            <span class="text-danger">{{ $errors->first('details') }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.update_contract') }}</button>
                </form>
            </div>
        </div>
    </section>
@stop
