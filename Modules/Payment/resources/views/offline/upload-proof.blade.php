@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('payment::payment.upload_proof'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ trans('payment::payment.offline_payment') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <h6 class="alert-heading">{{ trans('payment::payment.upload_instructions') }}</h6>
                                <p class="mb-0">{{ trans('payment::payment.max_files') }}</p>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>{{ trans('booking::booking.booking_details') }}</h6>
                                    <p>
                                        <strong>{{ trans('booking::booking.doctor') }}:</strong>
                                        Dr. {{ $booking->doctor->name }}
                                    </p>
                                    <p>
                                        <strong>{{ trans('booking::booking.booking_date') }}:</strong>
                                        {{ $booking->booking_date->format('Y-m-d') }}
                                        {{ $booking->start_time->format('H:i') }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>{{ trans('payment::payment.amount') }}</h6>
                                    <h3 class="text-primary">${{ number_format($booking->consultation_fee, 2) }}</h3>
                                </div>
                            </div>

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('payment.offline.submit', $booking) }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label">{{ trans('payment::payment.select_files') }}</label>
                                    <input type="file" name="proofs[]" class="form-control @error('proofs') is-invalid @enderror @error('proofs.*') is-invalid @enderror" multiple
                                           accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">{{ trans('payment::payment.max_files') }}</div>
                                    @error('proofs')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('proofs.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('patient.bookings.show', $booking) }}"
                                       class="btn btn-outline-secondary">
                                        {{ trans('booking::booking.back') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        {{ trans('payment::payment.submit_proof') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
