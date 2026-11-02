@extends('layouts.app')

@section('title', 'Login - URL Shortener')

@section('content')

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-3">

    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 430px;">

        <div class="card-body p-4 p-md-5">


            <!-- Heading -->
            <h3 class="text-center fw-bold mb-2">
                Login
            </h3>



            <!-- Alert -->
            <div id="loginAlert" class="alert d-none" role="alert"> </div>

            <!-- Login Form -->
            <form id="loginForm">

                @csrf

                <!-- Email -->
                <div class="mb-3">

                    <label for="email" class="form-label fw-semibold">
                        Email Address
                    </label>

                    <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" autocomplete="email">
                    <div id="emailError" class="text-danger small mt-1"></div>

                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Enter your password" autocomplete="current-password">
                    <div id="passwordError" class="text-danger small mt-1"></div>

                </div>

                <!-- Login Button -->
                <button type="submit" id="loginButton" class="btn btn-primary btn-lg w-100">
                    <span id="loginButtonText">Login</span>
                    <span id="loginLoader" class="spinner-border spinner-border-sm d-none ms-1" role="status" aria-hidden="true"></span>

                </button>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

$(document).ready(function () {




    // Login Form Submit
    $('#loginForm').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);
        let button = $('#loginButton');
        let buttonText = $('#loginButtonText');
        let loader = $('#loginLoader');

        $('#emailError').text('');
        $('#passwordError').text('');

        $('#loginAlert').addClass('d-none').removeClass('alert-success alert-danger').text('');
        button.prop('disabled', true);
        buttonText.text('Logging in...');
        loader.removeClass('d-none');


        $.ajax({

            url: "{{ route('login.submit') }}",
            type: "POST",
            data: form.serialize(),
            dataType: "json",

            success: function (response) {

                if (response.success) {

                    $('#loginAlert').removeClass('d-none').addClass('alert-success').text(response.message);

                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 500);

                } else {

                    $('#loginAlert').removeClass('d-none').addClass('alert-danger').text(response.message || 'Login failed.');

                }

            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    let response = xhr.responseJSON;

                    if (response.errors) {

                        if (response.errors.email) {
                            $('#emailError').text(response.errors.email[0]);

                        }

                        if (response.errors.password) {
                            $('#passwordError').text(response.errors.password[0]);

                        }

                    }

                } else {

                    let message = 'Something went wrong. Please try again.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {

                        message = xhr.responseJSON.message;

                    }

                    $('#loginAlert')
                        .removeClass('d-none')
                        .addClass('alert-danger')
                        .text(message);

                }

            },

            complete: function () {

                button.prop('disabled', false);

                buttonText.text('Login');

                loader.addClass('d-none');

            }

        });

    });

});

</script>

@endpush
