@extends('admin.layouts.default')

@section('title')
    {{ __('words.vehicles') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.vehicles') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.vehicles') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.vehicles_list') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">{{ __('words.add_vehicle') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="vehicles-table">
                        <thead>
                            <tr>
                                <th>{{ __('words.id') }}</th>
                                <th>{{ __('words.make') }}</th>
                                <th>{{ __('words.model') }}</th>
                                <th>{{ __('words.year') }}</th>
                                <th>{{ __('words.vin') }}</th>
                                <th>{{ __('words.price') }}</th>
                                <th>{{ __('words.color') }}</th>
                                <th>{{ __('words.registration_number') }}</th>
                                <th>{{ __('words.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables will automatically populate this --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@stop

@section('footer_scripts')
    <script type="text/javascript" src="{{ asset('vendors/datatables/js/jquery.dataTables.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script type="text/javascript" src="{{ asset('vendors/datatables/js/dataTables.bootstrap4.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(document).ready(function() {
            $('#vehicles-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.vehicles.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'make', name: 'make' },
                    { data: 'model', name: 'model' },
                    { data: 'year', name: 'year' },
                    { data: 'vin', name: 'vin' },
                    { data: 'price', name: 'price' },
                    { data: 'color', name: 'color' },
                    { data: 'registration_number', name: 'registration_number' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });
        });
    </script>
@stop
