<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <title>{{ __('ui.register') }} | {{ $appName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- global level css -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <!-- end of global level css -->
    <!-- page level css -->
    <link href="{{ asset('css/pages/login2.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendors/iCheck/css/minimal/blue.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" rel="stylesheet"/>
    <!-- styles of the page ends-->
</head>

<body>
<div class="container">
    <div class="row vertical-offset-50">
        <div class=" col-10 col-offset-1 col-sm-6 col-sm-offset-3  col-md-5 col-md-offset-4 col-lg-4 col-lg-offset-4 mx-auto">
            <div class="card ">
                <div class="card-header bg-default text-center border-0 py-2">
                    <h3>{{ __('ui.register') }}</h3>
                </div>
                <div class="card-body">
                    <!-- display all errors here -->
                    <form accept-charset="UTF-8" id="register_here" role="form" action="{{ route('register2') }}" method="post">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                        <fieldset>
                            <div class="form-group {{ $errors->first('first_name', 'has-error') }}">
                                <div class=" input-group ">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="livicon" data-name="user" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.first_name') }}" name="first_name" type="text"
                                           value="{!! old('first_name') !!}"/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('first_name', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="form-group  {{ $errors->first('last_name', 'has-error') }}">
                                <div class="input-group">
                                    <div class="input-group-append">
                                       <span class="input-group-text"><i class="livicon" data-name="user" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.last_name') }}" name="last_name" type="text"
                                           value="{!! old('last_name') !!}"/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('last_name', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="form-group  {{ $errors->first('email', 'has-error') }}">
                                <div class="input-group ">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="livicon" data-name="mail" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.e_mail') }}" name="email" type="email"
                                           value="{!! old('email') !!}"/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="form-group  {{ $errors->first('email_confirm', 'has-error') }}">
                                <div class="input-group ">
                                    <div class="input-group-append">
                                         <span class="input-group-text"><i class="livicon" data-name="mail" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.confirm_e_mail') }}" name="email_confirm"
                                           type="email" value="{!! old('email_confirm') !!}"/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('email_confirm', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="form-group  {{ $errors->first('password', 'has-error') }}">
                                <div class="input-group ">
                                    <div class="input-group-append">
                                        <span class="input-group-text"> <i class="livicon" data-name="key" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.password') }}" name="password" type="password"
                                           value=""/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('password', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="form-group  {{ $errors->first('password_confirm', 'has-error') }}">
                                <div class="input-group ">
                                    <div class="input-group-append">
                                         <span class="input-group-text"><i class="livicon" data-name="key" data-size="18" data-c="#000" data-hc="#000" data-loop="true"></i></span>
                                    </div>
                                    <input class="form-control" placeholder="{{ __('ui.confirm_password') }}" name="password_confirm"
                                           type="password" value=""/>
                                </div>
                                <div class="help-block">
                                {!! $errors->first('password_confirm', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input id="activate" name="activate" type="checkbox" value="1"
                                           class="minimal-blue" {!! old('terms')?"checked='checked'":"" !!}>
                                    <span style="font-size: 14px;">{{ __('ui.i_agree_for_terms_conditions') }}</span>
                                </label>
                                <br>
                            </div>
                            <input type="submit" value="Register" class="btn btn-primary btn-block btn-lg" />
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- global js -->
<script src="{{ asset('js/admin.js') }}" type="text/javascript"></script>
<!-- end of global js -->
<!-- begining of page level js-->
<script src="{{ asset('js/TweenLite.min.js') }}"></script>
<script src="{{ asset('vendors/iCheck/js/icheck.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendors/bootstrapvalidator/js/bootstrapValidator.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/register2.js') }}" type="text/javascript"></script>
<!-- end of page level js-->
</body>
</html>
