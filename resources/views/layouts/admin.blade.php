<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.admin_layout') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
      
        <div class="content">
            @yield('content') <!-- This will render content from child views -->
        </div>
    </div>
@include('admin.partials.widget-localization')
</body>
</html>
