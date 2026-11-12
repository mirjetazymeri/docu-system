@extends('admin.layouts.default')

@section('title', __('words.contracts'))

@section('header_styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.contracts') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.contracts') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="card-title">{{ __('words.contracts_list') }}</h3>
            </div>

            <div class="card-header">
                <div class="card-tools">
                    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">{{ __('words.add_contract') }}</a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="contracts_table">
                        <thead>
                            <tr>
                                <th>{{ __('words.id') }}</th>
                                <th>{{ __('words.employee') }}</th>
                                <th>{{ __('words.contract_type') }}</th>
                                <th>{{ __('words.start_date') }}</th>
                                <th>{{ __('words.end_date') }}</th>
                                <th>{{ __('words.closed_date') }}</th>
                                <th>{{ __('words.salary') }}</th>
                                <th>{{ __('words.actions') }}</th>
                            </tr>
                        </thead>
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

<script type="text/javascript">
        $(document).ready(function() {
            // Set CSRF token in AJAX headers
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#contracts_table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.contracts.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'contract_type', name: 'contract_type' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'closed_date', name: 'closed_date' },
                    { data: 'salary', name: 'salary' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
            });
        });
    </script>
@stop
