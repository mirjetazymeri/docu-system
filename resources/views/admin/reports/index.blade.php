@extends('admin.layouts.default')

@section('title')
    {{ __('words.reports') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.reports') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14"
                        data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.reports') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card-body">
            <div class="row">
                <!-- Card for Vehicle Reports -->
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="card">
                        <i class="livicon" data-name="car" data-size="56" data-c="#1DA1F2" data-hc="#1DA1F2" data-loop="true"></i>
                        <div class="card-body">
                            <h5 class="card-title">{{ __('words.vehicle_report') }}</h5>
                            <p class="card-text">{{ __('words.vehicle_report_description') }}</p>
                            <a href="{{ route('admin.reports.vehicles') }}"
                                class="btn btn-primary">{{ __('words.view_report') }}</a>
                        </div>
                    </div>
                </div>
                <!-- Card for Employee Reports -->
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="card">
                        <i class="livicon" data-name="user" data-size="56" data-c="#1DA1F2" data-hc="#1DA1F2" data-loop="true"></i>
                        <div class="card-body">
                            <h5 class="card-title">{{ __('words.employee_report') }}</h5>
                            <p class="card-text">{{ __('words.employee_report_description') }}</p>
                            <a href="{{ route('admin.reports.employees') }}" class="btn btn-primary">{{ __('words.view_report') }}</a>
                        </div>
                    </div>
                </div>

                <!-- Card for Employee Reports -->
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="card">
                        <i class="livicon" data-name="money" data-size="56" data-c="#1DA1F2" data-hc="#1DA1F2" data-loop="true"></i>
                        <div class="card-body">
                            <h5 class="card-title">{{ __('words.expense_report') }}</h5>
                            <p class="card-text">{{ __('words.expense_report_description') }}</p>
                            <a href="{{ route('admin.reports.employees') }}"
                                class="btn btn-primary">{{ __('words.view_report') }}</a>
                        </div>
                    </div>
                </div>
                <!-- Add more cards for other reports -->
            </div>
        </div>
    </section>
@stop
