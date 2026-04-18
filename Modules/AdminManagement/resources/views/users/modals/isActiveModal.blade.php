<div class="modal fade" id="isActiveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="editStatusUser"
              action="{{ route('admin.user_management.status') }}"
              method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" class="id" name="id" id="status_id">

            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-label-warning rounded-circle me-3">
                        <i class="ti tabler-user-cog fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">
                            {{ trans('adminmanagement::admin_management.user.status.title') }}
                        </h5>
                        <small class="text-muted">{{ trans('adminmanagement::admin_management.user.status.subtitle') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- User Info Card -->
                <div class="d-flex align-items-center p-3 bg-light rounded mb-4">
                    <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            <i class="ti tabler-user fs-4"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0" id="status_display_name"></h6>
                        <small class="text-muted" id="status_display_email"></small>
                    </div>
                </div>

                <!-- Status Toggle -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        {{ trans('adminmanagement::admin_management.user.status.current_status') }}
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-success">
                            <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1">
                            <label class="form-check-label" for="status_active">
                                <span class="badge bg-success me-1"><i class="ti tabler-check me-1"></i>{{ trans('adminmanagement::admin_management.user.status.active') }}</span>
                                <small class="text-muted d-block mt-1">{{ trans('adminmanagement::admin_management.user.status.active_desc') }}</small>
                            </label>
                        </div>
                        <div class="form-check form-check-warning">
                            <input class="form-check-input" type="radio" name="is_active" id="status_inactive" value="0">
                            <label class="form-check-label" for="status_inactive">
                                <span class="badge bg-warning me-1"><i class="ti tabler-clock me-1"></i>{{ trans('adminmanagement::admin_management.user.status.inactive') }}</span>
                                <small class="text-muted d-block mt-1">{{ trans('adminmanagement::admin_management.user.status.inactive_desc') }}</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-0" id="pending_approval_alert" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="ti tabler-alert-triangle me-2 fs-4"></i>
                        <div>
                            <strong>{{ trans('adminmanagement::admin_management.user.status.pending_notice') }}</strong>
                            <p class="mb-0 small">{{ trans('adminmanagement::admin_management.user.status.pending_notice_desc') }}</p>
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
                    <i class="ti tabler-check me-1"></i>
                    {{ trans('adminmanagement::admin_management.user.status.update') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status modal button click handler
        document.querySelectorAll('.IsActiveModalBTN').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const dataId = this.dataset.id;
                const name = this.dataset.name;
                const email = this.dataset.email;
                const active = this.dataset.active;

                // Populate form fields
                document.getElementById('status_id').value = dataId;
                document.getElementById('status_display_name').textContent = name;
                document.getElementById('status_display_email').textContent = email;

                // Set radio buttons
                if (active == 1) {
                    document.getElementById('status_active').checked = true;
                    document.getElementById('pending_approval_alert').style.display = 'none';
                } else {
                    document.getElementById('status_inactive').checked = true;
                    document.getElementById('pending_approval_alert').style.display = 'block';
                }
            });
        });

        // Show/hide pending alert based on selection
        document.querySelectorAll('input[name="is_active"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const alert = document.getElementById('pending_approval_alert');
                if (this.value === '0') {
                    alert.style.display = 'block';
                } else {
                    alert.style.display = 'none';
                }
            });
        });
    });
</script>
