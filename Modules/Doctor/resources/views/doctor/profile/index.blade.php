@php $page = 'doctor-profile'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('doctor::doctor.profile.my_profile'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="mb-0">{{ trans('doctor::doctor.profile.my_profile') }}</h5>
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

                            <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    {{-- Avatar Section --}}
                                    <div class="col-12 mb-4">
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="avatar-wrapper">
                                                @if($doctor->getFirstMediaUrl('img'))
                                                    <img src="{{ $doctor->getFirstMediaUrl('img', 'preview') }}"
                                                         alt="{{ $doctor->name }}"
                                                         class="rounded-circle"
                                                         id="avatar-preview"
                                                         style="width: 100px; height: 100px; object-fit: cover;">
                                                @else
                                                    <div class="avatar avatar-xl bg-primary rounded-circle d-flex align-items-center justify-content-center" id="avatar-placeholder" style="width: 100px; height: 100px;">
                                                        <span class="fs-2 text-white">{{ strtoupper(substr($doctor->name, 0, 1)) }}</span>
                                                    </div>
                                                    <img src="" alt="" class="rounded-circle d-none" id="avatar-preview" style="width: 100px; height: 100px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div>
                                                <label for="avatar" class="btn btn-outline-primary btn-sm">
                                                    <i class="ti tabler-upload me-1"></i> {{ trans('doctor::doctor.profile.change_avatar') }}
                                                </label>
                                                <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*" onchange="previewAvatar(this)">
                                                <p class="text-muted small mb-0 mt-1">{{ trans('doctor::doctor.profile.avatar_hint') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Personal Information --}}
                                    <div class="col-12 mb-3">
                                        <h6 class="fw-bold">{{ trans('doctor::doctor.profile.personal_info') }}</h6>
                                        <hr>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $doctor->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.email') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $doctor->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $doctor->phone) }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.date_of_birth') }}</label>
                                        <input type="date" name="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age', $doctor->age) }}">
                                        @error('age')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.gender') }}</label>
                                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                            <option value="">{{ trans('doctor::doctor.pleaseSelectOne') }}</option>
                                            @foreach(\Modules\Core\App\Enums\Gender::cases() as $gender)
                                                <option value="{{ $gender->value }}" {{ old('gender', $doctor->gender?->value) == $gender->value ? 'selected' : '' }}>
                                                    {{ $gender->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.specialty') }}</label>
                                        <select name="medical_specialty_id" class="form-select @error('medical_specialty_id') is-invalid @enderror">
                                            <option value="">{{ trans('doctor::doctor.pleaseSelectOne') }}</option>
                                            @foreach($specialties as $specialty)
                                                <option value="{{ $specialty->id }}" {{ old('medical_specialty_id', $doctor->medical_specialty_id) == $specialty->id ? 'selected' : '' }}>
                                                    {{ $specialty->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('medical_specialty_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.bio') }}</label>
                                        <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4" placeholder="{{ trans('doctor::doctor.profile.bio_placeholder') }}">{{ old('bio', $doctor->bio) }}</textarea>
                                        @error('bio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Change Password --}}
                                    <div class="col-12 mb-3 mt-3">
                                        <h6 class="fw-bold">{{ trans('doctor::doctor.profile.change_password') }}</h6>
                                        <hr>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.new_password') }}</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ trans('doctor::doctor.profile.leave_blank') }}">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ trans('doctor::doctor.profile.confirm_password') }}</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti tabler-check me-1"></i> {{ trans('doctor::doctor.profile.save_changes') }}
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

@push('scripts')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');

                preview.src = e.target.result;
                preview.classList.remove('d-none');

                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
