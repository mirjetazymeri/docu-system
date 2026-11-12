@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
{{ __('ui.edit_user') }}
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
<!--page level css -->
<link href="{{ asset('vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('vendors/select2/css/select2.min.css') }}" type="text/css" rel="stylesheet">
<link href="{{ asset('vendors/select2/css/select2-bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('vendors/datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendors/iCheck/css/all.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/pages/wizard.css') }}" rel="stylesheet">
@stop
<!--end of page level css-->


{{-- Page content --}}
@section('content')
<section class="content-header">
    <h1>{{ __('ui.edit_user') }}</h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                {{ __('admin_dashboard.page_title') }}
            </a>
        </li>
        <li><a href="#"> {{ __('words.words.users') }}</a></li>
        <li class="active">{{ __('ui.edit_user') }}</li>
    </ol>
</section>
<section class="content pr-3 pl-3">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12 my-3">
            <div class="card ">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="livicon" data-name="user-add" data-size="18" data-c="#fff" data-hc="#fff"
                            data-loop="true"></i>
                        {{ __('ui.editing_user') }} <p class="user_name_max">{!! $user->first_name!!} {!! $user->last_name!!}</p>
                    </h3>
                    <span class="float-right clickable">
                        <i class="fa fa-chevron-up"></i>
                    </span>
                </div>
                <div class="card-body">
                    <!--main content-->
                    {!! Form::model($user, ['url' => URL::to('admin/users/'. $user->id.''), 'method' => 'put', 'class'
                    => 'form-horizontal','id'=>'commentForm', 'enctype'=>'multipart/form-data','files'=> true]) !!}
                    {{ csrf_field() }}
                    <!-- CSRF Token -->


                    <div id="rootwizard">
                        <ul>
                            <li class="nav-item"><a href="#tab1" data-toggle="tab" class="nav-link">{{ __('ui.user_profile') }}</a>
                            </li>
                            <li class="nav-item"><a href="#tab2" data-toggle="tab" class="nav-link ml-2">{{ __('ui.bio') }}</a></li>
                            <li class="nav-item"><a href="#tab3" data-toggle="tab" class="nav-link ml-2">{{ __('ui.address') }}</a>
                            </li>
                            <li class="nav-item"><a href="#tab4" data-toggle="tab" class="nav-link ml-2">{{ __('ui.user_role') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane " id="tab1">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group {{ $errors->first('first_name', 'has-error') }}">
                                    <div class="row">
                                        <label for="first_name" class="col-sm-2 control-label">{{ __('ui.first_name') }}</label>
                                        <div class="col-sm-10">
                                            <input id="first_name" name="first_name" type="text"
                                                placeholder="{{ __('ui.first_name') }}" class="form-control required"
                                                value="{!! old('first_name', $user->first_name) !!}" />

                                            {!! $errors->first('first_name', '<span class="help-block">:message</span>')
                                            !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('last_name', 'has-error') }}">
                                    <div class="row"> <label for="last_name" class="col-sm-2 control-label">{{ __('ui.last_name') }}</label>
                                        <div class="col-sm-10">
                                            <input id="last_name" name="last_name" type="text" placeholder="{{ __('ui.last_name') }}"
                                                class="form-control required"
                                                value="{!! old('last_name', $user->last_name) !!}" />

                                            {!! $errors->first('last_name', '<span class="help-block">:message</span>')
                                            !!}
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group {{ $errors->first('email', 'has-error') }}">
                                    <div class="row">
                                        <label for="email" class="col-sm-2 control-label">{{ __('ui.email') }}</label>
                                        <div class="col-sm-10">
                                            <input id="email" name="email" placeholder="{{ __('ui.e_mail') }}" type="text"
                                                class="form-control required email"
                                                value="{!! old('email', $user->email) !!}" />
                                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('password', 'has-error') }}">

                                    <p class="text-warning">{{ __('ui.if_you_don_t_want_to_change_password_please_leave_them_empty') }}</p>
                                    <div class="row">
                                        <label for="password" class="col-sm-2 control-label">{{ __('ui.password') }}</label>
                                        <div class="col-sm-10">
                                            <input id="password" name="password" type="password" placeholder="{{ __('ui.password') }}"
                                                class="form-control required" value="{!! old('password') !!}" />
                                            {!! $errors->first('password', '<span class="help-block">:message</span>')
                                            !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('password_confirm', 'has-error') }}">
                                    <div class="row">
                                        <label for="password_confirm" class="col-sm-2 control-label">{{ __('ui.confirm_password') }}</label>
                                        <div class="col-sm-10">
                                            <input id="password_confirm" name="password_confirm" type="password"
                                                placeholder="{{ __('ui.confirm_password') }}" class="form-control required" />
                                            {!! $errors->first('password_confirm', '<span
                                                class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab2" disabled="disabled">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group  {{ $errors->first('dob', 'has-error') }}">
                                    <div class="row">
                                        <label for="dob" class="col-sm-2 control-label">{{ __('ui.date_of_birth') }}</label>
                                        <div class="col-sm-10">
                                            <input id="dob" name="dob" type="text" class="form-control"
                                                data-date-format="YYYY-MM-DD" value="{!! old('dob', $user->dob) !!}"
                                                placeholder="yyyy-mm-dd" />
                                        </div>
                                        <span class="help-block">{{ $errors->first('dob', ':message') }}</span>
                                    </div>
                                </div>


                                <div class="form-group {{ $errors->first('pic_file', 'has-error') }}">
                                    <div class="row">

                                        <label class="col-sm-2 control-label">{{ __('ui.profile_picture') }}</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail"
                                                    style="width: 200px; height: 200px;">
                                                    @if($user->pic)
                                                    <img src="{{ $user->pic }}" alt="img" class="img-responsive" />
                                                    @elseif($user->gender === "male")
                                                    <img src="{{ asset('images/authors/avatar3.png') }}" alt="..."
                                                        class="img-responsive" />
                                                    @elseif($user->gender === "female")
                                                    <img src="{{ asset('images/authors/avatar5.png') }}" alt="..."
                                                        class="img-responsive" />
                                                    @else
                                                    <img src="{{ asset('images/authors/no_avatar.jpg') }}" alt="..."
                                                        class="img-responsive" />
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-primary btn-file">
                                                        <span class="fileinput-new">{{ __('ui.select_image') }}</span>
                                                        <span class="fileinput-exists">{{ __('ui.change') }}</span>
                                                        <input id="pic" name="pic_file" type="file"
                                                            class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-primary fileinput-exists"
                                                        data-dismiss="fileinput"
                                                        style="color: black !important;">{{ __('ui.remove') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="help-block">{{ $errors->first('pic_file', ':message') }}</span>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="row">
                                        <label for="bio" class="col-sm-2 control-label">{{ __('ui.bio') }} <small>{{ __('ui.brief_intro') }}</small></label>
                                        <div class="col-sm-10">
                                            <textarea name="bio" id="bio" class="form-control resize_vertical"
                                                rows="4">{!! old('bio', $user->bio) !!}</textarea>
                                        </div>
                                        {!! $errors->first('bio', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab3" disabled="disabled">
                                <div class="form-group {{ $errors->first('gender', 'has-error') }}">
                                    <div class="row">
                                        <label for="email" class="col-sm-2 control-label">{{ __('ui.gender') }}</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="{{ __('ui.select_gender') }}" name="gender">
                                                <option value="">{{ __('ui.select') }}</option>
                                                <option value="male" @if($user->gender === 'male') selected="selected"
                                                    @endif >{{ __('ui.male') }}</option>
                                                <option value="female" @if($user->gender === 'female')
                                                    selected="selected" @endif >{{ __('ui.female') }}</option>
                                                <option value="other" @if($user->gender === 'other') selected="selected"
                                                    @endif >{{ __('ui.other') }}</option>

                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('gender', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('country', 'has-error') }}">
                                    <div class="row">
                                        <label for="country" class="col-sm-2 control-label">{{ __('ui.country') }}</label>
                                        <div class="col-sm-10">
                                            {!! Form::select('country',
                                            $countries,old('country',$user->country),['class' => 'country_field
                                            form-control']) !!}
                                        </div>
                                        <span class="help-block">{{ $errors->first('country', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="state" class="col-sm-2 control-label">{{ __('ui.state') }}</label>
                                        <div class="col-sm-10">
                                            <input id="state" name="user_state" type="text" class="form-control"
                                                value="{!! old('user_state', $user->user_state) !!}" />
                                        </div>
                                        <span class="help-block">{{ $errors->first('state', ':message') }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="city" class="col-sm-2 control-label">{{ __('ui.city') }}</label>
                                        <div class="col-sm-10">
                                            <input id="city" name="city" type="text" class="form-control"
                                                value="{!! old('city', $user->city) !!}" />
                                        </div>
                                        <span class="help-block">{{ $errors->first('city', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="address" class="col-sm-2 control-label">{{ __('ui.address') }}</label>
                                        <div class="col-sm-10">
                                            <input id="address" name="address" type="text" class="form-control"
                                                value="{!! old('address', $user->address) !!}" />
                                        </div>
                                        <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="postal" class="col-sm-2 control-label">{{ __('ui.postal_zip') }}</label>
                                        <div class="col-sm-10">
                                            <input id="postal" name="postal" type="text" class="form-control"
                                                value="{!! old('postal', $user->postal) !!}" />
                                        </div>
                                        <span class="help-block">{{ $errors->first('postal', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab4" disabled="disabled">
                                <p class="text-danger"><strong>{{ __('ui.be_careful_with_role_selection_if_you_give_admin_access_they_can_access_admin_section') }}</strong></p>

                                <div class="form-group required">
                                    <div class="row">
                                        <label for="role" class="col-sm-2 control-label">{{ __('ui.role') }}</label>
                                        <div class="col-sm-10">
                                            <select class="form-control required" title="{{ __('ui.select_role') }}"
                                                name="roles[]" id="roles">
                                                <option value="">{{ __('ui.select') }}</option>
                                                @foreach($roles as $role)
                                                <option value="{!! $role->id !!}"
                                                    {{ (array_key_exists($role->id, $userRoles) ? ' selected="selected"' : '') }}
                                                    @if($user->id==1&&$role->id>=2) disabled @endif @if($user->id==2 &&
                                                    $role->id!=2) disabled @endif>{{ __($role->name) }}</option>
                                                @endforeach
                                            </select>
                                            {!! $errors->first('role', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('role', ':message') }}</span>
                                </div>
                                <!-- Access Levels -->
                                <div class="form-group">
                                    <label for="access_level">{{ __('ui.access_levels') }}</label>
                                    <select id="access_level" name="access_level_ids[]" class="form-control" multiple>
                                        @foreach($accessLevels as $accessLevel)
                                            <option value="{{ $accessLevel->id }}" {{ in_array($accessLevel->id, $userAccessLevels) ? 'selected' : '' }}>
                                                {{ __($accessLevel->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('access_level_ids'))
                                        <span class="text-danger">{{ $errors->first('access_level_ids') }}</span>
                                    @endif
                                    <small>{{ __('ui.select_one_or_more_access_levels_for_the_user') }}</small>
                                </div>


                                <div class="form-group">
                                    <div class="row">
                                        <label for="activate" class="col-sm-2 control-label"> {{ __('ui.activate_user') }}</label>
                                        <div class="col-sm-10">
                                            <input id="activate" name="activate" type="checkbox"
                                                class="pos-rel p-l-30 custom-checkbox" value="1" @if($status)
                                                checked="checked" @endif>
                                            <span>{{ __('ui.to_activate_user_account_automatically_click_the_check_box') }}</span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <ul class="pager wizard">
                                <li class="previous"><a href="#">{{ __('ui.previous') }}</a></li>
                                <li class="next"><a href="#">{{ __('ui.next') }}</a></li>
                                <li class="next finish" style="display:none;"><a href="javascript:;">{{ __('ui.finish') }}</a></li>
                            </ul>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <!--row end-->
</section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
<script src="{{ asset('vendors/iCheck/js/icheck.js') }}"></script>
<script src="{{ asset('vendors/moment/js/moment.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<script src="{{ asset('vendors/jasny-bootstrap/js/jasny-bootstrap.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendors/select2/js/select2.js') }}" type="text/javascript"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<script src="{{ asset('vendors/bootstrapwizard/jquery.bootstrap.wizard.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendors/datetimepicker/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/edituser.js?v=locale-1') }}"></script>
<script>
    function formatState (state) {
            if (!state.id) { return state.text; }
            var $state = $(
                '<span><img src="{{asset('img/countries_flags')}}/'+ state.element.value.toLowerCase() + '.png" class="img-flag" width="20px" height="20px" /> ' + state.text + '</span>'
            );
            return $state;

        }
        $(".country_field").select2({
            templateResult: formatState,
            templateSelection: formatState,
            placeholder: @js(__('ui.select_a_country')),
            theme:"bootstrap"
        });

</script>
@stop
