@extends('admin.layouts.default')

@section('content')
    <h1>{{ __('ui.software_test_data') }}</h1>
    <a href="{{ route('admin.software.create') }}" class="btn btn-primary">{{ __('ui.add_test_data') }}</a>
    <a href="{{ route('admin.software.generatePdf') }}" class="btn btn-primary">{{ __('ui.generate_pdf_report') }}</a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <style>
        .table-container {
            max-height: 90vh;
            overflow-y: auto;
        }
        .table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }
        .table tbody tr {
            page-break-inside: avoid;
            white-space: nowrap;
        }
        @media print {
            .table tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>

    <div class="table-container">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>{{ __('ui.application_name') }}</th>
                    <th>{{ __('ui.category') }}</th>
                    <th>{{ __('ui.module') }}</th>
                    <th>{{ __('ui.feature') }}</th>
                    <th>{{ __('ui.result') }}</th>
                    <th>{{ __('ui.comment') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($softwareDetails as $detail)
                <tr>
                    <td>{{ $detail->softwareCompany->application->name }}</td>
                    <td>{{ $detail->category ?? 'N/A' }}</td> <!-- Display category -->
                    <td>{{ $detail->module }}</td>
                    <td>{{ $detail->feature }}</td>
                    <td>{{ ucfirst($detail->result) }}</td>
                    <td>{{ $detail->comment }}</td>
                    <td>
                        <a href="{{ route('admin.software.edit', $detail->id) }}" class="btn btn-warning btn-sm">{{ __('button.edit') }}</a>
                        <form action="{{ route('admin.software.destroy', $detail->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">{{ __('button.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
