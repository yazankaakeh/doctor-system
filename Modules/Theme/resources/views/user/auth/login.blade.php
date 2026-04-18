@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth.login.title', ['name' => config('variables.templateName')]))

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
                <!-- Login -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{url('/')}}" class="app-brand-link gap-2">
                                <img src="{{asset('landing/assets/img/tagiy.svg')}}" alt="">
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1 pt-2">
                            {{trans('auth.login.title', ['name' => config('variables.templateName')])}}
                        </h4>
                        <p class="mb-4">
                            {{trans('auth.login.desc')}}
                        </p>

                        <!-- Demo Credentials -->
                        <x-auth::demo-credentials user-type="admin" />

                        <form method="POST" id="formAuthentication" action="{{ route('admin.login.submit') }}"
                              class="mb-3">
                            @csrf
                            <x-core::input
                                label="auth.login.email"
                                type="text"
                                name="email"
                                id="email"
                                placeholder="jon_doe@gmail.com"
                                value="{{ old('email') }}"
                                autofocus="autofocus" />
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">
                                        {{trans('auth.login.password')}}
                                    </label>
                                    {{--  <a href="{{ route('password.request') }}">
                                          <small>
                                              {{trans('auth.login.forgotPassword')}}
                                          </small>
                                      </a>--}}
                                </div>
                                <div class=" input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                           aria-describedby="password"/>
                                    <span class="input-group-text cursor-pointer"><i
                                                class="ti tabler-eye-off"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember-me">
                                    <label class="form-check-label" for="remember-me">
                                        {{trans('auth.login.rememberMe')}}
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <x-core::recaptcha id="recaptcha" name="recaptcha"/>
                                @error('recaptcha')
                                <span class="invalid-feedback d-block" role="alert">
                                      <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    {{trans('auth.login.signIn')}}
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
              <span>
                {{trans('auth.login.newToPlatform')}}
              </span>
                            {{--<a href="{{route('register')}}">
                <span>
                  {{trans('auth.login.createAccount')}}
                </span>
                            </a>--}}
                        </p>

                        {{-- Social Login Buttons for Doctor --}}
                        <x-auth::social-login-buttons user-type="doctor" />
                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
@endsection
