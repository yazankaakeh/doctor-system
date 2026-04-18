@php
    use Modules\AdminManagement\Action\Auditing\RouteName;
    use Modules\AdminManagement\Models\AuditLog;

    // AuditLog::GetDoctors() doesn't exist on the model. Use GetAuditableModels()
    // which already returns Doctor::query()->select('name','id')->get().
    $doctors     = AuditLog::GetAuditableModels();
    $startDate   = app('request')->input('start_date');
    $endDate     = app('request')->input('end_date');
    $route_names = RouteName::Routes();
@endphp

        <!-- Main modal -->
<div id="filterModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- BEGIN: Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLabel1">{{trans('adminmanagement::admin_management.audits.filterModal.index')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- END: Modal Header -->
            <!-- BEGIN: Modal Body -->
            <div class="modal-body">
                <div class="col-md-12 col-12 mb-4">
                    <div class="col-md-12 mb-4">
                        <x-core::select
                            :label="trans('adminmanagement::admin_management.audits.filterModal.adminId')"
                            :placeholder="trans('adminmanagement::admin_management.pleaseSelectOne')"
                            id="doctorId"
                            name="doctorId"
                            :options="$doctors->pluck('name', 'id')->prepend(trans('adminmanagement::admin_management.pleaseSelectOne'), 'all')"
                            :value="app('request')->input('doctorId')">
                        </x-core::select>
                    </div>
                </div>
                <div class="col-md-12 col-12 mb-4">
                    <x-core::input
                        label="adminmanagement::admin_management.audits.filterModal.date"
                        id="bs-rangepicker-basic"
                        name="date_range"
                        type="text"
                        class="date-range"
                        value="">
                    </x-core::input>
                </div>

                <div class="col-md-12 col-12 mb-4">
                    <x-core::select
                        :label="trans('adminmanagement::admin_management.audits.filterModal.routeName')"
                        :placeholder="trans('adminmanagement::admin_management.pleaseSelectOne')"
                        id="routeName"
                        name="routeName"
                        :options="collect($route_names)->prepend(trans('adminmanagement::admin_management.pleaseSelectOne'), 'all')"
                        :value="app('request')->input('route_name')">
                    </x-core::select>
                </div>
            </div>
            <!-- END: Modal Body -->
            <!-- BEGIN: Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary"
                        data-bs-dismiss="modal">{{trans('adminmanagement::admin_management.close')}}</button>
                <button type="submit" id="filter"
                        class="btn btn-primary">{{trans('adminmanagement::admin_management.submit')}}</button>
            </div>
            <!-- END: Modal Footer -->
        </div>
    </div>
</div>
<!-- END: Modal Content -->
