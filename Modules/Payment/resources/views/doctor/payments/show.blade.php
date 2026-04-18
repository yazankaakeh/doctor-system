@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('payment::payment.payment_details'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-2">{{ trans('payment::payment.payment_details') }}</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('doctor.payment.index') }}">{{ trans('payment::payment.payments') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active">{{ trans('payment::payment.payment_details') }}</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="{{ route('doctor.payment.index') }}" class="btn btn-outline-secondary">
                            <i class="ti tabler-arrow-left me-1"></i>{{ trans('core::core.back') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Payment Information -->
                <div class="col-lg-8">
                    <!-- Payment Status Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ti tabler-info-circle me-2"></i>{{ trans('payment::payment.payment_information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.status') }}</label>
                                    <div>
                                        <span class="badge bg-{{ $payment->status->class() }} fs-6">
                                            <i class="ti tabler-{{ $payment->status->icon() }} me-1"></i>
                                            {{ $payment->status->label() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.payment_method') }}</label>
                                    <div>
                                        <span class="badge bg-label-info fs-6">
                                            <i class="ti tabler-{{ $payment->payment_method->icon() }} me-1"></i>
                                            {{ $payment->payment_method->label() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.amount') }}</label>
                                    <div class="fs-4 fw-bold text-primary">
                                        {{ $payment->currency }} {{ number_format($payment->amount, 2) }}
                                    </div>
                                </div>

                                @if($payment->transaction_id)
                                    <div class="col-md-6">
                                        <label class="text-muted small">{{ trans('payment::payment.transaction_id') }}</label>
                                        <div class="fw-medium">{{ $payment->transaction_id }}</div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.created_at') }}</label>
                                    <div class="fw-medium">{{ $payment->created_at->format('Y-m-d H:i:s') }}</div>
                                </div>

                                @if($payment->paid_at)
                                    <div class="col-md-6">
                                        <label class="text-muted small">{{ trans('payment::payment.paid_at') }}</label>
                                        <div class="fw-medium">{{ $payment->paid_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                @endif

                                @if($payment->verified_at)
                                    <div class="col-md-6">
                                        <label class="text-muted small">{{ trans('payment::payment.verified_at') }}</label>
                                        <div class="fw-medium">{{ $payment->verified_at->format('Y-m-d H:i:s') }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">{{ trans('payment::payment.verified_by') }}</label>
                                        <div class="fw-medium">
                                            @if($payment->verifiedBy)
                                                {{ $payment->verifiedBy->name }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($payment->admin_notes)
                                    <div class="col-12">
                                        <label class="text-muted small">{{ trans('payment::payment.admin_notes') }}</label>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti tabler-note me-2"></i>{{ $payment->admin_notes }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ti tabler-user me-2"></i>{{ trans('payment::payment.patient_information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.patient_name') }}</label>
                                    <div class="fw-medium">{{ $payment->patient->name }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('admin_management.email') }}</label>
                                    <div class="fw-medium">{{ $payment->patient->email }}</div>
                                </div>

                                @if($payment->patient->phone)
                                    <div class="col-md-6">
                                        <label class="text-muted small">{{ trans('admin_management.phone') }}</label>
                                        <div class="fw-medium">{{ $payment->patient->phone }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Booking Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ti tabler-calendar-event me-2"></i>{{ trans('payment::payment.booking_information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('payment::payment.booking_date') }}</label>
                                    <div class="fw-medium">{{ $payment->booking->booking_date->format('Y-m-d H:i') }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('booking::booking.status') }}</label>
                                    <div>
                                        <span class="badge bg-{{ $payment->booking->status->class() }}">
                                            {{ $payment->booking->status->label() }}
                                        </span>
                                    </div>
                                </div>

                                @if($payment->booking->notes)
                                    <div class="col-12">
                                        <label class="text-muted small">{{ trans('booking::booking.notes') }}</label>
                                        <div class="fw-medium">{{ $payment->booking->notes }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proofs -->
                    @if($payment->getMedia('payment_proofs')->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="ti tabler-file-upload me-2"></i>{{ trans('payment::payment.payment_proofs') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($payment->getMedia('payment_proofs') as $proof)
                                        <div class="col-md-6">
                                            <div class="border rounded p-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <i class="ti tabler-file text-primary fs-2 me-2"></i>
                                                        <span class="fw-medium">{{ $proof->file_name }}</span>
                                                    </div>
                                                    <a href="{{ $payment->getSecureMediaUrl($proof) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="ti tabler-eye me-1"></i>{{ trans('payment::payment.view_proof') }}
                                                    </a>
                                                </div>
                                                <div class="text-muted small mt-2">
                                                    {{ trans('payment::payment.uploaded') }}: {{ $proof->created_at->format('Y-m-d H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column - Actions -->
                <div class="col-lg-4">
                    <!-- Action Buttons -->
                    @if($payment->isAwaitingVerification())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">{{ trans('admin.audits.action') }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    {{ trans('payment::payment.verify_instructions') }}
                                </p>

                                <form action="{{ route('doctor.payment.verify', $payment) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">{{ trans('payment::payment.admin_notes') }}</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ trans('payment::payment.notes_placeholder') }}"></textarea>
                                        <small class="text-muted">Optional</small>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100"
                                            onclick="return confirm('{{ trans('payment::payment.verify_confirm') }}')">
                                        <i class="ti tabler-check me-1"></i>{{ trans('payment::payment.verify_payment') }}
                                    </button>
                                </form>

                                <form action="{{ route('doctor.payment.reject', $payment) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">{{ trans('payment::payment.rejection_reason') }}</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ trans('payment::payment.rejection_reason_placeholder') }}" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100"
                                            onclick="return confirm('{{ trans('payment::payment.reject_confirm') }}')">
                                        <i class="ti tabler-x me-1"></i>{{ trans('payment::payment.reject_payment') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Summary Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('payment::payment.summary') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ trans('payment::payment.payment_id') }}:</span>
                                <span class="fw-bold">#{{ $payment->id }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ trans('payment::payment.booking_id') }}:</span>
                                <span class="fw-bold">#{{ $payment->booking_id }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">{{ trans('payment::payment.total') }}:</span>
                                <span class="fw-bold text-primary fs-5">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
