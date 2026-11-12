@extends('admin/layouts/default')

{{-- Page title --}}
@section('title')
    {{ __('words.add_user') }}
    @parent
@stop

{{-- Page level styles --}}
@section('header_styles')
    <link href="{{ asset('vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/select2/css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('vendors/datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin/custom-styles.css') }}" rel="stylesheet">
    <style>
        /* Custom Styling */
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
        }

        .card-header {
            background-color: #f4f7fc;
            color: #333;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            font-size: 1.75rem;
            font-weight: 700;
            border-bottom: 2px solid #ddd;
        }

        .card-body {
            padding: 30px;
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: none;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #6AB295;
            box-shadow: 0 0 5px rgba(106, 178, 149, 0.6);
        }

        .form-check-label {
            font-size: 1rem;
            font-weight: 500;
            margin-left: 10px;
        }

        .btn {
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1.1rem;
            text-transform: uppercase;
            background-color: #6AB295;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #4a9c7a;
        }

        .img-thumbnail {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.1);
        }

        /* Form Layout Adjustments */
        .form-group {
            margin-bottom: 25px;
        }

        .col-md-6 {
            padding-right: 15px;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .col-md-6, .col-md-4 {
                margin-bottom: 15px;
            }

            .img-thumbnail {
                width: 100px;
                height: 100px;
            }

            .card-body {
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }
        }

        h2 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 40px;
        }
    </style>
@stop

@section('content')
    <div class="container">
        <h2>{{ __('ui.create_user') }}</h2>
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('ui.the_user_could_not_be_created') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-header">
                    {{ __('ui.user_information') }}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">{{ __('ui.first_name') }}</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">{{ __('ui.last_name') }}</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">{{ __('ui.email') }}</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="role">{{ __('ui.role') }}</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">{{ __('ui.select_a_role') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>{{ __($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="country">{{ __('ui.country') }}</label>
                        <select class="form-control" id="country" name="country" required>
                            <option value="">{{ __('ui.select_a_country') }}</option>
                            @foreach ($countries as $code => $name)
                                <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="access_level_ids">{{ __('ui.access_levels') }}</label>
                        <select class="form-control" id="access_level_ids" name="access_level_ids[]" multiple>
                            @foreach ($accessLevels as $accessLevel)
                                <option value="{{ $accessLevel->id }}"
                                        {{ in_array($accessLevel->id, old('access_level_ids', [])) ? 'selected' : '' }}>{{ __($accessLevel->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pic_file">{{ __('ui.profile_picture') }}</label>
                        <input type="file" class="form-control" id="pic_file" name="pic_file">
                    </div>

                    <div class="form-group">
                        <label for="password">{{ __('ui.password') }}</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">{{ __('ui.confirm_password') }}</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="activate" name="activate" value="1" {{ old('activate') ? 'checked' : '' }}>
                            <label class="form-check-label" for="activate">{{ __('ui.activate_user') }}</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('ui.create_user') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

{{-- Footer Scripts --}}
@section('footer_scripts')
    <script src="{{ asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendors/select2/js/select2.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/datetimepicker/js/moment.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script src="{{ asset('vendors/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/admin/custom-scripts.js') }}"></script>
@stop
