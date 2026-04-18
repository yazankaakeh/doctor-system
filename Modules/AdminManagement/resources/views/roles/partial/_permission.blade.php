@foreach($permissions as $index=> $permission)
    <div class="col-md-4 col-lg-4 col-sm-12 mb-3">
        <div class="card permission-card mb-0 h-100 {{ $index }}">
            <div class="card-header section-header d-flex justify-content-between align-items-center pb-2">
                <h6 class="mb-0 text-white">
                    <i class="icon-base ti tabler-folder me-2"></i>
                    {{ trans('adminmanagement::admin_management.sections.'.$index)}}
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-sm permission-count" style="background-color: rgba(255, 255, 255, 0.2); color: white;">
                        {{ count($permission) }}
                    </span>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input"
                               id="{{$index}}" onclick="checkAll('{{$index}}',this)">
                    </div>
                </div>
            </div>
            <div class="card-body pt-2">
                @foreach($permission as $perm)
                    <div class="permission-item d-flex justify-content-between align-items-center">
                        <label for="{{$perm->name}}" class="form-check-label mb-0" style="cursor: pointer;">
                            <i class="icon-base ti tabler-shield-check text-primary me-2"></i>
                            <span>{{ trans('adminmanagement::admin_management.permissions.'.str_replace('.', '-', $perm->name))}}</span>
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" onclick="checkBoxChild('{{$index}}', this)"
                                   class="form-check-input"
                                   name="permissions[{{$perm->name}}]"
                                   @if(isset($userPermissions))
                                       @if(in_array($perm->name,$userPermissions))
                                           checked
                                   @endif
                                   @endif
                                   @if(isset($oldPermissions))
                                       {{in_array($perm->name, $oldPermissions)  ? 'checked' : ''}}
                                   @endif
                                   id="{{$perm->name}}"
                                   value="{{$perm->name}}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach

@section('vendor-script')
    <script>
        function checkAll(checkBoxClass, el) {
            $('.' + checkBoxClass + ' input[name^="permissions"]').each(function () {
                let isChecked = $(el).is(':checked');
                $(this).prop('checked', isChecked);
            });
            updateSelectedCount();
        }

        function checkBoxChild(checkBoxClass) {
            let isChecked = true;
            $('.' + checkBoxClass + ' input[name^="permissions"]').each(function () {
                if (!$(this).is(':checked'))
                    isChecked = false;
            });

            $('#' + checkBoxClass + '').prop('checked', isChecked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = $('input[name^="permissions"]:checked').length;
            $('#count-selected').text(count);
        }

        $(document).ready(function () {
            @foreach($permissions as $index => $permission)
            checkBoxChild('{{$index}}');
            @endforeach

            $('#allCheckBoxes').click(function () {
                let allCheckBoxes = $('input[name^="permissions"]');
                let isChecked = $(this).is(':checked');
                allCheckBoxes.prop('checked', isChecked);

                // Also update section checkboxes
                @foreach($permissions as $index => $permission)
                $('#{{$index}}').prop('checked', isChecked);
                @endforeach

                updateSelectedCount();
            });

            // Update count on initial load
            updateSelectedCount();
        });
    </script>
@endsection
