@extends('theme::user.layouts.contentNavbarLayout')

@section('title', trans('payment::payment.pending_verifications'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ trans('payment::payment.pending_verifications') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table datanew">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('booking::booking.patient') }}</th>
                                        <th>{{ trans('booking::booking.doctor') }}</th>
                                        <th>{{ trans('payment::payment.amount') }}</th>
                                        <th>{{ trans('payment::payment.payment_proofs') }}</th>
                                        <th>{{ trans('booking::booking.booking_date') }}</th>
                                        <th>{{ trans('admin.audits.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->patient->name }}</td>
                                            <td>Dr. {{ $payment->booking->doctor->name }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                @foreach($payment->getMedia('payment_proofs') as $proof)
                                                    <a href="{{ $payment->getSecureMediaUrl($proof) }}" target="_blank"
                                                       class="btn btn-sm btn-outline-primary mb-1">
                                                        <i class="ti tabler-file me-1"></i>
                                                        {{ $proof->file_name }}
                                                    </a>
                                                @endforeach
                                            </td>
                                            <td>{{ $payment->booking->booking_date->format('Y-m-d') }}</td>
                                            <td>
                                                <form action="{{ route('admin.payment.verify', $payment) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm"
                                                            onclick="return confirm('Verify this payment?')">
                                                        <i class="ti tabler-check"></i>
                                                        {{ trans('payment::payment.verify_payment') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.payment.reject', $payment) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Reject this payment?')">
                                                        <i class="ti tabler-x"></i>
                                                        {{ trans('payment::payment.reject_payment') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{ $payments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
