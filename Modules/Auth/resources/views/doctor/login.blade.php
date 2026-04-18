@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth::auth.doctor_login'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'], 'build/modules/theme')
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'], 'build/modules/theme')
@endsection

@section('vendor-script')
    @vite([
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js'], 'build/modules/theme')
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-auth.js'], 'build/modules/theme')
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Login Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('landing/assets/img/tagiy.svg') }}" alt="{{ config('app.name') }}">
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2">{{ trans('auth::auth.doctor_login') }}</h4>
                        <p class="mb-4">{{ trans('auth::auth.doctor_login_subtitle') }}</p>

                        <!-- Demo Credentials -->
                        <x-auth::demo-credentials user-type="doctor" />

                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-3" action="{{ route('doctor.login.post') }}" method="POST">
                            @csrf

                            <x-core::input
                                label="auth::auth.email"
                                type="email"
                                name="email"
                                id="email"
                                placeholder="{{ trans('auth::auth.email_placeholder') }}"
                                value="{{ old('email') }}"
                                autofocus="autofocus" />

                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">{{ trans('auth::auth.password') }}</label>
                                    <a href="{{ route('doctor.password.request') }}">
                                        <small>{{ trans('auth::auth.forgot_password') }}</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                           aria-describedby="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                                </div>
                                @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        {{ trans('auth::auth.remember_me') }}
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    {{ trans('auth::auth.sign_in') }}
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>{{ trans('auth::auth.new_doctor') }}</span>
                            <a href="{{ route('doctor.register') }}">
                                <span>{{ trans('auth::auth.create_account') }}</span>
                            </a>
                        </p>

                        <p class="text-center">
                            <span>{{ trans('auth::auth.are_you_patient') }}</span>
                            <a href="{{ route('patient.login') }}">
                                <span>{{ trans('auth::auth.patient_login') }}</span>
                            </a>
                        </p>

                        <!-- Social Login Buttons -->
                        <x-auth::social-login-buttons user-type="doctor" />
                    </div>
                </div>
                <!-- /Login Card -->
            </div>
        </div>
    </div>
@endsection
