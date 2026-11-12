@extends('admin.layouts.default')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <!-- Create Device Type Button inline with the table -->
        <a href="{{ route('admin.device-types.create') }}" class="btn btn-dark rounded-pill px-3 py-2 shadow-lg d-inline">
            <i class="fa fa-plus-circle"></i> <strong>{{ __('words.add_new_device_type') }}</strong>
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive" style="max-width: 80%; margin: 0 auto;">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 150px; font-size: 0.9rem;">{{ __('words.name') }}</th> <!-- Smaller width for Name -->
                    <th style="width: 150px;">{{ __('words.actions') }}</th> <!-- Adjusted width of the Actions column -->
                </tr>
            </thead>
            <tbody>
                @foreach($deviceTypes as $deviceType)
                    <tr class="table-row-hover">
                        <td style="width: 150px; font-size: 0.9rem;">{{ $deviceType->name }}</td> <!-- Smaller width for Name -->
                        <td>
                            @include('admin.device_type.actions', ['deviceType' => $deviceType])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
