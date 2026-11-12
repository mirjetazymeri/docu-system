<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @section('title')
        | Welcome to Josh Frontend
        @show
    </title>

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/lib.css') }}">
    <link rel="stylesheet" href="{{ asset('css/frontend/custom1.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    @yield('header_styles')

    <style>
        /* Global Styling */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F7F7F7;
        }

        /* Header Styling */
        header {
            background: #757575;
            color: white;
        } 

        .icon-section {
            background-color: #747474; /* Green background */
            padding: 10px 0;
        }

        .icon-section a {
            color: white;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .icon-section a:hover {
            color: #FFC107;
        }

        .navbar {
            padding: 15px 0;
        }

        .nav-link {
            color: white;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #BDBDBD;
        }

        .dropdown-menu {
            background-color: white;
            border-radius: 5px;
        }

        .dropdown-item {
            color: #424242;
        }

        .dropdown-item:hover {
            background-color: #BDBDBD;
            color: white;
        }

        /* Footer Styling */
        footer {
            background-color: #616161;
            color: white;
            padding: 60px 0;
            margin-top: 40px;
        }

        footer h4 {
            font-weight: bold;
            margin-bottom: 15px;
        }

        footer a {
            color: #BDBDBD;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            background-color: #424242;
            text-align: center;
            padding: 10px 0;
        }

        .main-content {
            margin-bottom: 60px;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #BDBDBD;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            background-color: #9E9E9E;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header>
        <div class="icon-section text-center py-2">
            <a href="#"><i class="fab fa-facebook-f mx-2"></i></a>
            <a href="#"><i class="fab fa-twitter mx-2"></i></a>
            <a href="#"><i class="fab fa-google-plus-g mx-2"></i></a>
            <a href="#"><i class="fab fa-linkedin-in mx-2"></i></a>
            <a href="#"><i class="fas fa-rss mx-2"></i></a>
            <span class="ml-3">info@joshadmin.com | (703) 717-4200</span>
        </div>

        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <button class="navbar-toggler" type="button" data-toggle="collapse" 
                    data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" 
                    aria-label="{{ __('ui.toggle_navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item {!! (Request::is('/') ? 'active' : '') !!}">
                            <a class="nav-link" href="{{ route('home') }}">{{ __('general.home') }}</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="featuresDropdown" role="button"
                                data-toggle="dropdown" aria-expanded="false">
                                {{ __('ui.features') }}
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ URL::to('typography') }}">{{ __('ui.typography') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('advanced_features') }}">{{ __('ui.advanced_features') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('grid') }}">{{ __('ui.grid_system') }}</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button"
                                data-toggle="dropdown" aria-expanded="false">
                                {{ __('ui.pages') }}
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ URL::to('about_us') }}">{{ __('ui.about_us') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('timeline') }}">{{ __('ui.timeline') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('price') }}">{{ __('words.price') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('404') }}">{{ __('ui.404_error') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('500') }}">{{ __('ui.500_error') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('faq') }}">{{ __('ui.faq') }}</a>
                                <a class="dropdown-item" href="{{ URL::to('blank_page') }}">{{ __('ui.blank') }}</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('news') }}">{{ __('news/title.news') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('blog') }}">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('contact') }}">{{ __('ui.contact') }}</a>
                        </li>
                        @if(Sentinel::guest())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('login') }}">{{ __('ui.login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('register') }}">{{ __('ui.register') }}</a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('my-account') }}">{{ __('ui.my_account') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ URL::to('logout') }}">{{ __('ui.logout') }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <div class="container main-content">
        @yield('content')
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>{{ __('ui.about_us') }}</h4>
                    <p>{{ __('ui.we_provide_innovative_solutions_for_businesses_worldwide') }}</p>
                </div>
                <div class="col-md-4">
                    <h4>{{ __('ui.contact_us') }}</h4>
                    <p><i class="fas fa-phone-alt"></i> (703) 717-4200</p>
                    <p><i class="fas fa-envelope"></i> info@joshadmin.com</p>
                </div>
                <div class="col-md-4">
                    <h4>{{ __('ui.recent_posts') }}</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">{{ __('ui.post_one') }}</a></li>
                        <li><a href="#">{{ __('ui.post_two') }}</a></li>
                        <li><a href="#">{{ __('ui.post_three') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Josh Admin. {{ __('ui.rights_reserved') }}</p>
        </div>
    </footer>

    <a href="#" class="back-to-top" title="{{ __('ui.back_to_top') }}">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="{{ asset('js/frontend/lib.js') }}"></script>
    @include('admin.partials.widget-localization')
    @yield('footer_scripts')

    <script>
        $(".navbar-toggler").click(function () {
            $(this).closest('.navbar').find('.collapse').toggleClass('show');
        });

        $('.back-to-top').click(function (e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, '300');
        });
    </script>
</body>

</html>
