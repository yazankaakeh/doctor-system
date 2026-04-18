@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('payment::payment.all_payments'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ trans('payment::payment.all_payments') }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Filter Section --}}
                            <form method="GET" action="{{ route('doctor.payment.index') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ trans('payment::payment.status') }}</label>
                                        <select name="status" class="form-select">
                                            <option value="">{{ trans('payment::payment.all_statuses') }}</option>
                                            @foreach(\Modules\Payment\Enums\PaymentStatusEnum::cases() as $status)
                                                <option value="{{ $status->value }}"
                                                        {{ (request('status') == $status->value) ? 'selected' : '' }}>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">{{ trans('payment::payment.payment_method') }}</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="">{{ trans('payment::payment.all_methods') }}</option>
                                            @foreach(\Modules\Payment\Enums\PaymentMethodEnum::cases() as $method)
                                                <option value="{{ $method->value }}"
                                                        {{ (request('payment_method') == $method->value) ? 'selected' : '' }}>
                                                    {{ $method->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">{{ trans('payment::payment.date_from') }}</label>
                                        <input type="date" name="date_from" class="form-control"
                                               value="{{ request('date_from') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">{{ trans('payment::payment.date_to') }}</label>
                                        <input type="date" name="date_to" class="form-control"
                                               value="{{ request('date_to') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="ti tabler-filter me-1"></i>
                                            {{ trans('payment::payment.apply_filters') }}
                                        </button>
                                    </div>
                                </div>

                                @if(!empty($filters))
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <a href="{{ route('doctor.payment.index') }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti tabler-x me-1"></i>
                                                {{ trans('payment::payment.reset_filters') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </form>

                            {{-- Payments Table --}}
                            @if($payments->isEmpty())
                                <div class="alert alert-info">
                                    <i class="ti tabler-info-circle me-2"></i>
                                    {{ trans('payment::payment.no_payments') }}
                                </div>
                            @else
                                <div class="table-responsive text-nowrap">
                                    <table class="table datanew">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('payment::payment.patient_name') }}</th>
                                            <th>{{ trans('payment::payment.amount') }}</th>
                                            <th>{{ trans('payment::payment.payment_method') }}</th>
                                            <th>{{ trans('payment::payment.status') }}</th>
                                            <th>{{ trans('payment::payment.booking_date') }}</th>
                                            <th>{{ trans('payment::payment.created_at') }}</th>
                                            <th>{{ trans('admin.audits.action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($payments as $payment)
                                            <tr>
                                                <td>{{ $payment->patient->name }}</td>
                                                <td>
                                                    <strong>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-info">
                                                        <i class="ti tabler-{{ $payment->payment_method->icon() }} me-1"></i>
                                                        {{ $payment->payment_method->label() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $payment->status->class() }}">
                                                        <i class="ti tabler-{{ $payment->status->icon() }} me-1"></i>
                                                        {{ $payment->status->label() }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->booking->booking_date->format('Y-m-d H:i') }}</td>
                                                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        {{-- View Details --}}
                                                        <a href="{{ route('doctor.payment.show', $payment) }}"
                                                           class="btn btn-sm btn-outline-info"
                                                           title="{{ trans('admin.show') }}">
                                                            <i class="ti tabler-eye"></i>
                                                        </a>

                                                        {{-- Verify/Reject buttons for awaiting verification --}}
                                                        @if($payment->isAwaitingVerification())
                                                            <form action="{{ route('doctor.payment.verify', $payment) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm"
                                                                        title="{{ trans('payment::payment.verify_payment') }}"
                                                                        onclick="return confirm('{{ trans('payment::payment.verify_confirm') }}')">
                                                                    <i class="ti tabler-check"></i>
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('doctor.payment.reject', $payment) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                        title="{{ trans('payment::payment.reject_payment') }}"
                                                                        onclick="return confirm('{{ trans('payment::payment.reject_confirm') }}')">
                                                                    <i class="ti tabler-x"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="mt-3">
                                        {{ $payments->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
