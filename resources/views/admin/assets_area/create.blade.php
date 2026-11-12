@extends('admin.layouts.default')

@section('content')
    <h1>{{ __('ui.create_new_asset_area') }}</h1>

    <form action="{{ route('admin.assets-areas.store') }}" method="POST" class="mx-auto" style="max-width: 650px;">
        @csrf


        <div class="form-group mb-3">
            <label for="asset_name" class="font-weight-bold">{{ __('ui.asset_name') }}</label>
            <input type="text" name="asset_name" id="asset_name" class="form-control form-control-lg" required>
        </div>


        <div class="form-group mb-3">
            <label for="company_id" class="font-weight-bold">{{ __('ui.company') }}</label>
            <select name="company_id" id="company_id" class="form-control form-control-lg" required>
                <option value="">{{ __('ui.select_company') }}</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="branch_id" class="font-weight-bold">{{ __('ui.branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control form-control-lg" required>
                <option value="">{{ __('ui.select_branch') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mb-3">
            <label for="department_id" class="font-weight-bold">{{ __('ui.department') }}</label>
            <select name="department_id" id="department_id" class="form-control form-control-lg" required>
                <option value="">{{ __('ui.select_department') }}</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mb-3">
            <label for="workplace_id" class="font-weight-bold">{{ __('ui.workplace') }}</label>
            <select name="workplace_id" id="workplace_id" class="form-control form-control-lg" required>
                <option value="">{{ __('ui.select_workplace') }}</option>
                @foreach($workplaces as $workplace)
                    <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="device_type_id" class="font-weight-bold">{{ __('ui.device_type') }}</label>
            <select name="device_type_id" id="device_type_id" class="form-control form-control-lg" required>
                @foreach ($deviceTypes as $deviceType)
                    <option value="{{ $deviceType->id }}">{{ $deviceType->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mb-3">
            <label for="brand_id" class="font-weight-bold">{{ __('ui.brand') }}</label>
            <select name="brand_id" id="brand_id" class="form-control form-control-lg" required>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mb-3">
            <label for="serial_number" class="font-weight-bold">{{ __('ui.serial_number') }}</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control form-control-lg" required>
        </div>

        <div class="form-group mb-3">
            <label for="cpu" class="font-weight-bold">CPU</label>
            <input type="text" name="cpu" id="cpu" class="form-control form-control-lg">
        </div>


        <div class="form-group mb-3">
            <label for="ram" class="font-weight-bold">RAM</label>
            <input type="text" name="ram" id="ram" class="form-control form-control-lg">
        </div>


        <div class="form-group mb-3">
            <label for="storage" class="font-weight-bold">{{ __('ui.storage') }}</label>
            <input type="text" name="storage" id="storage" class="form-control form-control-lg">
        </div>


        <div class="form-group mb-3">
            <label for="gpu" class="font-weight-bold">GPU</label>
            <input type="text" name="gpu" id="gpu" class="form-control form-control-lg">
        </div>


        <button type="submit" class="btn btn-success btn-lg" style="width: auto; padding: 10px 20px; background-color: #10401F; color: white; font-size: 1.1rem; border-radius: 5px;">
            {{ __('ui.create_asset_area') }}
        </button>
    </form>
@endsection
