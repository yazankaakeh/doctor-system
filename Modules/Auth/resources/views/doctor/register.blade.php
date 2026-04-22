@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth::auth.doctor_registration'))

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
                <!-- Register Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('landing/assets/img/tagiy.svg') }}" alt="{{ config('app.name') }}">
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2">{{ trans('auth::auth.doctor_registration') }}</h4>
                        <p class="mb-4">{{ trans('auth::auth.doctor_registration_subtitle') }}</p>

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

                        <form id="formAuthentication" class="mb-3" action="{{ route('doctor.register.post') }}" method="POST">
                            @csrf

                            <x-core::input
                                label="auth::auth.full_name"
                                type="text"
                                name="name"
                                id="name"
                                placeholder="{{ trans('auth::auth.name_placeholder') }}"
                                value="{{ old('name') }}"
                                autofocus="autofocus" />

                            <x-core::input
                                label="auth::auth.email"
                                type="email"
                                name="email"
                                id="email"
                                placeholder="{{ trans('auth::auth.email_placeholder') }}"
                                value="{{ old('email') }}" />

                            <x-core::input
                                label="auth::auth.phone"
                                type="text"
                                name="phone"
                                id="phone"
                                placeholder="{{ trans('auth::auth.phone_placeholder') }}"
                                value="{{ old('phone') }}" />

                            <div class="mb-3">
                                <label class="form-label" for="medical_specialty_id">
                                    {{ trans('auth::auth.medical_specialty') }} <span class="text-danger">*</span>
                                </label>
                                <select name="medical_specialty_id" id="medical_specialty_id"
                                        class="form-select @error('medical_specialty_id') is-invalid @enderror" required>
                                    <option value="">{{ trans('auth::auth.select_specialty') }}</option>
                                    @foreach($medicalSpecialties as $id => $name)
                                        <option value="{{ $id }}" {{ old('medical_specialty_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('medical_specialty_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="gender">{{ trans('auth::auth.gender') }}</label>
                                        <select name="gender" id="gender" class="form-select">
                                            <option value="">{{ trans('auth::auth.select') }}</option>
                                            @foreach(\Modules\Core\App\Enums\Gender::cases() as $gender)
                                                <option value="{{ $gender->value }}" {{ old('gender') == $gender->value ? 'selected' : '' }}>
                                                    {{ $gender->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <x-core::input
                                        label="auth::auth.age"
                                        type="number"
                                        name="age"
                                        id="age"
                                        value="{{ old('age') }}"
                                        min="18"
                                        max="100" />
                                </div>
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <label class="form-label" for="password">{{ trans('auth::auth.password') }} <span class="text-danger">*</span></label>
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

                            <div class="mb-3 form-password-toggle">
                                <label class="form-label" for="password-confirm">{{ trans('auth::auth.confirm_password') }} <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password-confirm" class="form-control"
                                           name="password_confirmation"
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                           aria-describedby="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                                </div>
                            </div>

                            {{-- Live strong-password feedback (requirements + strength bar). --}}
                            <div class="mb-3">
                                <x-auth::password-strength target="password" match="password-confirm" />
                            </div>

                            <div class="mb-3">
                                <div class="alert alert-info mb-0">
                                    <i class="ti tabler-info-circle me-1"></i>
                                    {{ trans('auth::auth.registration_approval_notice') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    {{ trans('auth::auth.sign_up') }}
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>{{ trans('auth::auth.already_have_account') }}</span>
                            <a href="{{ route('doctor.login') }}">
                                <span>{{ trans('auth::auth.sign_in_instead') }}</span>
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
                <!-- /Register Card -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-auth::password-toggle-script />
@endpush
