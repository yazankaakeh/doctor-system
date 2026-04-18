@php use Modules\Core\App\Enums\Gender; @endphp
<div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" enctype="multipart/form-data" id="createUser"
              action="{{ route('admin.user_management.store') }}"
              method="POST">
            @csrf
            @method('POST')
            <div class="modal-header bg-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-white rounded-circle me-3">
                        <i class="ti tabler-user-plus text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0">
                            {{ trans('adminmanagement::admin_management.user.create.title') }}
                        </h5>
                        <small class="text-white-50">{{ trans('adminmanagement::admin_management.user.create.subtitle') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Personal Information Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti tabler-user me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.create.personal_info') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="create_name">
                                {{ trans('adminmanagement::admin_management.user.form.name') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-user"></i></span>
                                <input type="text" class="form-control" id="create_name" name="name"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.name_placeholder') }}"
                                       value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_email">
                                {{ trans('adminmanagement::admin_management.user.form.email') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                                <input type="email" class="form-control" id="create_email" name="email"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.email_placeholder') }}"
                                       value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_phone">
                                {{ trans('adminmanagement::admin_management.user.form.phone') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-phone"></i></span>
                                <input type="text" class="form-control" id="create_phone" name="phone"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.phone_placeholder') }}"
                                       value="{{ old('phone') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_age">
                                {{ trans('doctor::doctor.patients.age') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                                <input type="number" class="form-control" id="create_age" name="age"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.age_placeholder') }}"
                                       value="{{ old('age') }}" min="18" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_gender">
                                {{ trans('doctor::doctor.patients.gender') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="create_gender" name="gender" required>
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach(Gender::getAllEnumValuesKeysLabel() as $value => $label)
                                    <option value="{{ $value }}" {{ old('gender') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_img">
                                {{ trans('adminmanagement::admin_management.user.form.img') }}
                            </label>
                            <input type="file" class="form-control" id="create_img" name="img" accept="image/*">
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Professional Information Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti tabler-stethoscope me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.create.professional_info') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="create_role">
                                {{ trans('adminmanagement::admin_management.user.form.role') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" id="create_role" name="role" required>
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach($roles as $id => $name)
                                    <option value="{{ $id }}" {{ old('role') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_medicalSpecialtyId">
                                {{ trans('doctor::doctor.doctor.medicalSpecialtyId') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" id="create_medicalSpecialtyId" name="medicalSpecialtyId" required>
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach($medicalSpecialty as $id => $name)
                                    <option value="{{ $id }}" {{ old('medicalSpecialtyId') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Security Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti tabler-lock me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.create.security') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="create_password">
                                {{ trans('adminmanagement::admin_management.user.form.password') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                <input type="password" class="form-control" id="create_password" name="password"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.password_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="create_password_confirmation">
                                {{ trans('adminmanagement::admin_management.user.form.password_confirmation') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-lock-check"></i></span>
                                <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.password_confirmation_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="create_active" name="is_active" value="on">
                                <label class="form-check-label" for="create_active">
                                    {{ trans('adminmanagement::admin_management.user.form.isActive') }}
                                    <small class="text-muted d-block">{{ trans('adminmanagement::admin_management.user.form.isActive_hint') }}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="ti tabler-x me-1"></i>
                    {{ trans('adminmanagement::admin_management.close') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i>
                    {{ trans('adminmanagement::admin_management.user.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
