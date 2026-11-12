@extends('admin.layouts.default')

@section('title')
    {{ __('application_status.title') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
@stop

@section('content')
<main class="application-status-page">
    <header class="application-status-page__header">
        <span>{{ __('application_status.eyebrow') }}</span>
        <h1>{{ __('application_status.title') }}</h1>
        <p>{{ __('application_status.subtitle') }}</p>
    </header>

    <section class="application-status-panel">
        <div class="table-responsive">
            <table id="applicationsStatusTable" class="table application-status-table" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('application_status.application') }}</th>
                        <th>{{ __('application_status.category') }}</th>
                        <th>{{ __('application_status.testing') }}</th>
                        <th>{{ __('application_status.documentation') }}</th>
                        <th>{{ __('application_status.responsible') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                        @php($isTested = $application->completed_testings_count > 0)
                        @php($isDocumented = $application->documentation_status === 'completed')
                        <tr>
                            <td>
                                <a href="{{ route('units.index', ['application' => $application->id]) }}" class="application-status-table__name">
                                    {{ $application->name }}
                                </a>
                            </td>
                            <td>{{ $application->category->category_name ?? __('words.not_available') }}</td>
                            <td data-order="{{ $isTested ? 1 : 0 }}">
                                <span class="status-pill status-pill--{{ $isTested ? 'complete' : 'pending' }}">
                                    <i class="fas {{ $isTested ? 'fa-check-circle' : 'fa-clock' }}" aria-hidden="true"></i>
                                    {{ $isTested ? __('application_status.tested') : __('application_status.testing_pending') }}
                                </span>
                            </td>
                            <td data-order="{{ $isDocumented ? 1 : 0 }}">
                                <span class="status-pill status-pill--{{ $isDocumented ? 'complete' : 'pending' }}">
                                    <i class="fas {{ $isDocumented ? 'fa-check-circle' : 'fa-clock' }}" aria-hidden="true"></i>
                                    {{ $isDocumented ? __('application_status.documented') : __('application_status.documentation_in_process') }}
                                </span>
                            </td>
                            <td>
                                @forelse($application->users as $responsible)
                                    <span class="responsible-person">{{ trim($responsible->first_name . ' ' . $responsible->last_name) }}</span>
                                @empty
                                    <span class="responsible-person responsible-person--empty">{{ __('application_status.no_responsible') }}</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</main>

<style>
    .application-status-page{margin:24px;padding:26px;background:#f7f9fc;border-radius:18px}.application-status-page__header{margin-bottom:22px}.application-status-page__header span{color:#4f78c4;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.application-status-page__header h1{margin:5px 0;color:#24354d;font-size:28px;font-weight:850}.application-status-page__header p{margin:0;color:#718096}
    .application-status-panel{padding:20px;background:#fff;border:1px solid #dce4ef;border-radius:14px;box-shadow:0 8px 24px rgba(28,45,72,.06)}.application-status-table{margin-bottom:0!important}.application-status-table thead th{color:#5d6c80;background:#f6f8fb;border-top:0;border-bottom:1px solid #dce4ef;font-size:11px;text-transform:uppercase;letter-spacing:.04em}.application-status-table td{vertical-align:middle;border-color:#e7edf4;font-size:13px}.application-status-table__name{color:#244f8f;font-weight:800}.application-status-table__name:hover{color:#173866}
    .status-pill{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800;align-items:center;gap:6px;white-space:nowrap}.status-pill--complete{color:#17644f;background:#e0f4ec}.status-pill--pending{color:#86601d;background:#fff2d5}.responsible-person{display:inline-block;margin:2px 3px 2px 0;padding:4px 8px;color:#43546b;background:#edf2f7;border-radius:999px;font-size:10px;font-weight:700}.responsible-person--empty{color:#8a5a5a;background:#f9e8e8}
    #applicationsStatusTable_wrapper .dataTables_filter input,#applicationsStatusTable_wrapper .dataTables_length select{border:1px solid #ced8e5;border-radius:7px}#applicationsStatusTable_wrapper .dataTables_info,#applicationsStatusTable_wrapper .dataTables_paginate,#applicationsStatusTable_wrapper .dataTables_length,#applicationsStatusTable_wrapper .dataTables_filter{color:#66758a;font-size:12px}@media(max-width:600px){.application-status-page{margin:12px;padding:15px}.application-status-page__header h1{font-size:23px}}
</style>
@endsection

@section('footer_scripts')
    <script src="{{ asset('vendors/datatables/js/jquery.dataTables.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/datatables/js/dataTables.bootstrap4.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(function () {
            $('#applicationsStatusTable').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                language: {
                    search: @json(__('application_status.search')),
                    lengthMenu: @json(__('application_status.length_menu')),
                    info: @json(__('application_status.info')),
                    infoEmpty: @json(__('application_status.info_empty')),
                    zeroRecords: @json(__('application_status.zero_records')),
                    paginate: {
                        previous: @json(__('application_status.previous')),
                        next: @json(__('application_status.next'))
                    }
                }
            });
        });
    </script>
@stop
