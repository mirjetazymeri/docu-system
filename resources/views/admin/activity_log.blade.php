@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    Activity Log
    @parent
@stop

{{-- Page level styles --}}
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}"/>
    <link href="{{ asset('css/pages/tables.css') }}" rel="stylesheet" type="text/css"/>
@stop

{{-- Page content --}}
@section('content')
    <section class="content-header">
        <h1>Activity log</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('admin_dashboard.page_title') }}
                </a>
            </li>
            <li class="breadcrumb-item active">Activity log</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content pr-3 pl-3">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title">
                            <i class="livicon" data-name="user" data-size="16" data-loop="true" data-c="#fff" data-hc="white"></i>
                            Activity Log
                        </h4>
                    </div>
                    <div class="card-body">
                        <table id="table" class="table table-bordered dataTable no-footer table-responsive-lg table-responsive-sm table-responsive-md" cellspacing="0" width="100%">
                            <thead>
                                <tr class="filters">
                                    <th>UserID</th>
                                    <th>User Name</th>
                                    <th>{{ __('words.description') }}</th>
                                    <th>Attributes</th>
                                    <th>Old Values</th>
                                    <th>{{ __('ui.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- row -->
    </section>
@stop

{{-- Page level scripts --}}
@section('footer_scripts')
    <script type="text/javascript" src="{{ asset('vendors/datatables/js/jquery.dataTables.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script type="text/javascript" src="{{ asset('vendors/datatables/js/dataTables.bootstrap4.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(function() {
            $('#table').DataTable({
                "order": [[5, "desc"]],
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.activity_log.data') }}",
                columns: [
                    { data: 'causer_id', name: 'causer_id' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'description', name: 'description' },
                    { data: 'attributes', name: 'attributes', render: function(data) {
                        // Optional: Render data as preformatted text
                        return '<pre>' + data + '</pre>';
                    }},
                    { data: 'old', name: 'old', render: function(data) {
                        // Optional: Render data as preformatted text
                        return '<pre>' + data + '</pre>';
                    }},
                    { data: 'created_at', name: 'created_at' }
                ]
            });
        });
    </script>
@stop
