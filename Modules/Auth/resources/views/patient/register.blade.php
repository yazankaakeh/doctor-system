@php
    use Modules\Core\App\Enums\Gender;
    use Modules\Doctor\Enums\BloodType;
    use Modules\Doctor\Enums\MaritalStatus;
    use Modules\Core\app\Models\Country;

    $customizerHidden = 'customizer-hide';
    $countries = Country::getCountriesSelect2();
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', trans('auth::auth.patient_registration'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'], 'build/modules/theme')
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'], 'build/modules/theme')
    <style>
        .registration-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-section {
            border-bottom: 1px solid var(--bs-border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .section-header i {
            font-size: 1.25rem;
            color: var(--bs-primary);
        }
        .section-header h6 {
            margin: 0;
            font-weight: 600;
        }
        .optional-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        .collapse-toggle {
            cursor: pointer;
            user-select: none;
        }
        .collapse-toggle:hover {
            opacity: 0.8;
        }
        .collapse-toggle .toggle-icon {
            transition: transform 0.3s ease;
        }
        .collapse-toggle[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }
        .gender-options {
            display: flex;
            gap: 1rem;
        }
        .gender-option {
            flex: 1;
        }
        .gender-option input[type="radio"] {
            display: none;
        }
        .gender-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 2px solid var(--bs-border-color);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }
        .gender-option input[type="radio"]:checked + label {
            border-color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }
        .gender-option label:hover {
            border-color: var(--bs-primary);
        }
        .row-cols-2 > * {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    </style>
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
                <div class="card registration-card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('landing/assets/img/tagiy.svg') }}" alt="{{ config('app.name') }}">
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2 text-center">{{ trans('auth::auth.patient_registration') }}</h4>
                        <p class="mb-4 text-center">{{ trans('auth::auth.patient_registration_subtitle') }}</p>

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

                        <form id="formAuthentication" class="mb-3" action="{{ route('patient.register.post') }}" method="POST">
                            @csrf

                            {{-- Section 1: Account Information --}}
                            <div class="form-section">
                                <div class="section-header">
                                    <i class="ti tabler-user-circle"></i>
                                    <h6>{{ trans('auth::auth.personal_information') }}</h6>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="name">{{ trans('auth::auth.full_name') }} <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti tabler-user"></i></span>
                                        <input type="text"
                                               id="name"
                                               name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="{{ trans('auth::auth.name_placeholder') }}"
                                               value="{{ old('name') }}"
                                               autofocus
                                               required />
                                    </div>
                                    @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">{{ trans('auth::auth.email') }} <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                                        <input type="email"
                                               id="email"
                                               name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               placeholder="{{ trans('auth::auth.email_placeholder') }}"
                                               value="{{ old('email') }}"
                                               required />
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="phone">{{ trans('auth::auth.phone') }} <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti tabler-phone"></i></span>
                                        <input type="tel"
                                               id="phone"
                                               name="phone"
                                               class="form-control @error('phone') is-invalid @enderror"
                                               placeholder="{{ trans('auth::auth.phone_placeholder') }}"
                                               value="{{ old('phone') }}"
                                               required />
                                    </div>
                                    @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3 form-password-toggle">
                                        <label class="form-label" for="password">{{ trans('auth::auth.password') }} <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                            <input type="password"
                                                   id="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   name="password"
                                                   placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                   required />
                                            <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                                        </div>
                                        @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3 form-password-toggle">
                                        <label class="form-label" for="password-confirm">{{ trans('auth::auth.confirm_password') }} <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti tabler-lock-check"></i></span>
                                            <input type="password"
                                                   id="password-confirm"
                                                   class="form-control"
                                                   name="password_confirmation"
                                                   placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                   required />
                                            <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                                        </div>
                                    </div>

                                    {{-- Live strong-password feedback (requirements + strength bar). --}}
                                    <div class="col-12">
                                        <x-auth::password-strength target="password" match="password-confirm" />
                                    </div>
                                </div>
                            </div>

                            {{-- Section 2: Additional Information (Collapsible) --}}
                            <div class="form-section">
                                <div class="section-header collapse-toggle d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse"
                                     data-bs-target="#additionalInfo"
                                     aria-expanded="{{ old('gender') || old('age') || old('blood_type') || old('marital_status') || old('nationality_id') || old('work') ? 'true' : 'false' }}"
                                     aria-controls="additionalInfo">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti tabler-heart-rate-monitor"></i>
                                        <h6 class="mb-0">{{ trans('auth::auth.additional_info') }}</h6>
                                        <span class="badge bg-label-secondary optional-badge">{{ trans('auth::auth.additional_info_subtitle') }}</span>
                                    </div>
                                    <i class="ti tabler-chevron-down toggle-icon"></i>
                                </div>

                                <div class="collapse {{ old('gender') || old('age') || old('blood_type') || old('marital_status') || old('nationality_id') || old('work') ? 'show' : '' }}" id="additionalInfo">
                                    <div class="pt-3">
                                        {{-- Gender Selection --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ trans('auth::auth.gender') }}</label>
                                            <div class="gender-options">
                                                <div class="gender-option">
                                                    <input type="radio" name="gender" id="gender_male" value="{{ Gender::MALE->value }}" {{ old('gender') == Gender::MALE->value ? 'checked' : '' }}>
                                                    <label for="gender_male">
                                                        <i class="ti tabler-gender-male"></i>
                                                        {{ trans('auth::auth.male') }}
                                                    </label>
                                                </div>
                                                <div class="gender-option">
                                                    <input type="radio" name="gender" id="gender_female" value="{{ Gender::FEMALE->value }}" {{ old('gender') == Gender::FEMALE->value ? 'checked' : '' }}>
                                                    <label for="gender_female">
                                                        <i class="ti tabler-gender-female"></i>
                                                        {{ trans('auth::auth.female') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @error('gender')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            {{-- Age --}}
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="age">{{ trans('auth::auth.age') }}</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                                                    <input type="number"
                                                           id="age"
                                                           name="age"
                                                           class="form-control @error('age') is-invalid @enderror"
                                                           placeholder="{{ trans('auth::auth.age_placeholder') }}"
                                                           value="{{ old('age') }}"
                                                           min="1"
                                                           max="150" />
                                                </div>
                                                @error('age')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Blood Type --}}
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="blood_type">{{ trans('auth::auth.blood_type') }}</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti tabler-droplet"></i></span>
                                                    <select id="blood_type"
                                                            name="blood_type"
                                                            class="form-select @error('blood_type') is-invalid @enderror">
                                                        <option value="">{{ trans('auth::auth.select_blood_type') }}</option>
                                                        @foreach(BloodType::cases() as $bloodType)
                                                            <option value="{{ $bloodType->value }}" {{ old('blood_type') == $bloodType->value ? 'selected' : '' }}>
                                                                {{ $bloodType->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('blood_type')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- Marital Status --}}
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="marital_status">{{ trans('auth::auth.marital_status') }}</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti tabler-heart"></i></span>
                                                    <select id="marital_status"
                                                            name="marital_status"
                                                            class="form-select @error('marital_status') is-invalid @enderror">
                                                        <option value="">{{ trans('auth::auth.select_marital_status') }}</option>
                                                        @foreach(MaritalStatus::cases() as $status)
                                                            <option value="{{ $status->value }}" {{ old('marital_status') == $status->value ? 'selected' : '' }}>
                                                                {{ $status->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('marital_status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Nationality --}}
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="nationality_id">{{ trans('auth::auth.nationality') }}</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti tabler-flag"></i></span>
                                                    <select id="nationality_id"
                                                            name="nationality_id"
                                                            class="form-select @error('nationality_id') is-invalid @enderror">
                                                        <option value="">{{ trans('auth::auth.select_nationality') }}</option>
                                                        @foreach($countries as $id => $name)
                                                            <option value="{{ $id }}" {{ old('nationality_id') == $id ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('nationality_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Work/Occupation --}}
                                        <div class="mb-3">
                                            <label class="form-label" for="work">{{ trans('auth::auth.work') }}</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="ti tabler-briefcase"></i></span>
                                                <input type="text"
                                                       id="work"
                                                       name="work"
                                                       class="form-control @error('work') is-invalid @enderror"
                                                       placeholder="{{ trans('auth::auth.work_placeholder') }}"
                                                       value="{{ old('work') }}" />
                                            </div>
                                            @error('work')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    <span class="d-flex align-items-center justify-content-center gap-2">
                                        <i class="ti tabler-user-plus"></i>
                                        {{ trans('auth::auth.sign_up') }}
                                    </span>
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>{{ trans('auth::auth.already_have_account') }}</span>
                            <a href="{{ route('patient.login') }}">
                                <span>{{ trans('auth::auth.sign_in_instead') }}</span>
                            </a>
                        </p>

                        <!-- Social Login Buttons -->
                        <x-auth::social-login-buttons user-type="patient" />
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
