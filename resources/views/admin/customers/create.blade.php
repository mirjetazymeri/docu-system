@extends('admin.layouts.default')

@section('title')
    {{ __('words.add_customer') }}
    @parent
@stop

@section('content')
    <section class="content-header">
        <h1>{{ __('words.add_customer') }}</h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="home" data-size="14" data-color="#000"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li class="active">{{ __('words.add_customer') }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('words.add_customer') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">{{ __('words.user') }}</label>
                        <div class="input-group">
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">{{ __('words.select_user') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#createUserModal">{{ __('words.add_user') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="full_name">{{ __('words.full_name') }}</label>
                        <input type="text" name="full_name" id="full_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('words.email') }}</label>
                        <input type="email" name="email" id="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('words.phone') }}</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="address">{{ __('words.address') }}</label>
                        <input type="text" name="address" id="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city">{{ __('words.city') }}</label>
                        <input type="text" name="city" id="city" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="state">{{ __('words.state') }}</label>
                        <input type="text" name="state" id="state" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="postal_code">{{ __('words.postal_code') }}</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="country">{{ __('words.country') }}</label>
                        <input type="text" name="country" id="country" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('words.add_customer_button') }}</button>
                </form>
            </div>
        </div>
    </section>

    <!-- User Creation Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">{{ __('words.add_user') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('ui.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        @csrf
                        <div class="form-group">
                            <label for="first_name">{{ __('words.first_name') }}</label>
                            <input type="text" name="first_name" id="first_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="last_name">{{ __('words.last_name') }}</label>
                            <input type="text" name="last_name" id="last_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="email">{{ __('words.email') }}</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="password">{{ __('words.password') }}</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <button type="button" class="btn btn-primary" id="saveUserButton">{{ __('words.save_user') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    <script>
        $('#user_id').change(function() {
            let selectedOption = $(this).find('option:selected');
            let fullName = selectedOption.text();
            $('#full_name').val(fullName);
        });

        $('#saveUserButton').click(function() {
            $.ajax({
                url: '{{ route("admin.customers.storeUser") }}',
                method: 'POST',
                data: $('#createUserForm').serialize(),
                success: function(response) {
                    $('#user_id').append(new Option(response.first_name + ' ' + response.last_name, response.id, true, true)).trigger('change');
                    $('#createUserModal').modal('hide');
                    $('#createUserForm')[0].reset();
                },
                error: function(xhr) {
                    alert('An error occurred while creating the user.');
                }
            });
        });
    </script>
@stop
