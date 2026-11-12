@extends('admin.layouts.default')

@section('title')
    {{ __('words.companies') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.companies') }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}"><i class="livicon" data-name="home" data-size="14" data-color="#000"></i> {{ __('words.dashboard') }}</a></li>
            <li class="active">{{ __('words.companies') }}</li>
        </ol>
    </section>




    
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.companies_list') }}</h3>
                <div class="card-tools">
                <a href="{{ route('admin.companies.create') }}" 
   class="btn" 
   style="background-color: #10332C; color: white; padding: 8px 16px; text-transform: uppercase; border: none; border-radius: 5px; transition: background-color 0.3s ease;">
    {{ __('words.add_company') }}
</a>

                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="companies-table">
                        <thead>
                            <tr>
                                <th>{{ __('words.id') }}</th>
                                <th>{{ __('words.name') }}</th>
                                <th>{{ __('words.email') }}</th>
                                <th>{{ __('words.phone_number') }}</th>
                                <th>{{ __('words.website') }}</th>
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
            $('#companies-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.companies.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'website', name: 'website' },
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

<style>
    .btn:hover {
    background-color: #5A9E82; /* A slightly darker green */
    color: #fff; /* Ensures text remains white */
    text-decoration: none;
}

</style>