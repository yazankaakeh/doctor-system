{{-- Filter modal for medical examinations: GET form preserves filters in the query string --}}
<div class="modal modal-lg fade" id="filterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="row g-3" action="{{ route('doctor.medicalExamination.index') }}" method="GET">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">
                        {{ trans('doctor::doctor.medicalExaminations.filterTitle') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ trans('doctor::doctor.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-core::select :label="trans('doctor::doctor.patients.name')"
                                            :placeholder="trans('doctor::doctor.medicalExaminations.selectPatient')"
                                            id="filter_patient_id"
                                            name="patient_id"
                                            model="filter_patient_id"
                                            :options="$patients"
                                            :value="$filters['patient_id'] ?? ''">
                            </x-core::select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-core::select :label="trans('customer.account.status')"
                                            :placeholder="trans('customer.account.status')"
                                            id="filter_status"
                                            name="status"
                                            model="filter_status"
                                            :options="$statuses"
                                            :value="$filters['status'] ?? ''">
                            </x-core::select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-core::select :label="trans('customer.sidebar.clinic')"
                                            :placeholder="trans('customer.sidebar.clinic')"
                                            id="filter_clinic_id"
                                            name="clinic_id"
                                            model="filter_clinic_id"
                                            :options="$clinics"
                                            :value="$filters['clinic_id'] ?? ''">
                            </x-core::select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="filter_from" class="form-label">
                                {{ trans('doctor::doctor.medicalExaminations.from') }}
                            </label>
                            <input type="date"
                                   id="filter_from"
                                   name="from"
                                   class="form-control"
                                   value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="filter_to" class="form-label">
                                {{ trans('doctor::doctor.medicalExaminations.to') }}
                            </label>
                            <input type="date"
                                   id="filter_to"
                                   name="to"
                                   class="form-control"
                                   value="{{ $filters['to'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('doctor.medicalExamination.index') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-refresh me-1"></i>
                        {{ trans('doctor::doctor.medicalExaminations.clearFilters') }}
                    </a>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ trans('doctor::doctor.close') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-filter me-1"></i>
                        {{ trans('doctor::doctor.submit') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
