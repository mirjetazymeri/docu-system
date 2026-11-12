@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
{{ $appName }}
@parent
@stop

{{-- page level styles --}}
@section('header_styles')


<link rel="stylesheet" href="{{ asset('vendors/animate/animate.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/pages/only_dashboard.css') }}" />
<link rel="stylesheet" href="{{ asset('vendors/morrisjs/morris.css') }}">
<link rel="stylesheet" href="{{ asset('css/pages/dashboard2.css') }}" />
<meta name="_token" content="{{ csrf_token() }}">
@stop

{{-- Page content --}}
@section('content')
<section class="content-header">
    <h1>{{ __('ui.nav_welcome_to_dashboard') }} <span class="d-none d-md-inline-block header_info">( Dynamic Dashboard )</span></h1>
    <ol class="breadcrumb">
        <li class=" breadcrumb-item active">
            <a href="#">
                <i class="livicon" data-name="home" data-size="16" data-color="#333" data-hovercolor="#333"></i>
                {{ __('admin_dashboard.page_title') }}
            </a>
        </li>
    </ol>
</section>

<!--</section>-->
<section class="content pl-3 pr-3">
    @if ($analytics_error != 0)
    <div class="alert alert-danger alert-dismissable margin5">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>{{ __('ui.error') }}</strong> {{ __('ui.nav_you_need_to_add_google_analytics_file_for_full_working_of_the_page') }}
    </div>
    @endif
    <div class="row">
        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-6 margin_10 animated fadeInLeftBig">
            <!-- Trans label pie charts strats here-->
            <div class="widget-1">
                <div class="card-body squarebox square_boxs">
                    <div class="col-12 float-left nopadmar">
                        <div class="row">
                            <div class="square_box col-6 text-right">
                                <span>{{ __('ui.nav_visitors') }}</span>

                                <div class="number" id="myTargetElement3"></div>
                            </div>
                            <div class="col-6">
                                <span class="widget_circle3 float-right">
                                    <i class="livicon livicon-evo-holder " data-name="eye-open" data-l="true"
                                        data-c="#01BC8C" data-hc="#01BC8C" data-s="40"></i>
                                </span>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3  col-md-6 col-sm-6 margin_10 animated fadeInLeftBig">
            <!-- Trans label pie charts strats here-->
            <div class="widget-1">
                <div class="card-body squarebox square_boxs">
                    <div class="col-12 float-left nopadmar">
                        <div class="row">
                            <div class="square_box col-6 text-right">
                                <span>{{ __('words.words.users') }}</span>

                                <div class="number" id="myTargetElement4"></div>
                            </div>
                            <div class="col-6">
                                <span class="widget_circle4 float-right">
                                    <i class="livicon livicon-evo-holder " data-name="user" data-l="true"
                                        data-c="#F89A14" data-hc="#F89A14" data-s="40"></i>
                                </span>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-6 margin_10 animated fadeInRightBig">
            <!-- Trans label pie charts strats here-->
            <div class="widget-1">
                <div class="card-body squarebox square_boxs">
                    <div class="col-12 float-left nopadmar">
                        <div class="row">
                            <div class="square_box col-6 text-right">
                                <span>{{ __('ui.nav_page_views') }}</span>

                                <div class="number" id="myTargetElement1"></div>
                            </div>
                            <div class="col-6">
                                <span class="widget_circle1 float-right">
                                    <i class="livicon livicon-evo-holder " data-name="flag" data-l="true"
                                        data-c="#10332C
" data-hc="#10332C
" data-s="40"></i>
                                </span>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-6 margin_10 animated fadeInRightBig">
            <!-- Trans label pie charts strats here-->
            <div class="widget-1">
                <div class="card-body squarebox square_boxs">
                    <div class="col-12 float-left nopadmar">
                        <div class="row">
                            <div class="square_box col-6 text-right">
                                <span>{{ __('ui.nav_articles') }}</span>

                                <div class="number" id="myTargetElement2"></div>
                            </div>
                            <div class="col-6">
                                <span class="widget_circle2 float-right">
                                    <i class="livicon livicon-evo-holder " data-name="pen" data-l="true"
                                        data-c="#10332C" data-hc="#10332C" data-s="40"></i>
                                </span>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--/row-->
    <div class="row ">
        <div class="col-md-12 col-sm-12 col-lg-8 col-12 no_padding">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-12">
                    <div class="card card-border main_chart">
                        <div class="card-header bg-white">
                            <span>
                                <i class="livicon" data-name="barchart" data-size="16" data-loop="true" data-c="#EF6F6C"
                                    data-hc="#EF6F6C"></i> {{ __('ui.nav_users_stats') }}
                            </span>
                        </div>
                        <div class="card-body">
                            {!! $users_chart->container() !!}

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-12 my-3">
                    <div class="card card-border roles_chart">

                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="livicon" data-name="users" data-size="16" data-loop="true" data-c="#F89A14"
                                    data-hc="#F89A14"></i>
                                {{ __('ui.nav_user_roles') }}
                            </h4>

                        </div>
                        <div class="card-body nopadmar">
                            {!! $user_roles->container() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-sm-6 col-12 my-3 ">
                    <div class="card card-border">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="livicon" data-name="barchart" data-size="16" data-loop="true" data-c="#67C5DF"
                                    data-hc="#67C5DF"></i>
                                {{ __('ui.nav_yearly_visitors') }}
                            </h4>

                        </div>
                        <div class="card-body nopadmar">
                            <div id="bar_chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-12 col-12">
                    <div class="card card-border map">

                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="livicon" data-name="map" data-size="16" data-loop="true" data-c="#515763"
                                    data-hc="#515763"></i>
                                {{ __('ui.nav_user_types') }}
                            </h3>

                        </div>
                        <div class="card-body nopadmar">
                            <div id="donut_chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-12 my-lg-0 my-3 my-md-3 my-sm-0">
            <div class="card card-border">
                <div class="card-header border-light bg-white">
                    <span>
                        <i class="livicon" data-name="users" data-size="18" data-color="#21887F" data-hc="#21887F"
                            data-l="true"></i>
                        {{ __('ui.nav_recent_users') }}
                    </span>
                </div>
                <div class="card-body nopadmar users">
                    @foreach($users as $user )
                    <div class="media">
                        <div>
                            @if($user->pic)
                            <img src="{{ $user->pic }}" class="media-object rounded-circle">
                            @else
                            <img src="{{ asset('images/authors/no_avatar.jpg') }}" class="media-object rounded-circle">
                            @endif
                        </div>
                        <div class="media-body ml-3">
                            <h5 class="media-heading">{{ $user->full_name }}</h5>
                            <p>{{ $user->email }} <span
                                    class="user_create_date float-right">{{ $user->created_at->format('d M') }} </span>
                            </p>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
            <div class="card card-border my-3">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="livicon" data-name="eye-open" data-size="16" data-loop="true" data-c="#EF6F6C"
                            data-hc="#EF6F6C"></i>
                        {{ __('ui.nav_this_week_visitors') }}
                    </h4>

                </div>
                <div class="card-body nopadmar">
                    <div id="visitors_chart"></div>
                </div>
            </div>
            <div class="card card-border">
                <div class="card-header border-light">
                    <h3 class="card-title">
                        <i class="livicon" data-name="pen" data-size="16" data-color="#21887F" data-hc="#21887F"
                            data-l="true"></i>
                        {{ __('ui.nav_recent_blogs') }}
                    </h3>
                </div>
                <div class="card-body nopadmar blogs">
                    @foreach($blogs as $blog )
                    <div class="media">
                        <div>
                            @if($blog->author->pic)
                            <img src="{{  $blog->author->pic }}" class="media-object rounded-circle">
                            @else
                            <img src="{{ asset('images/authors/no_avatar.jpg') }}" class="media-object rounded-circle">
                            @endif
                        </div>

                        <div class="media-body ml-3">
                            <h5 class="media-heading">{{ $blog->title }}</h5>
                            <p>{{ __('ui.nav_category') }} {{ $blog->category->title }} <span class="user_create_date float-right">{{ __('ui.nav_by') }}
                                    {{ $blog->author->full_name }} </span></p>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="editConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('ui.close') }}"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ __('ui.nav_alert') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ __('ui.nav_you_are_already_editing_a_row_you_must_save_or_cancel_that_row_before_edit_delete_a_new_row') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('ui.close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
<script type="text/javascript" src="{{ asset('vendors/moment/js/moment.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<!--for calendar-->
<script src="{{ asset('vendors/moment/js/moment.min.js') }}" type="text/javascript"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<!-- Back to Top-->
<script type="text/javascript" src="{{ asset('vendors/countUp.js/js/countUp.js') }}"></script>
<script src="http://maps.google.com/maps/api/js?key=AIzaSyADWjiTRjsycXf3Lo0ahdc7dDxcQb475qw&libraries=places"></script>
<script src="{{ asset('vendors/morrisjs/morris.min.js') }}"></script>
<script src="{{ asset('vendors/highcharts/highcharts.js') }}" charset="utf-8"></script>


<script>
    var useOnComplete = false,
        useEasing = false,
        useGrouping = false,
        options = {
            useEasing: useEasing, // toggle easing
            useGrouping: useGrouping, // 1,000,000 vs 1000000
            separator: ',', // character to use as a separator
            decimal: '.' // character to use as a decimal
        };
    <!-- prettier-ignore -->
    var demo = new CountUp("myTargetElement1", 12.52, {!! $pageVisits!!}, 0, 6, options);
    demo.start();
    <!-- prettier-ignore -->
    var demo = new CountUp("myTargetElement2", 1, {!! $blog_count!!}, 0, 6, options);
    demo.start();
    <!-- prettier-ignore -->
    var demo = new CountUp("myTargetElement3", 24.02, {{ $visitors }}, 0, 6, options);
    demo.start();
    <!-- prettier-ignore -->
    var demo = new CountUp("myTargetElement4", 125, {{ $user_count }}, 0, 6, options);
    demo.start();

    $('.blogs').slimScroll({
        color: '#A9B6BC',
        height: 350 + 'px',
        size: '5px'
    });

    <!-- prettier-ignore -->
    var week_data = {!! $month_visits !!};
    var year_data = {!! $year_visits !!};
    var user_types = {!! $userTypes !!}

    function lineChart() {
        if(week_data) {
            Morris.Line({
                element: 'visitors_chart',
                data: week_data,
                lineColors: ['#10332C', '#21887F', '#EF6F6C'],
                xkey: 'date',
                ykeys: ['pageViews', 'visitors'],
                labels: ['pageViews', 'visitors'],
                pointSize: 0,
                lineWidth: 2,
                resize: true,
                fillOpacity: 1,
                behaveLikeLine: true,
                gridLineColor: '#e0e0e0',
                hideHover: 'auto'

            });
        }
    }

    function barChart() {
        Morris.Bar({
            element: 'bar_chart',
            data: year_data.length ? year_data : [{
                label: "No Data",
                value: 100
            }],
            barColors: ['#10332C', '#21887F'],
            xkey: 'date',
            ykeys: ['pageViews', 'visitors'],
            labels: ['pageViews', 'visitors'],
            pointSize: 0,
            lineWidth: 2,
            resize: true,
            fillOpacity: 0.4,
            behaveLikeLine: true,
            gridLineColor: '#e0e0e0',
            hideHover: 'auto'

        });
    }
    function donutChart() {
        Morris.Donut({
            element: 'donut_chart',
            data: user_types,
            colors: ['#10332C', '#21887F'],
            resize: true,
        });
    }
    lineChart();
    barChart();
    donutChart();
    $(".sidebar-toggle").on("click", function() {
        setTimeout(function() {
            $('#visitors_chart').empty();
            $('#bar_chart').empty();
            lineChart();
            barChart();
        }, 10);
    });
</script>
{!! $users_chart->script() !!}
{!! $user_roles->script() !!}
@stop
