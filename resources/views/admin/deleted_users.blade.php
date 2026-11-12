@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    Deleted Users
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link rel="stylesheet" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}"/>
    <link href="{{ asset('css/pages/tables.css') }}" rel="stylesheet" type="text/css"/>

    <style>
        body {
            background: linear-gradient(135deg, #f3f4f7, #d9e2ec);
            font-family: 'Poppins', sans-serif;
        }

        h1 {
            color:#10332C;
            text-align: center;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .content-header {
            margin-bottom: 30px;
        }

        /* Glassmorphic Section Cards */
        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        

        .card-header {
            background: linear-gradient(135deg, #E21C1C, #D91515);
            color: white;
            padding: 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
        }

        /* User List Styling */
        .user-table {
            margin-top: 10px;
        }

        .table-responsive {
            border-radius: 15px;
        }

        .table th {
            background-color: #f1f1f1;
            text-transform: uppercase;
            font-size: 14px;
            font-weight: bold;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(244, 132, 99, 0.1);
            transform: scale(1.01);
        }

        /* Floating Action Button Styles */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .fab {
            background-color:#10332C;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .fab:hover {
            background-color:#1F514D;
            transform: scale(1.15);
        }

        /* Tooltip Styling */
        .tooltip-inner {
            background-color: #FFA726;
            color: white;
        }

        .btn {
            background-color:#10332C;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .btn:hover {
            background-color:#1F514D;
        }
    </style>
@stop

{{-- Page content --}}
@section('content')

<section class="content-header">
    <h1>Deleted Users</h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <i class="livicon" data-name="home" data-size="14"></i> {{ __('admin_dashboard.page_title') }}
            </a>
        </li>
        <li><a href="#">{{ __('words.words.users') }}</a></li>
        <li class="active">Deleted Users</li>
    </ol>
</section>

<!-- Main content -->
<section class="content pl-3 pr-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <span><i class="livicon" data-name="users-remove" data-size="18"></i> Deleted Users List</span>
                    <button class="btn">Export List</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive user-table">
                        <table class="table table-hover table-striped" id="table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.first_name') }}</th>
                                    <th>{{ __('ui.last_name') }}</th>
                                    <th>{{ __('ui.user_e_mail') }}</th>
                                    <th>Deleted At</th>
                                    <th>{{ __('ui.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->first_name }}</td>
                                        <td>{{ $user->last_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->deleted_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.restore.user', $user->id) }}" data-toggle="tooltip" title="Restore User">
                                                <i class="livicon" data-name="user-flag" data-c="#6CC66C" data-size="20"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Action Buttons -->
<div class="fab-container">
    <div class="fab" data-toggle="tooltip" title="{{ __('words.add_user') }}">
        <i class="fa fa-plus"></i>
    </div>
    <div class="fab" data-toggle="tooltip" title="View Users">
        <i class="fa fa-users"></i>
    </div>
</div>

@stop

{{-- Page level scripts --}}
@section('footer_scripts')
    <script src="{{ asset('vendors/datatables/js/jquery.dataTables.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/datatables/js/dataTables.bootstrap4.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>

    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('#table').DataTable();
        });
    </script>
@stop
