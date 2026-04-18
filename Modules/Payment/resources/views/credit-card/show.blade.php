@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('payment::payment.credit_card.title'))

@section('vendor-style')
    <style>
        /* ---------- Credit-card prototype ---------- */
        .cc-page .cc-wrap { max-width: 960px; margin: 0 auto; }

        .cc-page .cc-card {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            overflow: hidden;
        }
        .cc-page .cc-card__header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex; align-items: center; justify-content: space-between;
        }
        .cc-page .cc-card__body { padding: 1.5rem; }

        /* Card preview */
        .cc-preview {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #23265c 100%);
            color: #fff;
            border-radius: 14px;
            padding: 1.5rem;
            min-height: 200px;
            display: flex; flex-direction: column; justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
            font-family: 'Courier New', monospace;
            position: relative;
            overflow: hidden;
        }
        .cc-preview::before {
            content: ''; position: absolute; right: -30px; top: -30px;
            width: 160px; height: 160px; border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .cc-preview__chip {
            width: 42px; height: 32px; border-radius: 5px;
            background: linear-gradient(135deg, #e8d27a, #bf9a4e);
        }
        .cc-preview__brand { font-family: inherit; font-size: .85rem; text-transform: uppercase; letter-spacing: .1em; opacity: .85; }
        .cc-preview__number {
            font-size: 1.4rem;
            letter-spacing: .16em;
            margin: 1rem 0;
            word-spacing: .35em;
        }
        .cc-preview__row { display: flex; justify-content: space-between; font-size: .8rem; opacity: .85; }
        .cc-preview__label { display: block; font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; opacity: .65; margin-bottom: 3px; }

        .cc-amount {
            display: flex; justify-content: space-between; align-items: center;
            padding: .85rem 1rem;
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius, 0.375rem);
            background: var(--bs-tertiary-bg);
            margin-bottom: 1.25rem;
        }
        .cc-amount__label { font-size: .8rem; color: var(--bs-secondary-color); }
        .cc-amount__value { font-size: 1.5rem; font-weight: 600; color: var(--bs-primary); }

        .cc-hint {
            background: rgba(var(--bs-warning-rgb), .08);
            border: 1px dashed rgba(var(--bs-warning-rgb), .4);
            border-radius: var(--bs-border-radius, 0.375rem);
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--bs-body-color);
        }
        .cc-hint code {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 4px;
            padding: 1px 6px;
            font-size: .8rem;
        }

        .cc-brand-row {
            display: flex; align-items: center; gap: .35rem;
            font-size: .78rem; color: var(--bs-secondary-color);
        }

        .cc-page .form-label { font-size: .85rem; font-weight: 500; }
        .cc-page .form-control { font-family: 'Courier New', monospace; letter-spacing: .04em; }

        @media (max-width: 767.98px) {
            .cc-preview__number { font-size: 1.15rem; letter-spacing: .12em; }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper cc-page">
        <div class="content">
            <div class="cc-wrap">

                {{-- Back link --}}
                <div class="mb-3">
                    <a href="{{ route('patient.bookings.show', $booking) }}" class="small text-muted text-decoration-none">
                        <i class="ti tabler-arrow-left me-1"></i>
                        {{ trans('booking::booking.back') }}
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti tabler-alert-circle me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <div class="cc-card">
                    <div class="cc-card__header">
                        <div>
                            <h5 class="mb-0">
                                <i class="ti tabler-credit-card text-primary me-1"></i>
                                {{ trans('payment::payment.credit_card.title') }}
                            </h5>
                            <small class="text-muted">
                                {{ trans('payment::payment.credit_card.subtitle') }}
                            </small>
                        </div>
                        <span class="badge bg-label-warning">
                            <i class="ti tabler-test-pipe me-1"></i>
                            {{ trans('payment::payment.credit_card.sandbox_badge') }}
                        </span>
                    </div>

                    <div class="cc-card__body">
                        <div class="row g-4">

                            {{-- Left column: interactive card preview + amount --}}
                            <div class="col-lg-5">
                                <div class="cc-preview mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="cc-preview__chip"></div>
                                        <div class="cc-preview__brand" id="ccPreviewBrand">
                                            {{ trans('payment::payment.credit_card.card') }}
                                        </div>
                                    </div>

                                    <div class="cc-preview__number" id="ccPreviewNumber">
                                        •••• •••• •••• ••••
                                    </div>

                                    <div class="cc-preview__row">
                                        <div>
                                            <span class="cc-preview__label">{{ trans('payment::payment.credit_card.cardholder') }}</span>
                                            <span id="ccPreviewName">FULL NAME</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="cc-preview__label">{{ trans('payment::payment.credit_card.expiry') }}</span>
                                            <span id="ccPreviewExpiry">MM/YY</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="cc-amount">
                                    <span class="cc-amount__label">
                                        <i class="ti tabler-receipt me-1"></i>
                                        {{ trans('payment::payment.credit_card.amount_due') }}
                                    </span>
                                    <span class="cc-amount__value">
                                        ${{ number_format($booking->consultation_fee, 2) }}
                                    </span>
                                </div>

                                <div class="small mb-3">
                                    <div class="mb-1">
                                        <span class="text-muted">{{ trans('booking::booking.doctor') }}:</span>
                                        <span class="fw-medium">Dr. {{ $booking->doctor->name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted">{{ trans('booking::booking.booking_date') }}:</span>
                                        <span class="fw-medium">
                                            {{ $booking->booking_date?->format('Y-m-d') }}
                                            {{ $booking->start_time?->format('H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="cc-hint">
                                    <div class="fw-medium mb-1">
                                        <i class="ti tabler-info-circle me-1"></i>
                                        {{ trans('payment::payment.credit_card.test_cards_title') }}
                                    </div>
                                    <div>
                                        {{ trans('payment::payment.credit_card.test_cards_success') }}:
                                        <code>4242 4242 4242 4242</code>
                                    </div>
                                    <div>
                                        {{ trans('payment::payment.credit_card.test_cards_decline') }}:
                                        <code>4000 0000 0000 0002</code>
                                    </div>
                                </div>
                            </div>

                            {{-- Right column: form --}}
                            <div class="col-lg-7">
                                <form method="POST"
                                      action="{{ route('payment.credit_card.submit', $booking) }}"
                                      id="ccForm"
                                      novalidate
                                      autocomplete="on">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="cardholder_name" class="form-label">
                                            {{ trans('payment::payment.credit_card.cardholder') }}
                                        </label>
                                        <input type="text"
                                               id="cardholder_name"
                                               name="cardholder_name"
                                               autocomplete="cc-name"
                                               value="{{ old('cardholder_name', auth('web')->user()?->name) }}"
                                               placeholder="{{ trans('payment::payment.credit_card.cardholder_placeholder') }}"
                                               class="form-control @error('cardholder_name') is-invalid @enderror"
                                               required>
                                        @error('cardholder_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="card_number" class="form-label">
                                            {{ trans('payment::payment.credit_card.card_number') }}
                                        </label>
                                        <div class="input-group">
                                            <input type="text"
                                                   id="card_number"
                                                   name="card_number"
                                                   inputmode="numeric"
                                                   autocomplete="cc-number"
                                                   maxlength="19"
                                                   placeholder="1234 5678 9012 3456"
                                                   value="{{ old('card_number') }}"
                                                   class="form-control @error('card_number') is-invalid @enderror"
                                                   required>
                                            <span class="input-group-text bg-body" id="ccBrandIcon">
                                                <i class="ti tabler-credit-card"></i>
                                            </span>
                                            @error('card_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-4">
                                            <label for="expiry_month" class="form-label">
                                                {{ trans('payment::payment.credit_card.expiry_month') }}
                                            </label>
                                            <select id="expiry_month" name="expiry_month"
                                                    class="form-select @error('expiry_month') is-invalid @enderror"
                                                    autocomplete="cc-exp-month"
                                                    required>
                                                <option value="">MM</option>
                                                @for($m = 1; $m <= 12; $m++)
                                                    @php $mm = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                                    <option value="{{ $mm }}" @selected(old('expiry_month') === $mm)>{{ $mm }}</option>
                                                @endfor
                                            </select>
                                            @error('expiry_month')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <label for="expiry_year" class="form-label">
                                                {{ trans('payment::payment.credit_card.expiry_year') }}
                                            </label>
                                            <select id="expiry_year" name="expiry_year"
                                                    class="form-select @error('expiry_year') is-invalid @enderror"
                                                    autocomplete="cc-exp-year"
                                                    required>
                                                <option value="">YYYY</option>
                                                @for($y = (int)date('Y'); $y <= (int)date('Y')+12; $y++)
                                                    <option value="{{ $y }}" @selected((string)old('expiry_year') === (string)$y)>{{ $y }}</option>
                                                @endfor
                                            </select>
                                            @error('expiry_year')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <label for="cvv" class="form-label">
                                                {{ trans('payment::payment.credit_card.cvv') }}
                                            </label>
                                            <input type="password"
                                                   id="cvv"
                                                   name="cvv"
                                                   inputmode="numeric"
                                                   autocomplete="cc-csc"
                                                   maxlength="4"
                                                   placeholder="•••"
                                                   class="form-control @error('cvv') is-invalid @enderror"
                                                   required>
                                            @error('cvv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="cc-brand-row mb-3">
                                        <i class="ti tabler-lock me-1"></i>
                                        {{ trans('payment::payment.credit_card.secure_note') }}
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('patient.bookings.show', $booking) }}"
                                           class="btn btn-label-secondary">
                                            {{ trans('doctor::doctor.close') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="ccSubmit">
                                            <i class="ti tabler-lock me-1"></i>
                                            {{ trans('payment::payment.credit_card.pay_now', ['amount' => '$' . number_format($booking->consultation_fee, 2)]) }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        (function () {
            const fields = {
                name:   document.getElementById('cardholder_name'),
                number: document.getElementById('card_number'),
                month:  document.getElementById('expiry_month'),
                year:   document.getElementById('expiry_year'),
                cvv:    document.getElementById('cvv'),
            };
            const preview = {
                number: document.getElementById('ccPreviewNumber'),
                name:   document.getElementById('ccPreviewName'),
                expiry: document.getElementById('ccPreviewExpiry'),
                brand:  document.getElementById('ccPreviewBrand'),
                icon:   document.getElementById('ccBrandIcon'),
            };
            const submit = document.getElementById('ccSubmit');

            // --- Card number formatting: group every 4 digits, strip non-digits ---
            fields.number.addEventListener('input', (e) => {
                const digits = e.target.value.replace(/\D+/g, '').slice(0, 19);
                e.target.value = digits.replace(/(.{4})/g, '$1 ').trim();
                updatePreviewNumber(digits);
                updateBrand(digits);
            });

            // --- CVV: digits only ---
            fields.cvv.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D+/g, '').slice(0, 4);
            });

            // --- Name: uppercase in preview, original casing in the field ---
            fields.name.addEventListener('input', () => {
                preview.name.textContent = (fields.name.value || 'FULL NAME').toUpperCase();
            });
            if (fields.name.value) {
                preview.name.textContent = fields.name.value.toUpperCase();
            }

            // --- Expiry preview ---
            const updateExpiry = () => {
                const mm = fields.month.value || 'MM';
                const yy = fields.year.value ? String(fields.year.value).slice(-2) : 'YY';
                preview.expiry.textContent = `${mm}/${yy}`;
            };
            fields.month.addEventListener('change', updateExpiry);
            fields.year.addEventListener('change', updateExpiry);

            function updatePreviewNumber(digits) {
                let out = '';
                for (let i = 0; i < 16; i++) {
                    if (i > 0 && i % 4 === 0) out += ' ';
                    out += digits[i] ? digits[i] : '•';
                }
                preview.number.textContent = out;
            }

            function updateBrand(digits) {
                let brand = 'CARD', iconClass = 'ti tabler-credit-card';
                if (/^4/.test(digits))                  { brand = 'VISA';       iconClass = 'ti tabler-brand-visa'; }
                else if (/^(5[1-5]|2[2-7])/.test(digits)) { brand = 'MASTERCARD'; iconClass = 'ti tabler-brand-mastercard'; }
                else if (/^3[47]/.test(digits))         { brand = 'AMEX';       iconClass = 'ti tabler-brand-amex'; }
                else if (/^6(?:011|5)/.test(digits))    { brand = 'DISCOVER';   iconClass = 'ti tabler-credit-card'; }
                preview.brand.textContent = brand;
                preview.icon.innerHTML = `<i class="${iconClass}"></i>`;
            }

            // --- Prevent double-submit ---
            document.getElementById('ccForm').addEventListener('submit', () => {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    @json(trans('payment::payment.credit_card.processing'));
            });

            // Initial render if old() values restored the form
            updatePreviewNumber((fields.number.value || '').replace(/\D+/g, ''));
            updateBrand((fields.number.value || '').replace(/\D+/g, ''));
            updateExpiry();
        })();
    </script>
@endsection
