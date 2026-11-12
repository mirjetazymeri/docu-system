<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ __('ui.page_not_found') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- global level css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/lib.css') }}">
    <!-- end of globallevel css-->
    <!-- page level styles-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/frontend/404.css') }}" />

    <style>
        .btn-primary {
            color: #fff;
            background-color: #337ab7 !important;
            border-color: #2e6da4 !important;
            font-size:14px !important;
        }
    </style>
    <!-- end of page level styles-->
</head>

<body>

    <div id="animate" class="row">
        <div class="number">4</div>
        <div class="icon"> <i class="livicon" data-name="pacman" data-size="105" data-c="#f6c500" data-hc="#f1b21d" data-eventtype="click" data-iteration="15"></i>
        </div>
        <div class="number">4</div>
    </div>
    <div class="hgroup">
        <h1>{{ __('ui.page_not_found_hihi') }}</h1>
        <h2>{{ __('ui.it_seems_that_page_you_are_looking_for_no_longer_exists') }}</h2>
        <a href="{{ route('home') }}" class="btn btn-primary text-white">
            {{ __('general.home') }}
        </a>
    </div>
    <!-- global js -->
    <script src="{{ asset('js/frontend/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/frontend/bootstrap.min.js') }}" type="text/javascript"></script>
    <!--livicons-->
    <script src="{{ asset('js/raphael.min.js') }}"></script>
    <script src="{{ asset('js/livicons-1.4.min.js') }}"></script>
    <!-- end of global js -->
    <!-- begining of page level js-->
    <script src="{{ asset('js/frontend/404.js') }}"></script>
    <!-- end of page level js-->
</body>
</html>
