@extends('admin.layouts.default')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">

        <a href="{{ route('admin.brands.create') }}" class="btn btn-dark rounded-pill px-3 py-2 shadow-lg">
            <i class="fa fa-plus-circle"></i> <strong>{{ __('words.add_brand') }}</strong>
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
                    <th style="width: 150px; font-size: 0.9rem;">{{ __('words.name') }}</th>
                    <th style="width: 150px;">{{ __('words.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    <tr class="table-row-hover">
                        <td style="width: 150px; font-size: 0.9rem;">{{ $brand->name }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle rounded-pill p-1" type="button" id="actionsDropdown-{{ $brand->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i> {{ __('words.actions') }}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="actionsDropdown-{{ $brand->id }}">
                                    <a class="dropdown-item" href="{{ route('admin.brands.edit', $brand->id) }}" style="color: black;">
                                        <i class="fa fa-edit"></i> {{ __('words.edit') }}
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm(@js(__('ui.confirm_delete')));" style="color: black;">
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
