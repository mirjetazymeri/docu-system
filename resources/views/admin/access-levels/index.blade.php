@extends('admin.layouts.default')

@section('title', __('ui.access_levels'))

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h3 class="text-primary font-weight-bold">{{ __('ui.access_levels') }}</h3>
                <div>
                    <a href="{{ route('admin.access-levels.create') }}" class="btn btn-main mb-2">
                        <i class="fas fa-plus-circle"></i> {{ __('ui.new_access_level') }}
                    </a>
                    <a href="{{ route('admin.modules.create') }}" class="btn btn-outline-main mb-2">
                        <i class="fas fa-cubes"></i> {{ __('ui.new_module') }}
                    </a>
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-outline-main mb-2">
                        <i class="fas fa-shield-alt"></i> {{ __('ui.new_permission') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Access Levels Cards -->
        <div class="row">
            @forelse ($accessLevels as $accessLevel)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-light shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between p-3 bg-light">
                            <h5 class="card-title mb-0 text-primary font-weight-bold">{{ __($accessLevel->name) }}</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">{{ $accessLevel->description }}</p>
                            <div class="mb-3">
                                <strong>{{ __('ui.permissions') }}</strong>
                                <div class="permissions-list mt-2">
                                    @foreach ($accessLevel->permissions as $permission)
                                        <span class="badge badge-pill badge-primary mr-2">{{ __($permission->name) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('admin.access-levels.show', $accessLevel->id) }}" class="btn btn-link btn-sm text-primary">
                                <i class="fas fa-eye"></i> {{ __('words.view.documentation') }}
                            </a>
                            <a href="{{ route('admin.access-levels.edit', $accessLevel->id) }}" class="btn btn-link btn-sm text-primary">
                                <i class="fas fa-edit"></i> {{ __('button.edit') }}
                            </a>
                            <form action="{{ route('admin.access-levels.destroy', $accessLevel->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger" onclick="return confirm(@js(__('ui.are_you_sure')))">
                                    <i class="fas fa-trash-alt"></i> {{ __('button.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">{{ __('ui.no_access_levels_found') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        /* Overall Layout Styling */
        .container-fluid {
            margin-top: 20px;
        }

        /* Button Styling */
        .btn-main {
            background-color: #1F514D;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-main:hover {
            background-color: #1c4e48;
            transform: scale(1.05);
        }

        .btn-outline-main {
            color: #1F514D;
            border: 2px solid #1F514D;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease, transform 0.2s ease;
        }

        .btn-outline-main:hover {
            border-color: #1c4e48;
            color: #1c4e48;
            transform: scale(1.05);
        }

        /* Card Styling */
        .card {
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
        }

        /* Badge Styling for Permissions */
        .permissions-list .badge {
            font-size: 0.85rem;
            background-color: #1F514D;
            color: white;
        }

        /* Card Header */
        .card-header {
            border-bottom: 2px solid #f0f0f0;
        }

        /* Card Footer */
        .card-footer {
            border-top: 2px solid #f0f0f0;
        }

        /* Text and Spacing Adjustments */
        .text-muted {
            font-size: 1rem;
        }

        .text-primary {
            color: #1F514D;
        }

        .font-weight-bold {
            font-weight: 600;
        }

        /* Overall layout spacing */
        .row.mb-4 {
            margin-bottom: 40px;
        }
    </style>
@endsection
