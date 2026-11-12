<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    {{--<meta charset="utf-8">--}}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.forgot_password') }}</title>
    <!--global css starts-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}">
    <link href="{{ asset('vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" rel="stylesheet" />
    <!--end of global css-->
    <!--page level css starts-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/frontend/forgot.css') }}">
    <!--end of page level css-->
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="box animation flipInX">
                <img src="{{ asset('images/josh-new.png') }}" alt="logo" class="img-responsive mar">
                <h3 class="text-primary">{{ __('ui.forgot_password') }}</h3>
                <p>{{ __('ui.enter_your_email_to_reset_your_password') }}</p>
                <div id="notific">
                    @include('notifications')
                </div>
                <form action="{{ route('forgot-password') }}" class="omb_loginForm" autocomplete="off" method="POST">
                    {!! Form::token() !!}
                    <div class="form-group">
                        <label class="sr-only"></label>
                        <input type="email" class="form-control email" name="email" placeholder="{{ __('ui.email') }}"
                            value="{!! old('email') !!}">
                        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                    </div>
                    <div class="form-group">
                        <input class="form-control btn btn-primary btn-block" type="submit" value="Reset Your Password">
                    </div>
                </form>

                {{ __('ui.back_to_login_page') }}<a href="{{ route('login') }}"> {{ __('ui.click_here') }}</a>
            </div>
        </div>
    </div>
    <!--global js starts-->
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrapvalidator/js/bootstrapValidator.min.js') }}" type="text/javascript">
    </script>
    <script type="text/javascript" src="{{ asset('js/frontend/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/frontend/forgotpwd_custom.js') }}"></script>
    <!--global js end-->
@include('admin.partials.widget-localization')
</body>

</html>