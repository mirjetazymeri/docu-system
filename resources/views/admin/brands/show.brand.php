@extends('admin.layouts.default')
@section('content')
    <h1>{{ __('ui.brand_details') }}</h1>

    <p>{{ __('ui.name') }}: {{ $brand->name }}</p>

    <a href="{{ route('admin.brands.index') }}" class="btn btn-primary">{{ __('ui.back_to_list') }}</a>
@endsection
