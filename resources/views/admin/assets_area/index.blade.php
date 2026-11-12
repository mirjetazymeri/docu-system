@extends('admin.layouts.default')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <!-- Add New Asset Button positioned lower -->
        <a href="{{ route('admin.assets-areas.create') }}" class="btn btn-dark rounded-pill px-4 py-2 shadow-lg">
            <i class="fa fa-plus-circle"></i> <strong>{{ __('ui.add_new_asset') }}</strong>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>{{ __('words.name') }}</th>
                    <th>{{ __('words.companyy') }}</th>
                    <th>{{ __('words.branch') }}</th>
                    <th>{{ __('words.department') }}</th>
                    <th>{{ __('words.workplace') }}</th>
                    <th>{{ __('words.device_type') }}</th>
                    <th>{{ __('words.brand') }}</th>
                    <th>{{ __('words.serial_number') }}</th>
                    <th>{{ __('words.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                    <tr class="table-row-hover">
                        <td>{{ $asset->id }}</td>
                        <td>{{ $asset->asset_name }}</td>
                        <td>{{ $asset->company->name ?? 'N/A' }}</td>
                        <td>{{ $asset->branch->name ?? 'N/A' }}</td>
                        <td>{{ $asset->department->name ?? 'N/A' }}</td>
                        <td>{{ $asset->workplace->name ?? 'N/A' }}</td>
                        <td>{{ $asset->deviceType->name ?? 'N/A' }}</td>
                        <td>{{ $asset->brand->name ?? 'N/A' }}</td>

                        <td>{{ $asset->serial_number }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle rounded-pill" type="button" id="actionsDropdown-{{ $asset->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i> {{ __('words.actions') }}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="actionsDropdown-{{ $asset->id }}">
                                    <a class="dropdown-item" href="{{ route('admin.assets-areas.edit', $asset->id) }}" style="color: black;">
                                        <i class="fa fa-edit"></i> {{ __('words.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.assets-areas.pdf', $asset->id) }}" style="color: black;">
                                        <i class="fa fa-file-pdf"></i> {{ __('ui.view_as_pdf') }}
                                    </a>
                                    <form action="{{ route('admin.assets-areas.destroy', $asset->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm(@js(__('words.confirm_delete')));" style="color: black;">
                                            <i class="fa fa-trash"></i> {{ __('words.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
