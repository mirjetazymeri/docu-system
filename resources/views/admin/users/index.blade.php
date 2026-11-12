@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    {{ __('ui.users_list') }}
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}" />
    <link href="{{ asset('css/pages/tables.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
            font-family: 'Poppins', sans-serif;
        }

        h1 {
            color:#10332C;
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }


        .card-header {
            background: linear-gradient(135deg,#10332C,#1F514D);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            margin: 0;
            font-weight: 600;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        .table-hover tbody tr {
            transition: background-color 0.2s ease, transform 0.15s ease;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(244, 132, 99, 0.15);
            transform: scale(1.01);
        }

        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color:#10332C;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .fab:hover {
            background-color:#1F514D;
            transform: scale(1.1);
        }

        .modal-header {
            background: linear-gradient(135deg,#10332C,#1F514D);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-content {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn {
            background-color:#10332C;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color:#1F514D;
        }

        .btn-secondary {
            background-color: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #ccc;
        }
    </style>
@stop

{{-- Page content --}}
@section('content')
<section class="content-header">
    <h1>{{ __('ui.users_list') }}</h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <i class="livicon" data-name="home" data-size="14"></i> {{ __('admin_dashboard.page_title') }}
            </a>
        </li>
        <li><a href="#">{{ __('words.words.users') }}</a></li>
        <li class="active">{{ __('ui.users_list') }}</li>
    </ol>
</section>

<!-- Main content -->
<section class="content pl-3 pr-3">
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="livicon" data-name="user" data-size="18"></i> {{ __('ui.users_list') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('ui.first_name') }}</th>
                                    <th>{{ __('ui.last_name') }}</th>
                                    <th>{{ __('ui.user_e_mail') }}</th>
                                    <th>{{ __('documentation_workers.status') }}</th>
                                    <th>{{ __('ui.created_at') }}</th>
                                    <th>{{ __('ui.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Action Button -->
<a href="{{ route('admin.users.create') }}" class="fab" data-toggle="tooltip" title="{{ __('words.add_user') }}" aria-label="{{ __('ui.create_new_user') }}">
    <i class="fa fa-plus"></i>
</a>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_confirm" tabindex="-1" role="dialog" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="deleteLabel">{{ __('ui.delete_user') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('ui.are_you_sure_you_want_to_delete_this_user_this_action_is_irreversible') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('button.cancel') }}</button>
                <a type="button" class="btn btn-danger Remove_square">{{ __('button.delete') }}</a>
            </div>
        </div>
    </div>
</div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
<script src="{{ asset('vendors/datatables/js/jquery.dataTables.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<script src="{{ asset('vendors/datatables/js/dataTables.bootstrap4.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('admin.users.data') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'first_name', name: 'first_name' },
                { data: 'last_name', name: 'last_name' },
                { data: 'email', name: 'email' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });

        $('#delete_confirm').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var recipient = button.data('id');
            $(this).find('.modal-footer a').prop('href', '/admin/users/' + recipient + '/delete');
        });

        table.on('draw', function () {
            $('.livicon').updateLivicon();
        });
    });
</script>
@stop
