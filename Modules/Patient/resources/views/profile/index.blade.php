@php $page = 'patient-profile'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('patient::patient.sidebar.my_profile'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('patient::patient.sidebar.my_profile') }}</h5>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('patient.profile.update') }}" method="POST">
                                @csrf

                                <div class="row">
                                    {{-- Personal Information --}}
                                    <div class="col-12 mb-4">
                                        <h6 class="fw-bold">{{ trans('patient::patient.personal_info') }}</h6>
                                        <hr>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $patient->name) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.email') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.age') }}</label>
                                        <input type="number" name="age" class="form-control" value="{{ old('age', $patient->age) }}" min="1" max="150">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.gender') }}</label>
                                        <select name="gender" class="form-select">
                                            <option value="">{{ trans('patient::patient.select') }}</option>
                                            @foreach(\Modules\Core\App\Enums\Gender::cases() as $gender)
                                                <option value="{{ $gender->value }}" {{ old('gender', $patient->gender?->value) == $gender->value ? 'selected' : '' }}>
                                                    {{ $gender->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.blood_type') }}</label>
                                        <select name="blood_type" class="form-select">
                                            <option value="">{{ trans('patient::patient.select') }}</option>
                                            @foreach(\Modules\Doctor\Enums\BloodType::cases() as $bloodType)
                                                <option value="{{ $bloodType->value }}" {{ old('blood_type', $patient->blood_type?->value) == $bloodType->value ? 'selected' : '' }}>
                                                    {{ $bloodType->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.marital_status') }}</label>
                                        <select name="marital_status" class="form-select">
                                            <option value="">{{ trans('patient::patient.select') }}</option>
                                            @foreach(\Modules\Doctor\Enums\MaritalStatus::cases() as $status)
                                                <option value="{{ $status->value }}" {{ old('marital_status', $patient->marital_status?->value) == $status->value ? 'selected' : '' }}>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.nationality') }}</label>
                                        <select name="nationality_id" class="form-select">
                                            <option value="">{{ trans('patient::patient.select') }}</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}" {{ old('nationality_id', $patient->nationality_id) == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.work') }}</label>
                                        <input type="text" name="work" class="form-control" value="{{ old('work', $patient->work) }}">
                                    </div>

                                    {{-- Medical Information --}}
                                    <div class="col-12 mb-4 mt-3">
                                        <h6 class="fw-bold">{{ trans('patient::patient.medical_info') }}</h6>
                                        <hr>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.drug_allergies') }}</label>
                                        <textarea name="drug_allergies" class="form-control" rows="3">{{ old('drug_allergies', $patient->drug_allergies) }}</textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.disabilities') }}</label>
                                        <textarea name="disabilities" class="form-control" rows="3">{{ old('disabilities', $patient->disabilities) }}</textarea>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.medical_history') }}</label>
                                        <textarea name="medical_history" class="form-control" rows="3">{{ old('medical_history', $patient->medical_history) }}</textarea>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.surgical_history') }}</label>
                                        <textarea name="surgical_history" class="form-control" rows="3">{{ old('surgical_history', $patient->surgical_history) }}</textarea>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.accident_history') }}</label>
                                        <textarea name="accident_history" class="form-control" rows="3">{{ old('accident_history', $patient->accident_history) }}</textarea>
                                    </div>

                                    {{-- Change Password --}}
                                    <div class="col-12 mb-4 mt-3">
                                        <h6 class="fw-bold">{{ trans('patient::patient.change_password') }}</h6>
                                        <hr>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.new_password') }}</label>
                                        <input type="password" name="password" class="form-control" placeholder="{{ trans('patient::patient.leave_blank') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('patient::patient.confirm_password') }}</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti tabler-check me-1"></i> {{ trans('patient::patient.save_changes') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
