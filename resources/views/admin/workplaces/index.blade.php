@extends('admin.layouts.default')

@section('title')
    {{ __('words.workplaces') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.workplaces') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.workplaces') }}</li>
        </ol>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.workplaces_list') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.workplaces.create') }}" class="btn btn-custom-primary" style="background-color: #10332C; color: white; padding: 8px 16px; text-transform: uppercase; border: none; border-radius: 5px; transition: background-color 0.3s ease;">
                        {{ __('words.add_workplace') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="workplaces-table">
                        <thead>
                            <tr>
                                <th>{{ __('words.id') }}</th>
                                <th>{{ __('words.name') }}</th>
                                <th>{{ __('words.department') }}</th>

                                <th>{{ __('words.branch') }}</th>
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
            $('#workplaces-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.workplaces.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'department_name', name: 'department.name' },
                    { data: 'branch_name', name: 'department.branch.name' },
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
