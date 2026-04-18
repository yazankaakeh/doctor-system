@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth::auth.forgot_password_title'))

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
                <!-- Forgot Password Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('landing/assets/img/tagiy.svg') }}" alt="{{ config('app.name') }}">
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2">{{ trans('auth::auth.forgot_password_title') }}</h4>
                        <p class="mb-4">{{ trans('auth::auth.forgot_password_subtitle') }}</p>

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

                        <form id="formAuthentication" class="mb-3" action="{{ route('patient.password.email') }}" method="POST">
                            @csrf

                            <x-core::input
                                label="auth::auth.email"
                                type="email"
                                name="email"
                                id="email"
                                placeholder="{{ trans('auth::auth.email_placeholder') }}"
                                value="{{ old('email') }}"
                                autofocus="autofocus" />

                            <button class="btn btn-primary d-grid w-100">
                                {{ trans('auth::auth.send_reset_link') }}
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('patient.login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="ti tabler-chevron-left scaleX-n1-rtl me-1"></i>
                                {{ trans('auth::auth.back_to_login') }}
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password Card -->
            </div>
        </div>
    </div>
@endsection
