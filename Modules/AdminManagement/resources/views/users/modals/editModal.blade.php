@php use Modules\Core\App\Enums\Gender; @endphp
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" enctype="multipart/form-data" id="editUser"
              action="{{ route('admin.user_management.update') }}"
              method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editeId">
            <div class="modal-header bg-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-white rounded-circle me-3 overflow-hidden" id="edit_avatar_preview">
                        <img src="{{ asset('assets/img/avatars/default.png') }}" id="edit_img_preview" alt="Avatar"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0">
                            {{ trans('adminmanagement::admin_management.user.edit.title') }}
                        </h5>
                        <small class="text-white-50" id="edit_user_email_display"></small>
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
                            <label class="form-label" for="edit_name">
                                {{ trans('adminmanagement::admin_management.user.form.name') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-user"></i></span>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.name_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_email">
                                {{ trans('adminmanagement::admin_management.user.form.email') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                                <input type="email" class="form-control" id="edit_email" name="email"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.email_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_age">
                                {{ trans('doctor::doctor.patients.age') }}
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                                <input type="number" class="form-control" id="edit_age" name="age"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.age_placeholder') }}"
                                       min="18" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_gender">
                                {{ trans('doctor::doctor.patients.gender') }}
                            </label>
                            <select class="form-select" id="edit_gender" name="gender">
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach(Gender::getAllEnumValuesKeysLabel() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
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
                            <label class="form-label" for="edit_role">
                                {{ trans('adminmanagement::admin_management.user.form.role') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" id="edit_role" name="role" required>
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach($roles as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_medicalSpecialtyId">
                                {{ trans('doctor::doctor.doctor.medicalSpecialtyId') }}
                            </label>
                            <select class="form-select select2" id="edit_medicalSpecialtyId" name="medicalSpecialtyId">
                                <option value="">{{ trans('adminmanagement::admin_management.pleaseSelectOne') }}</option>
                                @foreach($medicalSpecialty as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Profile Image Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti tabler-photo me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.edit.profile_image') }}
                    </h6>
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-xl rounded-circle overflow-hidden border" style="width: 80px; height: 80px;">
                                <img src="{{ asset('assets/img/avatars/default.png') }}" id="edit_img_large_preview"
                                     alt="Avatar" class="w-100 h-100 object-fit-cover">
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label" for="edit_img">
                                {{ trans('adminmanagement::admin_management.user.form.img') }}
                            </label>
                            <input type="file" class="form-control" id="edit_img" name="img" accept="image/*">
                            <small class="text-muted">{{ trans('adminmanagement::admin_management.user.form.img_hint') }}</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Security Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti tabler-lock me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.edit.change_password') }}
                    </h6>
                    <div class="alert alert-info mb-3">
                        <i class="ti tabler-info-circle me-2"></i>
                        {{ trans('adminmanagement::admin_management.user.edit.password_hint') }}
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_password">
                                {{ trans('adminmanagement::admin_management.user.form.password') }}
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                <input type="password" class="form-control" id="edit_password" name="password"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.password_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_password_confirmation">
                                {{ trans('adminmanagement::admin_management.user.form.password_confirmation') }}
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-lock-check"></i></span>
                                <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.form.password_confirmation_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" value="on">
                                <label class="form-check-label" for="editIsActive">
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
                    {{ trans('adminmanagement::admin_management.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Edit modal button click handler
        document.querySelectorAll('.EditModalBTN').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const dataId = this.dataset.id;
                const name = this.dataset.name;
                const email = this.dataset.email;
                const img = this.dataset.img;
                const active = this.dataset.active;
                const role = this.dataset.role;

                // Populate form fields
                document.getElementById('editeId').value = dataId;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_user_email_display').textContent = email;
                document.getElementById('editIsActive').checked = active == 1;

                // Update avatar previews
                const defaultImg = '{{ asset('assets/img/avatars/default.png') }}';
                const imgSrc = img || defaultImg;
                document.getElementById('edit_img_preview').src = imgSrc;
                document.getElementById('edit_img_large_preview').src = imgSrc;

                // Set role select
                const roleSelect = document.getElementById('edit_role');
                if (roleSelect) {
                    roleSelect.value = role || '';
                    // Trigger change for Select2
                    if (typeof $ !== 'undefined' && $(roleSelect).hasClass('select2-hidden-accessible')) {
                        $(roleSelect).trigger('change');
                    }
                }
            });
        });

        // Image preview on file select
        document.getElementById('edit_img')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_img_preview').src = e.target.result;
                    document.getElementById('edit_img_large_preview').src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>
