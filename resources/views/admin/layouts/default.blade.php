<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @section('title')
            | {{ $appName }}
        @show
    </title>

    <!-- global css -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ asset('lib/app.css') }}" rel="stylesheet" type="text/css" />
    <!-- end of global css -->

    <!--page level css-->
    @yield('header_styles')
    @include('admin.layouts._sidebar_styles')
    <!--end of page level css-->

<body class="skin-josh">
    <header class="header">
        <a href="{{ Sentinel::inRole('admin') ? route('admin.dashboard') : route('applications.index') }}" class="logo">
            <img src="{{ asset('frontend/assets/images/logo-prog.png') }}" alt="Documentation-system">
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <div>
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <div class="responsive_nav"></div>
                </a>
            </div>
            <div class="navbar-right toggle">
                <ul class="nav navbar-nav  list-inline">
                    {{-- @include('admin.layouts._messages') --}}
                    {{-- @include('admin.layouts._notifications') --}}
                    @include('admin.layouts._languages')

                    <!-- Language dropdown -->

                    <!-- End Language dropdown -->
                    {{-- <li class=" nav-item dropdown user user-menu">
                        <span id="timer" class="badge badge-danger p-3">1:00</span>
                    </li> --}}

                    <li class=" nav-item dropdown user user-menu">
                        <span id="timer" class="nav-link" data-timeout="{{ Sentinel::getUser()->inactivity_timeout }}"></span>
                    </li>

                    <li class=" nav-item dropdown user user-menu">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            @if (Sentinel::getUser()->pic)
                                <img src="{{ Sentinel::getUser()->pic }}" alt="img" height="35px" width="35px"
                                    class="rounded-circle img-fluid float-left" />
                            @elseif(Sentinel::getUser()->gender === 'male')
                                <img src="{{ asset('images/authors/avatar3.png') }}" alt="img" height="35px"
                                    width="35px" class="rounded-circle img-fluid float-left" />
                            @elseif(Sentinel::getUser()->gender === 'female')
                                <img src="{{ asset('images/authors/avatar5.png') }}" alt="img" height="35px"
                                    width="35px" class="rounded-circle img-fluid float-left" />
                            @else
                                <img src="{{ asset('images/authors/no_avatar.jpg') }}" alt="img" height="35px"
                                    width="35px" class="rounded-circle img-fluid float-left" />
                            @endif
                            <div class="riot">
                                <div>
                                    <p class="user_name_max">{{ Sentinel::getUser()->first_name }}
                                        {{ Sentinel::getUser()->last_name }}</p>
                                    <span>
                                        <i class="caret"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header bg-light-blue">
                                @if (Sentinel::getUser()->pic)
                                    <img src="{{ Sentinel::getUser()->pic }}" alt="img" height="35px"
                                        width="35px" class="rounded-circle img-fluid float-left" />
                                @elseif(Sentinel::getUser()->gender === 'male')
                                    <img src="{{ asset('images/authors/avatar3.png') }}" alt="img" height="35px"
                                        width="35px" class="rounded-circle img-fluid float-left" />
                                @elseif(Sentinel::getUser()->gender === 'female')
                                    <img src="{{ asset('images/authors/avatar5.png') }}" alt="img" height="35px"
                                        width="35px" class="rounded-circle img-fluid float-left" />
                                @else
                                    <img src="{{ asset('images/authors/no_avatar.jpg') }}" alt="img"
                                        height="35px" width="35px" class="rounded-circle img-fluid float-left" />
                                @endif
                                <p class="topprofiletext">{{ Sentinel::getUser()->first_name }}
                                    {{ Sentinel::getUser()->last_name }}</p>
                            </li>
                            <!-- Menu Body -->
                            <li>
                                <a href="{{ Sentinel::inRole('admin') ? URL::route('admin.users.show', Sentinel::getUser()->id) : route('my-account') }}">
                                    <i class="livicon" data-name="user" data-s="18"></i>
                                    {{ __('ui.my_profile') }}
                                </a>
                            </li>
                            <li role="presentation"></li>
                            <li>
                                <a href="{{ Sentinel::inRole('admin') ? route('admin.users.edit', Sentinel::getUser()->id) : route('my-account') }}">
                                    <i class="livicon" data-name="gears" data-s="18"></i>
                                    {{ __('ui.account_settings') }}
                                </a>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="float-left">
                                    <a href="{{ URL::route('lockscreen', Sentinel::getUser()->id) }}">
                                        <i class="livicon" data-name="lock" data-size="16" data-c="#555555"
                                            data-hc="#555555" data-loop="true"></i>
                                        {{ __('ui.lock') }}
                                    </a>
                                </div>
                                <div class="float-right">
                                    <a href="{{ URL::to('admin/logout') }}">
                                        <i class="livicon" data-name="sign-out" data-s="18"></i>
                                        {{ __('ui.logout') }}
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

        </nav>
    </header>
    <div class="wrapper ">

        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side ">
            <section class="sidebar ">
                <div class="page-sidebar  sidebar-nav">
                    @if(Sentinel::getUser()->hasAccess('users.view'))
                    <div class="nav_icons">
                        <ul class="sidebar_threeicons">
                            <li>
                                <a href="{{ URL::to('admin/advanced_tables') }}">
                                    <i class="livicon" data-name="table" title="{{ __('ui.advanced_tables') }}" data-loop="true"
                                        data-color="#FFFFFF" data-hc="#FFFFFF" data-s="25"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ URL::to('admin/tasks') }}">
                                    <i class="livicon" data-name="list-ul" title="{{ __('ui.tasks') }}" data-loop="true"
                                        data-color="#FFFFFF" data-hc="#FFFFFF" data-s="25"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ URL::to('admin/gallery') }}">
                                    <i class="livicon" data-name="image" title="{{ __('ui.gallery') }}" data-loop="true"
                                        data-color="#FFFFFF" data-hc="#FFFFFF" data-s="25"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ URL::to('admin/users') }}">
                                    <i class="livicon" data-name="user" title="{{ __('words.words.users') }}" data-loop="true"
                                        data-color="#FFFFFF" data-hc="FFFFFF" data-s="25"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                    <div class="clearfix"></div>
                    <!-- BEGIN SIDEBAR MENU -->
                    @include('admin.layouts._left_menu')
                    <!-- END SIDEBAR MENU -->
                </div>
            </section>
        </aside>

        <aside class="right-side">

            <!-- Notifications -->
            <div id="notific">
                @include('notifications')
            </div>

            <!-- Content -->
            @yield('content')

        </aside>
        <!-- right-side -->
    </div>
    <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button"
        title="{{ __('ui.return_to_top') }}" data-toggle="tooltip" data-placement="left">
        <i class="livicon" data-name="plane-up" data-size="18" data-loop="true" data-c="#fff"
            data-hc="white"></i>
    </a>
    <!-- global js -->


    <script src="{{ asset('js/admin.js') }}" type="text/javascript"></script>
    @include('admin.partials.widget-localization')
    <script src="{{ asset('lib/js/autologout.js') }}" type="text/javascript"></script>
    <!-- end of global js -->
    <!-- begin page level js -->
    @yield('footer_scripts')
    <!-- end page level js -->
</body>

</html>
