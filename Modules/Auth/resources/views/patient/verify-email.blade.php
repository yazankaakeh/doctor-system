@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth::auth.verify_email'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'], 'build/modules/theme')
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'], 'build/modules/theme')
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Verify Email Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('landing/assets/img/tagiy.svg') }}" alt="{{ config('app.name') }}">
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2">{{ trans('auth::auth.verify_email') }}</h4>
                        <p class="mb-4">
                            {{ trans('auth::auth.verify_email_message') }}
                            {{ trans('auth::auth.did_not_receive_email') }}
                        </p>

                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="mb-3" method="POST" action="{{ route('patient.verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary d-grid w-100">
                                {{ trans('auth::auth.request_another') }}
                            </button>
                        </form>

                        <div class="text-center">
                            <form method="POST" action="{{ route('patient.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted">
                                    <i class="ti tabler-logout me-1"></i>
                                    {{ trans('auth::auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /Verify Email Card -->
            </div>
        </div>
    </div>
@endsection
