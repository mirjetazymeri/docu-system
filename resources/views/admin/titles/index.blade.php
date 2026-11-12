@extends('admin.layouts.default')

@section('title', __('ui.audit_titles'))

@section('content')
    <div class="container my-5">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary font-weight-bold">{{ __('ui.audit_titles') }}</h1>
            <a href="{{ route('admin.audit-titles.create') }}" class="btn" style="background-color: #10332C; color: white;">
                <i class="fas fa-plus"></i> {{ __('ui.create_new_title') }}
            </a>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
            </div>
        @endif

        <!-- Audit Titles Table -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">{{ __('ui.audit_titles_list') }}</h4>
            </div>
            <div class="card-body">
                @if ($titles->isEmpty())
                    <p class="text-center text-muted">{{ __('ui.no_audit_titles_found') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th scope="col">Id</th>
                                    <th scope="col">{{ __('ui.title') }}</th>
                                    <th scope="col">{{ __('ui.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($titles as $index => $title)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $title->name }}</td>
                                        <td>
                                            @include('admin.titles.actions', [
                                                'id' => $title->id,
                                                'editRoute' => route('admin.audit-titles.edit', $title->id),
                                                'deleteRoute' => route('admin.audit-titles.destroy', $title->id)
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
