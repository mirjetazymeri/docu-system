@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
Form Wizard
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link href="{{ asset('vendors/select2/css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('vendors/select2/css/select2-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/iCheck/css/all.css') }}"  rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/pages/wizard.css') }}" rel="stylesheet">
@stop

{{-- Page content --}}
@section('content')

<section class="content-header">
    <!--section starts-->
    <h1>Form Wizards</h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <i class="livicon" data-name="home" data-size="14" data-loop="true"></i>
                {{ __('admin_dashboard.page_title') }}
            </a>
        </li>
        <li>
            <a href="#">Forms</a>
        </li>
        <li class="active">Form Wizards</li>
    </ol>
</section>
<!--section ends-->
<section class="content pr-3 pl-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header bg-success text-white">
                    <span>
                        <i class="livicon" data-name="bell" data-size="16" data-loop="true" data-c="#fff" data-hc="white"></i> Bootstrap Wizard
                    </span>
                    <span class="float-right ">
                        <i class="fa fa-chevron-up clickable"></i>
                    </span>
                </div>
                <div class="card-body">
                    <form id="commentForm" method="post" action="#">
                        <div id="rootwizard">
                            <ul class="nav nav-pills">
                                <li  class="nav-item">
                                    <a href="#tab1" data-toggle="tab" class="nav-link active color_accrd">{{ __('widgets.datatable.paginate.first') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#tab2" data-toggle="tab" class="nav-link color_accrd ml-2">Second</a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="nav-link color_accrd ml-2">Third</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="tab1">
                                    <div class="form-group">
                                        <label for="userName" class="control-label">User name *</label>
                                        <input id="userName" name="username" type="text" placeholder="Enter your name" class="form-control required">
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="control-label">{{ __('ui.email') }}</label>
                                        <input id="email" name="email" placeholder="Enter your Email" type="text" class="form-control required email">
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="control-label">{{ __('ui.password') }}</label>
                                        <input id="password" name="password" type="password" placeholder="{{ __('admin_login.password_placeholder') }}" class="form-control required">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm" class="control-label">{{ __('ui.confirm_password') }}</label>
                                        <input id="confirm" name="confirm" type="password" placeholder="Confirm your password " class="form-control required">
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab2">
                                    <div class="form-group">
                                        <label for="name" class="control-label">First name *</label>
                                        <input id="name" name="fname" placeholder="Enter your First name" type="text" class="form-control required">
                                    </div>
                                    <div class="form-group">
                                        <label for="surname" class="control-label">Last name *</label>
                                        <input id="surname" name="lname" type="text" placeholder=" Enter your Last name" class="form-control required">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">{{ __('ui.gender') }}</label>
                                        <select class="form-control" name="gender" id="gender"
                                                title="Select an account type...">
                                            <option disabled="" selected="">{{ __('ui.select') }}</option>
                                            <option>MALE</option>
                                            <option>FEMALE</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="control-label">{{ __('ui.address') }}</label>
                                        <input id="address" name="address" type="text" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="age" class="control-label">Age *</label>
                                        <input id="age" name="age" type="text" maxlength="3" class="form-control required number" min="17" data-bv-greaterthan-inclusive="false" data-bv-greaterthan-message="The input must be greater than or equal to 18" max="100" data-bv-lessthan-inclusive="true" data-bv-lessthan-message="The input must be less than 100">                                    </div>
                                </div>
                                <div class="tab-pane" id="tab3">
                                    <div class="form-group">
                                        <label for="phone1" class="control-label">Home number *</label>
                                        <input type="text" class="form-control" id="phone1" name="phone1" placeholder="(999)999-9999">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone2" class="control-label">Personal number *</label>
                                        <input type="text" class="form-control" id="phone2" name="phone2" placeholder="(999)999-9999">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone3" class="control-label">Alternate number *</label>
                                        <input type="text" class="form-control" id="phone3" name="phone3" placeholder="(999)999-9999">
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input id="acceptTerms" name="acceptTerms" type="checkbox" class="custom-checkbox"> *I agree with the Terms and Conditions.
                                        </label>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="previous">
                                        <a href="#">{{ __('ui.previous') }}</a>
                                    </li>
                                    <li class="next">
                                        <a href="#">{{ __('ui.next') }}</a>
                                    </li>
                                    <li class="next finish" style="display:none;">
                                        <a href="javascript:;">{{ __('ui.finish') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div id="myModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">User Register</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You Submitted Successfully.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- content -->
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="{{ asset('vendors/iCheck/js/icheck.js') }}"></script>
    <script src="{{ asset('vendors/moment/js/moment.min.js') }}" ></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/select2/js/select2.js') }}" type="text/javascript"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/bootstrapwizard/jquery.bootstrap.wizard.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/pages/form_wizard.js') }}"  type="text/javascript"></script>
@stop
