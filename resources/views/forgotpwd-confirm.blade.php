<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    {{--<meta charset="utf-8">--}}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.forgot_password') }}</title>
    <!--global css starts-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
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
            <h3 class="text-primary">{{ __('ui.reset_your_password') }}</h3>
            <p>{{ __('ui.enter_your_new_password_details') }}</p>
            <div id="notific">
            @include('notifications')
            </div>
            <form action="{{ route('forgot-password-confirm',compact(['userId','passwordResetCode'])) }}" class="omb_loginForm pwd_validation"  autocomplete="off" method="POST">
                {!! Form::token() !!}
                <label class="sr-only"> {{ __('ui.new_password') }}</label>
                <input type="password" class="form-control" name="password" placeholder="{{ __('ui.new_password') }}">
                <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                <label class="sr-only">{{ __('ui.confirm_password') }}</label>
                <input type="password" class="form-control mt-15" name="password_confirm" placeholder="{{ __('ui.confirm_new_password') }}">
                <span class="help-block">{{ $errors->first('password_confirm', ':message') }}</span>
                <input type="submit" class="btn btn-block btn-primary" value="Submit to Reset Password" style="margin-top:10px;">
            </form>
        </div>
    </div>
</div>
<!--global js starts-->
<script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/frontend/bootstrap.min.js') }}"></script>
<!--global js end-->
@include('admin.partials.widget-localization')
</body>
</html>
