@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    View User Details
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <link href="{{ asset('vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendors/x-editable/css/bootstrap-editable.css') }}" rel="stylesheet"/>

    <style>
        body {
            background-color: #f5f7fa;
        }

        h1 {
            color:#10332C;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-pills .nav-link {
            background-color: #f1f1f1;
            margin-right: 10px;
            color: #4a4a4a;
            border-radius: 50px;
            transition: background-color 0.3s;
        }

        .nav-pills .nav-link.active, .nav-pills .nav-link:hover {
            background-color:#10332C;
            color: white;
        }

        .profile-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-radius: 15px;
            background-color: #fafafa;
        }

        .profile-card img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid#10332C;
        }

        .info-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .info-group:last-child {
            border-bottom: none;
        }

        .info-group i {
            color:#10332C;
            font-size: 20px;
        }

        .info-label {
            font-weight: bold;
            margin-right: 10px;
            color: #4a4a4a;
        }

        .info-value {
            color: #333;
        }

        .btn {
            background-color:#10332C;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color:#1F514D;
        }

        .btn-secondary {
            background-color: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #ccc;
        }
    </style>
@stop

{{-- Page content --}}
@section('content')
    <section class="content-header">
        <h1>{{ __('ui.user_profile') }}</h1>
    </section>

    <section class="profile-container">
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#profile" data-toggle="pill">{{ __('ui.user_profile') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#password" data-toggle="pill">{{ __('ui.change_password') }}</a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="{{ URL::to('admin/user_profile') }}">Advanced Profile</a>
            </li> -->
        </ul>

        <div class="tab-content">
            <!-- User Profile Tab -->
            <div class="tab-pane fade show active" id="profile">
                <div class="profile-card">
                    <img
                        src="{{ $user->pic ?? ($user->gender === 'male' ? asset('images/authors/avatar3.png') : ($user->gender === 'female' ? asset('images/authors/avatar5.png') : asset('images/authors/no_avatar.jpg'))) }}"
                        alt="User Avatar">
                    <div>
                        <h4>{{ $user->first_name }} {{ $user->last_name }}</h4>
                        <p><strong>{{ __('ui.email') }}</strong> {{ $user->email }}</p>
                        <p><strong>{{ __('ui.status') }}</strong>
                            @if($user->deleted_at) Deleted
                            @elseif(Activation::completed($user)) {{ __('ui.activated') }}
                            @else {{ __('application_status.testing_pending') }} @endif
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="info-group">
                        <i class="fas fa-venus-mars"></i>
                        <span class="info-label">{{ __('ui.gender') }}</span>
                        <span class="info-value">{{ $user->gender }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="info-label">{{ __('ui.date_of_birth') }}</span>
                        <span class="info-value">{{ $user->dob != '0000-00-00' ? $user->dob : '-' }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-flag"></i>
                        <span class="info-label">{{ __('ui.country') }}</span>
                        <span class="info-value">{{ $user->country }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="info-label">{{ __('ui.state') }}</span>
                        <span class="info-value">{{ $user->state }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-city"></i>
                        <span class="info-label">{{ __('ui.city') }}</span>
                        <span class="info-value">{{ $user->city }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-home"></i>
                        <span class="info-label">{{ __('ui.address') }}</span>
                        <span class="info-value">{{ $user->address }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-envelope"></i>
                        <span class="info-label">{{ __('ui.postal_code') }}</span>
                        <span class="info-value">{{ $user->postal }}</span>
                    </div>
                    <div class="info-group">
                        <i class="fas fa-calendar-check"></i>
                        <span class="info-label">{{ __('ui.created_at') }}</span>
                        <span class="info-value">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="password">
                <form class="form-horizontal mt-4">
                    @csrf
                    <div class="form-group">
                        <label for="password">{{ __('ui.new_password') }}</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="{{ __('ui.enter_new_password') }}">
                    </div>

                    <div class="form-group">
                        <label for="password-confirm">{{ __('ui.confirm_password') }}</label>
                        <input type="password" id="password-confirm" name="confirm_password" class="form-control" placeholder="{{ __('ui.confirm_new_password') }}">
                    </div>

                    <button type="submit" class="btn mt-2" id="change-password">{{ __('button.submit') }}</button>
                    <input type="reset" class="btn btn-secondary mt-2" value="Reset">
                </form>
            </div>
        </div>
    </section>
@stop

@section('footer_scripts')
    <script>
        $(document).ready(function () {
            $('#change-password').click(function (e) {
                e.preventDefault();
                const password = $('#password').val();
                const confirmPassword = $('#password-confirm').val();

                if (password === "") {
                    alert(@js(__('ui.please_enter_a_password')));
                } else if (password !== confirmPassword) {
                    alert(@js(__('ui.passwords_do_not_match')));
                } else {
                    const data = {
                        _token: '{{ csrf_token() }}',
                        password: password,
                        id: {{ $user->id }}
                    };
                    $.post("passwordreset", data, function () {
                        alert(@js(__('ui.password_reset_successful')));
                        $('#password, #password-confirm').val('');
                    }).fail(function () {
                        alert(@js(__('ui.error_in_password_reset')));
                    });
                }
            });
        });
    </script>
@stop
