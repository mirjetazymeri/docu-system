@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    {{ __('ui.gallery') }}
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')

    <!-- Add fancyBox main CSS files -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/fancybox/css/jquery.fancybox.css') }}"
          media="screen"/>
    <!-- Add Button helper (this is optional) -->
    <link rel="stylesheet" type="text/css"
          href="{{ asset('vendors/fancybox/helpers/css/jquery.fancybox-buttons.css') }}"/>
    <!-- Add Thumbnail helper (this is optional) -->
    <link rel="stylesheet" type="text/css"
          href="{{ asset('vendors/fancybox/helpers/css/jquery.fancybox-thumbs.css') }}"/>


    <style>
        #fancybox-thumbs ul li a{

            background-image: url('{{ asset('img/img_holder/gal/4.jpg') }}');
        }
        #fancybox-thumbs ul li img {
            display: block;
            position: relative;
            border: 0;
            padding: 0;
            max-width: none !important; /* ADD THIS LINE*/
        }
    </style>

    <!--page level css end-->
@stop

{{-- Page content --}}
@section('content')

    <section class="content-header">
        <h1>{{ __('ui.gallery') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('admin_dashboard.page_title') }}
                </a>
            </li>
            <li>
                <a href="#">{{ __('ui.gallery') }}</a>
            </li>
            <li class="active">
                {{ __('ui.gallery') }}
            </li>
        </ol>
    </section>
    <section class="content pr-3 pl-3">
        <div class="row">
            <div class="col-lg-12">
                <div class="card tabtop">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title">
                            <i class="livicon" data-name="image" data-size="16" data-loop="true" data-c="#fff"
                               data-hc="white"></i>
                            {{ __('ui.gallery') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-lg">
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a href="#tab_1"  class="nav-link active" data-toggle="tab">{{ __('ui.nav_basic_gallery') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tab_2" class="nav-link" data-toggle="tab">{{ __('ui.nav_standard_gallery') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tab_3"  class="nav-link" data-toggle="tab">{{ __('ui.nav_button_helper') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tab_4" class="nav-link" data-toggle="tab">{{ __('ui.nav_thubnail_helper') }}</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="clothing-nav-content">

                                    <div class="tab-pane fade show active p-3" id="tab_1">
                                        <div class="row">
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                                </div>
                                        <div class="row">
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-b"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-c"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_on_popup_for_exit') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-6 col-sm-3 p-2">
                                                <a class="fancybox-effects-a"
                                                   href="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                   title="{{ __('ui.nav_click_aside_to_exit_popup') }}">
                                                    <img src="{{ asset('img/img_holder/gal/1.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /basic gallery -->
                                    <div class="tab-pane  fade p-3" id="tab_2">
                                        <div class="row">
                                        <div class="col-md-12 col-lg-12 col-12 ">
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_1') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_2') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_3') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-xs-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_1') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_2') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_3') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_1') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 ol-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_2') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                                </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_3') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_1') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_2') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_3') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                                </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox"
                                                   href="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                   data-fancybox-group="gallery" title="{{ __('ui.nav_image_title_4') }}">
                                                    <img src="{{ asset('img/img_holder/gal/2.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                        </div>
                                        </div>
                                    <!-- /standard gallery -->
                                    <div class="tab-pane fade p-3" id="tab_3">
                                        <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-buttons" data-fancybox-group="button"
                                                   href="{{ asset('img/img_holder/gal/3.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/3.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                            </div>
                                        </div>
                                    <!-- /button helper gallery -->
                                    <div class="tab-pane fade p-3" id="tab_4">
                                        <div class="row">
                                        <div class="col-md-12 col-lg-12 col-12">
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-6 col-sm-3 p-2">
                                                <a class="fancybox-thumbs" data-fancybox-group="thumb"
                                                   href="{{ asset('img/img_holder/gal/4.jpg') }}">
                                                    <img src="{{ asset('img/img_holder/gal/4.jpg') }}"
                                                         class="img-fluid gallery-style" alt="Image">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                        </div>
                                    <!-- /thumnail helper gallery -->
                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- nav-tabs-custom -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- row-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')

    <!-- Add mousewheel plugin (this is optional) -->
    <script type="text/javascript"
            src="{{ asset('vendors/fancybox/js/jquery.mousewheel.pack.js') }}"></script>
    <script type="text/javascript"
            src="{{ asset('vendors/fancybox/js/jquery.fancybox.pack.js') }}"></script>
    <script type="text/javascript"
            src="{{ asset('vendors/fancybox/helpers/js/jquery.fancybox-buttons.js') }}"></script>
    <script type="text/javascript"
            src="{{ asset('vendors/fancybox/helpers/js/jquery.fancybox-thumbs.js') }}"></script>
    <!-- Add Media helper (this is optional) -->
    <script type="text/javascript"
            src="{{ asset('vendors/fancybox/helpers/js/jquery.fancybox-media.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pages/jquery_fancy_thumb.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/pages/gallery.js') }}"></script>


<script>
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        e.target // newly activated tab
        e.relatedTarget // previous active tab
        $("#clothing-nav-content .tab-pane").removeClass("show active");
    });
</script>

@stop
