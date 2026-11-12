@extends('admin.layouts.default')

@section('title')
    {{ __('words.branches') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.branches') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.branches') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.branches_list') }}</h3>
                <div class="card-tools">
                <a href="{{ route('admin.branches.create') }}" 
   class="btn" 
   style="background-color: #10332C; color: white; border-radius: 5px; padding: 10px 20px; border: none; transition: background-color 0.3s, transform 0.3s;">
   {{ __('words.add_branch') }}
</a>

                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="branches-table">
                        <thead>
                            <tr>
                                <th>{{ __('words.id') }}</th>
                                <th>{{ __('words.name') }}</th>
                                <th>{{ __('words.company') }}</th>
                                <th>{{ __('words.location') }}</th>
                                <th>{{ __('words.manager') }}</th>
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
            $('#branches-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.branches.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'company_name', name: 'company.name' },
                    { data: 'location', name: 'location' },
                    { data: 'manager', name: 'manager' },
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
