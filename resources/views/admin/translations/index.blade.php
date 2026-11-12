@extends('admin.layouts.default')

@section('title')
    {{ __('words.translations') }}
    @parent
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/tables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/datatables/css/dataTables.bootstrap4.css') }}">
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.translations') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.translations') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.translation_list') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.translations.create') }}" class="btn btn-primary">{{ __('words.add_translation') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="translations-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('words.key') }}</th>
                                @foreach ($languages as $lang)
                                    <th>{{ strtoupper($lang) }}</th>
                                @endforeach
                                <th>{{ __('words.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($translations['en'] ?? [] as $key => $translation)
                                <tr>
                                    <td>{{ $key }}</td>
                                    @foreach ($languages as $lang)
                                        <td>{{ $translations[$lang][$key] ?? '' }}</td>
                                    @endforeach
                                    <td>
                                        <a href="{{ route('admin.translations.edit', $key) }}" class="btn btn-warning btn-sm">{{ __('words.edit') }}</a>
                                    </td>
                                </tr>
                            @endforeach
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
            $('#translations-table').DataTable({
                responsive: true,
                autoWidth: false,
                paging: true,
                searching: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: @js(__('ui.search_translations'))
                }
            });
        });
    </script>
@stop
