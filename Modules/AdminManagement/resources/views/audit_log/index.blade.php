@php use Modules\AdminManagement\Action\Auditing\RouteName; @endphp
@extends('theme::user.layouts.horizontalLayout')

{{--@section('title', 'Audit Log')

@section('vendor-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/typeahead-js/typeahead.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>
    <link rel="stylesheet"
          href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}"/>
@endsection

@section('vendor-script')
    <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/bloodhound/bloodhound.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/pickr/pickr.js')}}"></script>




@endsection

@section('page-script')
    <script src="{{asset('assets/js/cards-actions.js')}}"></script>
    <script src="{{asset('assets/js/forms-selects.js')}}"></script>
    <script src="{{asset('assets/js/forms-typeahead.js')}}"></script>
    <script src="{{asset('assets/js/forms-pickers.js')}}"></script>
@endsection--}}
@section('title', trans('adminmanagement::admin_management.audits.index'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5 class="">{{trans('adminmanagement::admin_management.audits.index')}}</h5>
                            <h5 class="">
                                <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#filterModal"
                                        class="btn btn-primary">
                                    <i class="ti tabler-plus me-1"></i>
                                    {{trans('adminmanagement::admin_management.audits.filter')}}
                                </button>
                            </h5>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <div class="card-body">
                                <div class="card-content">
                                    <table id="table-draggable1" class="datanew table">
                                        <thead>
                                        <tr>
                                            <th> {{trans('adminmanagement::admin_management.audits.admin')}}</th>
                                            <th> {{trans('adminmanagement::admin_management.audits.action')}}</th>
                                            <th> {{trans('adminmanagement::admin_management.audits.ip')}}</th>
                                            <th> {{trans('adminmanagement::admin_management.audits.time')}}</th>
                                            <th> {{trans('adminmanagement::admin_management.audits.changes')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="sortable">
                                        @foreach ($data as $audit)
                                            <tr class="intro-x ">
                                                <td>
                                                    <span class="font-medium">
                                                        {{ $audit->auditable?->name ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="#" class="font-medium whitespace-nowrap">
                                                        {{RouteName::GetRouteName($audit->route_name) ?? $audit->route_name}}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="#" class="font-medium whitespace-nowrap">
                                                        {{$audit->ip}}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="#" class="font-medium whitespace-nowrap">
                                                        {{$audit->created_at->format('y:m:d')}}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a type="button"
                                                       data-bs-toggle="modal" data-bs-target="#payloadModal">
                                                        @if($audit->payload)
                                                            <i data-id='{{ $audit->id }}' type="button"

                                                               class="fa fa-fw fa-exclamation-circle payload"></i>
                                                        @endif
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @includeIf('adminmanagement::audit_log.modals.payloadModal')
    @includeIf('adminmanagement::audit_log.modals.filterModal')
@endsection
@section('vendor-script')
    <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
    <script>
        let modal = $('#filterModal');
        let body = $('body');
        $(document).ready(function () {
            $('.select2').select2({
                dropdownParent: modal
            });
            $('.date-range').daterangepicker({
                dropdownParent: modal,
                format: 'YYYY-MM-DD'

            });
            body.on({
                click: function () {
                    let adminId = $('#adminId').val();
                    let select2 = $('#routeName').val();
                    let fullDate = $('.date-range').val().split('-');
                    let start_date = '';
                    let end_date = '';
                    if (!fullDate.isEmpty) {
                        start_date = fullDate[0];
                        end_date = fullDate[1];
                    }
                    window.location.replace("{{url('/')}}/admin/audit-log?adminId=" + adminId +
                        '&start_date=' + start_date +
                        '&end_date=' + end_date +
                        '&route_name=' + select2
                    );
                }
            }, '#filter');
        });


        body.on({
            click: function () {
                $('.adminId').val(null).trigger('change');
                $('.select2').val(null).trigger('change');
                $('#date-range').val('');
            }
        }, '#reset');

        $(document).ready(function () {

            $('.payload').click(function () {

                let audit_id = $(this).data('id');

                // AJAX request
                $.ajax({
                    url: '{{route('admin.audits.getPayload')}}' + '/' + audit_id,
                    type: 'get',
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        // Add response in Modal body
                        $('.modal-content-payload').html(response.payload);

                        // Display Modal
                    }
                });
            });
        });
    </script>
@endsection
