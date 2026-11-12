@extends('admin.layouts.default')

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1 style="color: #000;">{{ __('words.audit_list') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14"
                        data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.audit_list') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="card-tools">
                    <a href="{{ route('admin.audits.create') }}"
                        class="btn btn-custom-primary">{{ __('words.create_audit') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="audits-table">
                        <thead>
                            <tr>
                                <th>{{ __('words.title') }}</th>
                                <th>{{ __('words.company') }}</th>
                                <th>{{ __('words.branch') }}</th>
                                <th>{{ __('words.audit_date') }}</th>

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

    <script>
        $(document).ready(function() {
            $('#audits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.audits.index') }}',
                columns: [{
                        data: 'auditTitleName',
                        name: 'auditTitle.name'
                    }, // Updated column name
                    {
                        data: 'company.name',
                        name: 'company.name'
                    },
                    {
                        data: 'branch.name',
                        name: 'branch.name'
                    },
                    {
                        data: 'created_date',
                        name: 'created_date'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });
        });
    </script>

@stop

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
        color: #343a40;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #343a40;
        color: #fff;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .card-tools .btn {
        background-color: #10332C;
        color: white;
        border-radius: 5px;
        padding: 10px 20px;
    }

    .card-tools .btn:hover {
        background-color: #0a221d;
        transform: scale(1.05);
        color: white;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .table-hover tbody tr:hover {
        background-color: #f1f1f1;
    }

    .btn-info,
    .btn-warning,
    .btn-danger {
        background-color: blue border-radius: 5px;
        padding: 6px 12px;
        margin: 2px;
        transition: background-color 0.3s, transform 0.3s;
    }

    .btn-info:hover {
        color: white;
        background-color: #007bff;
        transform: scale(1.05);
    }

    .btn-warning:hover {
        background-color: #ffc107;
        transform: scale(1.05);
    }

    .btn-danger:hover {
        background-color: #98291B;
        transform: scale(1.05);
    }
</style>
