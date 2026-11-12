@extends('admin.layouts.default')
@section('content')
    <h1>{{ __('ui.edit_asset_area') }}</h1>
    <form action="{{ route('admin.assets-areas.update', $asset->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="asset_name">{{ __('ui.asset_name') }}</label>
            <input type="text" name="asset_name" id="asset_name" class="form-control" value="{{ $asset->asset_name }}" required>
        </div>
        <div class="form-group">
            <label for="device_type_id">{{ __('ui.device_type') }}</label>
            <select name="device_type_id" id="device_type_id" class="form-control" required>
                @foreach ($deviceTypes as $deviceType)
                    <option value="{{ $deviceType->id }}" {{ $deviceType->id == $asset->device_type_id ? 'selected' : '' }}>{{ $deviceType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="brand_id">{{ __('ui.brand') }}</label>
            <select name="brand_id" id="brand_id" class="form-control" required>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" {{ $brand->id == $asset->brand_id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="serial_number">{{ __('ui.serial_number') }}</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ $asset->serial_number }}" required>
        </div>
        <div class="form-group">
            <label for="cpu">CPU</label>
            <input type="text" name="cpu" id="cpu" class="form-control" value="{{ $asset->cpu }}" required>
        </div>
        <div class="form-group">
            <label for="ram">RAM</label>
            <input type="text" name="ram" id="ram" class="form-control" value="{{ $asset->ram }}" required>
        </div>
        <div class="form-group">
            <label for="storage">{{ __('ui.storage') }}</label>
            <input type="text" name="storage" id="storage" class="form-control" value="{{ $asset->storage }}" required>
        </div>
        <div class="form-group">
            <label for="gpu">GPU</label>
            <input type="text" name="gpu" id="gpu" class="form-control" value="{{ $asset->gpu }}" required>
        </div>
        <button type="submit" class="btn btn-success">{{ __('button.save') }}</button>
    </form>
@endsection
