{{-- Create-Medical-Examination modal: pick a patient, then POST to store to create/update and redirect to the create view --}}
<div class="modal modal-md fade" id="storeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        {{-- The URL must be built client-side from the selected patient id, so we intercept the submit. --}}
        <form id="createMedicalExaminationForm" method="POST" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storeModalLabel">
                        {{ trans('doctor::doctor.medicalExaminations.createTitle') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ trans('doctor::doctor.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <x-core::select :label="trans('doctor::doctor.patients.name')"
                                        :placeholder="trans('doctor::doctor.medicalExaminations.selectPatient')"
                                        id="store_patient_id"
                                        name="patient_id"
                                        model="store_patient_id"
                                        :options="$patients"
                                        required="required"
                                        value="">
                        </x-core::select>
                        <small class="text-muted d-block mt-2">
                            <i class="ti tabler-info-circle me-1"></i>
                            {{ trans('doctor::doctor.medicalExaminations.createHint') }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ trans('doctor::doctor.close') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createMedicalExaminationSubmit">
                        <i class="ti tabler-plus me-1"></i>
                        {{ trans('doctor::doctor.medicalExaminations.createAndContinue') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const storeUrlTemplate = @json(route('doctor.medicalExamination.store', ['patientId' => '__PATIENT_ID__']));
            const form = document.getElementById('createMedicalExaminationForm');
            const submit = document.getElementById('createMedicalExaminationSubmit');

            if (!form || !submit) {
                return;
            }

            form.addEventListener('submit', function (e) {
                const select = document.getElementById('store_patient_id');
                const patientId = select ? select.value : '';

                if (!patientId) {
                    e.preventDefault();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(trans('doctor::doctor.medicalExaminations.selectPatient')),
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    } else {
                        alert(@json(trans('doctor::doctor.medicalExaminations.selectPatient')));
                    }
                    return;
                }

                form.action = storeUrlTemplate.replace('__PATIENT_ID__', encodeURIComponent(patientId));
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    @json(trans('doctor::doctor.medicalExaminations.createAndContinue'));
            });
        })();
    </script>
@endpush
