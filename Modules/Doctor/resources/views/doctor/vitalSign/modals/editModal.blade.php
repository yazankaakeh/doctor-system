@php use Modules\Core\App\Enums\ActiveEnum; @endphp
<div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="row g-3" enctype="multipart/form-data" id="editUser"
              action="{{route('doctor.vitalSign.update')}}"
              method="POST">
            @csrf
            @method('POST')
            <input type="hidden" name="id" id="editeId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{trans('doctor::doctor.vitalSign.updateVitalSign')}}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <x-core::inputMultiLanguageComponent divClass="col-lg-12 col-sm-12 col-md-6 mb-3"
                                                             label="doctor::doctor.vitalSign.name"
                                                             name="name"
                                                             type="text" id="name"/>
                        <div class="col-4 mb-3">
                            <x-core::input
                                    :label="trans('doctor::doctor.vitalSign.min_value')"
                                    :placeholder="trans('doctor::doctor.vitalSign.min_value')"
                                    id="edit_min_value"
                                    name="min_value"
                                    type="number"
                                    step="0.01"
                                    value="{{old('min_value')}}"/>
                        </div>
                        <div class="col-4 mb-3">
                            <x-core::input
                                    :label="trans('doctor::doctor.vitalSign.max_value')"
                                    :placeholder="trans('doctor::doctor.vitalSign.max_value')"
                                    id="edit_max_value"
                                    name="max_value"
                                    type="number"
                                    step="0.01"
                                    value="{{old('max_value')}}"/>
                        </div>
                        <div class="col-4 mb-3">
                            <x-core::input
                                    :label="trans('doctor::doctor.vitalSign.unit')"
                                    :placeholder="trans('doctor::doctor.vitalSign.unit_placeholder')"
                                    id="edit_unit"
                                    name="unit"
                                    type="text"
                                    value="{{old('unit')}}"/>
                        </div>
                        <div class="col-6 mb-3">
                            <x-core::select :label="trans('doctor::doctor.patients.active')"
                                            :placeholder="trans('doctor::doctor.patients.active')"
                                            id="is_active"
                                            name="is_active"
                                            required="required"
                                            model="is_active"
                                            :options="ActiveEnum::getAllEnumValuesKeysLabel()"
                                            value="{{old('active')}}">

                            </x-core::select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{trans('doctor::doctor.close')}}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{trans('doctor::doctor.save')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.EditModalBTN').on('click', function () {
            let dataId = $(this).data('id');
            let active = $(this).data('active');
            let minValue = $(this).data('min-value');
            let maxValue = $(this).data('max-value');
            let unit = $(this).data('unit');
            let nameTranslations;
            try {
                nameTranslations = JSON.parse($(this).attr('data-name') || '{}');
            } catch {
                nameTranslations = {};
            }
            const modal = $('#editModal');
            Object.entries(nameTranslations).forEach(([locale, value]) => {
                modal.find(`[name="name[${locale}]"]`).val(value ?? '');
            });
            $('#editModal #editeId').val(dataId);
            $('#editModal #is_active').val(active);
            $('#editModal #edit_min_value').val(minValue ?? '');
            $('#editModal #edit_max_value').val(maxValue ?? '');
            $('#editModal #edit_unit').val(unit ?? '');
        });
    });
</script>
