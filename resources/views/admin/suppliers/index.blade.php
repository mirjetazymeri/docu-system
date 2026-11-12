@extends('admin.layouts.default')

@section('title')
    {{ __('words.suppliers') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.suppliers') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.suppliers') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.suppliers_list') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">{{ __('words.add_supplier') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="suppliers-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('words.name') }}</th>
                                <th>{{ __('words.nui') }}</th>
                                <th>{{ __('words.city') }}</th>
                                <th>{{ __('words.address') }}</th>
                                <th>{{ __('words.contact_person') }}</th>
                                <th>{{ __('words.phone') }}</th>
                                <th>{{ __('words.email') }}</th>
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
            $('#suppliers-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.suppliers.index') }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'nui', name: 'nui' },
                    { data: 'city', name: 'city' },
                    { data: 'address', name: 'address' },
                    { data: 'contact_person', name: 'contact_person' },
                    { data: 'phone', name: 'phone' },
                    { data: 'email', name: 'email' },
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
