<div class="booking-wizard-container">
    <style>
        /* Booking Wizard - Using Bootstrap CSS Variables */

        /* Progress Steps */
        .bw-progress {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 0;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .bw-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
            max-width: 160px;
        }

        .bw-step-connector {
            position: absolute;
            top: 24px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: var(--bs-border-color);
            z-index: 0;
        }

        .bw-step-connector.active {
            background: var(--bs-success);
        }

        .bw-step:last-child .bw-step-connector {
            display: none;
        }

        .bw-step-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            background: var(--bs-tertiary-bg);
            border: 3px solid var(--bs-border-color);
            color: var(--bs-secondary-color);
            position: relative;
            z-index: 1;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bw-step.active .bw-step-circle {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
            box-shadow: 0 4px 20px rgba(var(--bs-primary-rgb), 0.4);
            transform: scale(1.1);
        }

        .bw-step.completed .bw-step-circle {
            background: var(--bs-success);
            border-color: var(--bs-success);
            color: #fff;
            box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.3);
        }

        .bw-step.clickable {
            cursor: pointer;
        }

        .bw-step.clickable:hover .bw-step-circle {
            transform: scale(1.05);
        }

        .bw-step-label {
            margin-top: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            text-align: center;
            transition: color 0.3s;
        }

        .bw-step.active .bw-step-label,
        .bw-step.completed .bw-step-label {
            color: var(--bs-body-color);
        }

        /* Main Card */
        .bw-card {
            background: var(--bs-card-bg, var(--bs-body-bg));
            border: 1px solid var(--bs-border-color);
            border-radius: 24px;
            box-shadow: var(--bs-box-shadow-sm);
            overflow: hidden;
        }

        .bw-card-body {
            padding: 2.5rem;
        }

        @media (max-width: 768px) {
            .bw-card-body {
                padding: 1.5rem;
            }
        }

        /* Step Header */
        .bw-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .bw-header-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 2rem;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .bw-header-icon.success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .bw-header h4 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--bs-heading-color, var(--bs-body-color));
            margin-bottom: 0.5rem;
        }

        .bw-header p {
            color: var(--bs-secondary-color);
            font-size: 1rem;
            margin: 0;
        }

        /* Selection Cards */
        .bw-selection-grid {
            display: grid;
            gap: 1rem;
        }

        .bw-selection-grid.specialties {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        }

        .bw-selection-grid.doctors {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .bw-selection-card {
            background: var(--bs-card-bg, var(--bs-body-bg));
            border: 2px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .bw-selection-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .bw-selection-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.5);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(var(--bs-primary-rgb), 0.15);
        }

        .bw-selection-card:hover::before {
            opacity: 1;
        }

        .bw-selection-card.selected {
            border-color: var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), 0.08);
        }

        .bw-selection-card.selected::after {
            content: '\2713';
            position: absolute;
            top: 12px;
            right: 12px;
            width: 24px;
            height: 24px;
            background: var(--bs-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
        }

        /* Specialty Card */
        .bw-specialty-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            transition: all 0.3s;
        }

        .bw-selection-card:hover .bw-specialty-icon,
        .bw-selection-card.selected .bw-specialty-icon {
            background: var(--bs-primary);
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.4);
        }

        .bw-specialty-name {
            font-weight: 600;
            color: var(--bs-body-color);
            text-align: center;
            font-size: 0.95rem;
        }

        /* Doctor Card */
        .bw-doctor-card {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bw-doctor-avatar {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid var(--bs-border-color);
            transition: all 0.3s;
        }

        .bw-selection-card:hover .bw-doctor-avatar,
        .bw-selection-card.selected .bw-doctor-avatar {
            border-color: var(--bs-primary);
        }

        .bw-doctor-info {
            flex: 1;
        }

        .bw-doctor-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--bs-body-color);
            margin-bottom: 0.25rem;
        }

        .bw-doctor-specialty {
            font-size: 0.875rem;
            color: var(--bs-secondary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bw-doctor-arrow {
            font-size: 1.25rem;
            color: var(--bs-secondary-color);
            transition: all 0.3s;
        }

        .bw-selection-card:hover .bw-doctor-arrow {
            color: var(--bs-primary);
            transform: translateX(4px);
        }

        /* Calendar Section */
        .bw-calendar-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 992px) {
            .bw-calendar-container {
                grid-template-columns: 1fr;
            }
        }

        .bw-calendar {
            background: var(--bs-card-bg, var(--bs-body-bg));
            border: 1px solid var(--bs-border-color);
            border-radius: 20px;
            padding: 1.5rem;
        }

        .bw-calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .bw-calendar-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--bs-body-color);
        }

        .bw-calendar-nav {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            cursor: pointer;
            transition: all 0.2s;
        }

        .bw-calendar-nav:hover:not(:disabled) {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
        }

        .bw-calendar-nav:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .bw-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .bw-weekday {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.5rem 0;
        }

        .bw-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.25rem;
        }

        .bw-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--bs-body-color);
            transition: all 0.2s;
        }

        .bw-day.other-month {
            color: var(--bs-secondary-color);
            opacity: 0.3;
        }

        .bw-day.disabled {
            color: var(--bs-secondary-color);
            opacity: 0.5;
        }

        .bw-day.available {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            cursor: pointer;
            font-weight: 600;
        }

        .bw-day.available:hover {
            background: var(--bs-primary);
            color: #fff;
            transform: scale(1.1);
        }

        .bw-day.selected {
            background: var(--bs-primary);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.4);
        }

        .bw-calendar-legend {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--bs-border-color);
        }

        .bw-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--bs-secondary-color);
        }

        .bw-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .bw-legend-dot.available {
            background: var(--bs-primary);
        }

        .bw-legend-dot.unavailable {
            background: var(--bs-border-color);
        }

        /* Time Slots */
        .bw-slots-panel {
            background: var(--bs-tertiary-bg);
            border-radius: 20px;
            padding: 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .bw-slots-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .bw-slots-header i {
            font-size: 1.25rem;
            color: var(--bs-primary);
        }

        .bw-slots-header h6 {
            font-weight: 700;
            color: var(--bs-body-color);
            margin: 0;
        }

        .bw-selected-date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .bw-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
            flex: 1;
        }

        .bw-slot {
            padding: 0.75rem;
            border-radius: 12px;
            border: 2px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--bs-body-color);
            cursor: pointer;
            transition: all 0.2s;
        }

        .bw-slot:hover {
            border-color: var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), 0.05);
            transform: translateY(-2px);
        }

        .bw-slot i {
            font-size: 0.9rem;
            color: var(--bs-primary);
        }

        .bw-slots-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            text-align: center;
            padding: 2rem;
        }

        .bw-slots-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--bs-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 2rem;
            color: var(--bs-primary);
            box-shadow: var(--bs-box-shadow-sm);
        }

        .bw-slots-empty p {
            color: var(--bs-secondary-color);
            margin: 0;
        }

        /* Summary Card */
        .bw-summary {
            background: var(--bs-tertiary-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .bw-summary-doctor {
            display: flex;
            align-items: center;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .bw-summary-avatar {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid var(--bs-body-bg);
            box-shadow: var(--bs-box-shadow-sm);
        }

        .bw-summary-doctor-info {
            flex: 1;
            margin-left: 1.25rem;
        }

        .bw-summary-doctor-name {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--bs-body-color);
            margin-bottom: 0.25rem;
        }

        .bw-summary-doctor-specialty {
            color: var(--bs-secondary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bw-summary-fee {
            text-align: right;
        }

        .bw-summary-fee-label {
            font-size: 0.8rem;
            color: var(--bs-secondary-color);
        }

        .bw-summary-fee-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--bs-primary);
        }

        .bw-summary-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        @media (max-width: 576px) {
            .bw-summary-details {
                grid-template-columns: 1fr;
            }
        }

        .bw-summary-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bw-summary-item-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .bw-summary-item-icon.primary {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .bw-summary-item-icon.success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .bw-summary-item-label {
            font-size: 0.75rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bw-summary-item-value {
            font-weight: 700;
            color: var(--bs-body-color);
        }

        /* Notes Input */
        .bw-notes {
            margin-bottom: 1.5rem;
        }

        .bw-notes-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.75rem;
        }

        .bw-notes-label small {
            font-weight: 400;
            color: var(--bs-secondary-color);
        }

        .bw-notes textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--bs-border-color);
            border-radius: 12px;
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .bw-notes textarea::placeholder {
            color: var(--bs-secondary-color);
        }

        .bw-notes textarea:focus {
            outline: none;
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
        }

        /* Auth Section */
        .bw-auth-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .bw-auth-tabs .btn-group {
            background: var(--bs-tertiary-bg);
            padding: 0.25rem;
            border-radius: 12px;
        }

        .bw-auth-tabs .btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
        }

        .bw-auth-form {
            background: var(--bs-card-bg, var(--bs-body-bg));
            border: 1px solid var(--bs-border-color);
            border-radius: 20px;
            padding: 2rem;
        }

        .bw-form-group {
            margin-bottom: 1.25rem;
        }

        .bw-form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.5rem;
        }

        .bw-form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--bs-border-color);
            border-radius: 12px;
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .bw-form-input::placeholder {
            color: var(--bs-secondary-color);
        }

        .bw-form-input:focus {
            outline: none;
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
        }

        .bw-form-input.is-invalid {
            border-color: var(--bs-danger);
        }

        /* Buttons */
        .bw-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--bs-border-color);
            margin-top: 1.5rem;
        }

        .bw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .bw-btn-back {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border: 1px solid var(--bs-border-color);
        }

        .bw-btn-back:hover {
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
        }

        .bw-btn-primary {
            background: var(--bs-primary);
            color: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.3);
        }

        .bw-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--bs-primary-rgb), 0.4);
            color: #fff;
        }

        .bw-btn-success {
            background: var(--bs-success);
            color: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.3);
        }

        .bw-btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.4);
            color: #fff;
        }

        /* Alert */
        .bw-alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .bw-alert-info {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .bw-alert-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .bw-alert-danger {
            background: rgba(var(--bs-danger-rgb), 0.1);
            color: var(--bs-danger);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .bw-progress {
                gap: 0.25rem;
            }

            .bw-step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.875rem;
            }

            .bw-step-connector {
                top: 20px;
            }

            .bw-step-label {
                display: none;
            }

            .bw-header h4 {
                font-size: 1.4rem;
            }

            .bw-header-icon {
                width: 64px;
                height: 64px;
                font-size: 1.5rem;
            }
        }

        /* Spinner */
        .bw-btn .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>

    <!-- Progress Steps -->
    <div class="bw-progress">
        @php
            $steps = [
                1 => ['label' => __('booking::booking.step_1'), 'icon' => 'stethoscope'],
                2 => ['label' => __('booking::booking.step_2'), 'icon' => 'users'],
                3 => ['label' => __('booking::booking.step_3'), 'icon' => 'calendar'],
                4 => ['label' => __('booking::booking.step_4'), 'icon' => 'clipboard-check'],
            ];
            if (!$this->isLoggedIn) {
                $steps[5] = ['label' => __('booking::booking.step_5'), 'icon' => 'user-circle'];
            }
        @endphp

        @foreach($steps as $num => $stepData)
            <div class="bw-step {{ $step >= $num ? 'active' : '' }} {{ $step > $num ? 'completed clickable' : '' }}"
                 @if($step > $num) wire:click="goToStep({{ $num }})" @endif>
                <div class="bw-step-connector {{ $step > $num ? 'active' : '' }}"></div>
                <div class="bw-step-circle">
                    @if($step > $num)
                        <i class="ti tabler-check"></i>
                    @else
                        <span>{{ $num }}</span>
                    @endif
                </div>
                <span class="bw-step-label">{{ $stepData['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="bw-card">
        <div class="bw-card-body">

            <!-- Step 1: Select Specialty -->
            @if($step === 1)
                <div class="bw-header">
                    <div class="bw-header-icon">
                        <i class="ti tabler-stethoscope"></i>
                    </div>
                    <h4>{{ __('booking::booking.select_specialty') }}</h4>
                    <p>{{ __('booking::booking.choose_medical_specialty') }}</p>
                </div>

                @if($specialties->isEmpty())
                    <div class="bw-alert bw-alert-info">
                        <i class="ti tabler-info-circle"></i>
                        <span>{{ __('booking::booking.no_specialties') }}</span>
                    </div>
                @else
                    <div class="bw-selection-grid specialties">
                        @foreach($specialties as $specialty)
                            <div class="bw-selection-card {{ $selectedSpecialtyId === $specialty->id ? 'selected' : '' }}"
                                 wire:click="selectSpecialty({{ $specialty->id }})">
                                <div class="bw-specialty-icon">
                                    <i class="ti tabler-heartbeat"></i>
                                </div>
                                <div class="bw-specialty-name">{{ $specialty->name }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- Step 2: Choose Doctor -->
            @if($step === 2)
                <div class="bw-header">
                    <div class="bw-header-icon">
                        <i class="ti tabler-users"></i>
                    </div>
                    <h4>{{ __('booking::booking.select_doctor') }}</h4>
                    <p>{{ __('booking::booking.choose_preferred_doctor') }}</p>
                </div>

                @if($filteredDoctors->isEmpty())
                    <div class="bw-alert bw-alert-warning">
                        <i class="ti tabler-alert-triangle"></i>
                        <span>{{ __('booking::booking.no_doctors') }}</span>
                    </div>
                @else
                    <div class="bw-selection-grid doctors">
                        @foreach($filteredDoctors as $doctor)
                            <div class="bw-selection-card {{ $selectedDoctorId === $doctor->id ? 'selected' : '' }}"
                                 wire:click="selectDoctor({{ $doctor->id }})">
                                <div class="bw-doctor-card">
                                    <img src="{{ $doctor->getFirstMediaUrl('img') ?: asset('assets/img/avatars/1.png') }}"
                                         alt="Dr. {{ $doctor->name }}"
                                         class="bw-doctor-avatar">
                                    <div class="bw-doctor-info">
                                        <div class="bw-doctor-name">Dr. {{ $doctor->name }}</div>
                                        <div class="bw-doctor-specialty">
                                            <i class="ti tabler-stethoscope"></i>
                                            {{ $doctor->medicalSpecialty?->name }}
                                        </div>
                                    </div>
                                    <i class="ti tabler-chevron-right bw-doctor-arrow"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="bw-actions">
                    <button class="bw-btn bw-btn-back" wire:click="previousStep">
                        <i class="ti tabler-arrow-left"></i>
                        {{ __('booking::booking.back') }}
                    </button>
                </div>
            @endif

            <!-- Step 3: Pick Date & Time -->
            @if($step === 3)
                <div class="bw-header">
                    <div class="bw-header-icon">
                        <i class="ti tabler-calendar"></i>
                    </div>
                    <h4>{{ __('booking::booking.select_date') }}</h4>
                    <p>{{ __('booking::booking.choose_date_time') }}</p>
                </div>

                @if($availableDates->isEmpty())
                    <div class="bw-alert bw-alert-warning">
                        <i class="ti tabler-calendar-off"></i>
                        <span>{{ __('booking::booking.no_available_slots') }}</span>
                    </div>
                @else
                    <div class="bw-calendar-container">
                        <!-- Calendar -->
                        <div class="bw-calendar">
                            <div class="bw-calendar-header">
                                <button type="button" class="bw-calendar-nav"
                                        wire:click="previousMonth"
                                        @if(!$this->canGoPreviousMonth) disabled @endif>
                                    <i class="ti tabler-chevron-left"></i>
                                </button>
                                <span class="bw-calendar-title">{{ $this->calendarMonthName }}</span>
                                <button type="button" class="bw-calendar-nav" wire:click="nextMonth">
                                    <i class="ti tabler-chevron-right"></i>
                                </button>
                            </div>

                            <div class="bw-weekdays">
                                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                                    <div class="bw-weekday">{{ $day }}</div>
                                @endforeach
                            </div>

                            <div class="bw-days">
                                @foreach($this->calendarDays as $day)
                                    @if($day['isAvailable'])
                                        <button type="button"
                                                wire:click="selectDate('{{ $day['date'] }}')"
                                                class="bw-day available {{ $day['isSelected'] ? 'selected' : '' }}">
                                            {{ $day['day'] }}
                                        </button>
                                    @elseif($day['isCurrentMonth'])
                                        <div class="bw-day disabled">{{ $day['day'] }}</div>
                                    @else
                                        <div class="bw-day other-month">{{ $day['day'] }}</div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="bw-calendar-legend">
                                <div class="bw-legend-item">
                                    <div class="bw-legend-dot available"></div>
                                    <span>{{ __('booking::booking.available') }}</span>
                                </div>
                                <div class="bw-legend-item">
                                    <div class="bw-legend-dot unavailable"></div>
                                    <span>{{ __('booking::booking.unavailable') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Time Slots -->
                        <div class="bw-slots-panel">
                            <div class="bw-slots-header">
                                <i class="ti tabler-clock"></i>
                                <h6>{{ __('booking::booking.available_slots') }}</h6>
                            </div>

                            @if($selectedDate && count($availableSlots) > 0)
                                <div class="bw-selected-date">
                                    <i class="ti tabler-calendar-event"></i>
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                                </div>
                                <div class="bw-slots-grid">
                                    @foreach($availableSlots as $slot)
                                        <button type="button"
                                                wire:click="selectSlot({{ $selectedAvailabilityId }}, '{{ $slot['start'] }}')"
                                                class="bw-slot">
                                            <i class="ti tabler-clock"></i>
                                            {{ $slot['start'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @elseif($selectedDate)
                                <div class="bw-alert bw-alert-info">
                                    <i class="ti tabler-info-circle"></i>
                                    {{ __('booking::booking.no_slots_for_date') }}
                                </div>
                            @else
                                <div class="bw-slots-empty">
                                    <div class="bw-slots-empty-icon">
                                        <i class="ti tabler-calendar-event"></i>
                                    </div>
                                    <p>{{ __('booking::booking.select_date_first') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="bw-actions">
                    <button class="bw-btn bw-btn-back" wire:click="previousStep">
                        <i class="ti tabler-arrow-left"></i>
                        {{ __('booking::booking.back') }}
                    </button>
                </div>
            @endif

            <!-- Step 4: Confirm Booking -->
            @if($step === 4)
                <div class="bw-header">
                    <div class="bw-header-icon success">
                        <i class="ti tabler-clipboard-check"></i>
                    </div>
                    <h4>{{ __('booking::booking.booking_summary') }}</h4>
                    <p>{{ __('booking::booking.review_appointment') }}</p>
                </div>

                @error('booking')
                    <div class="bw-alert bw-alert-danger">
                        <i class="ti tabler-alert-circle"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <!-- Summary Card -->
                <div class="bw-summary">
                    <div class="bw-summary-doctor">
                        <img src="{{ $this->selectedDoctor?->getFirstMediaUrl('img') ?: asset('assets/img/avatars/1.png') }}"
                             alt="Doctor"
                             class="bw-summary-avatar">
                        <div class="bw-summary-doctor-info">
                            <div class="bw-summary-doctor-name">Dr. {{ $this->selectedDoctor?->name }}</div>
                            <div class="bw-summary-doctor-specialty">
                                <i class="ti tabler-stethoscope"></i>
                                {{ $this->selectedDoctor?->medicalSpecialty?->name }}
                            </div>
                        </div>
                        <div class="bw-summary-fee">
                            <div class="bw-summary-fee-label">{{ __('booking::booking.consultation_fee') }}</div>
                            <div class="bw-summary-fee-value">${{ number_format($selectedAvailability?->consultation_fee ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="bw-summary-details">
                        <div class="bw-summary-item">
                            <div class="bw-summary-item-icon primary">
                                <i class="ti tabler-calendar"></i>
                            </div>
                            <div>
                                <div class="bw-summary-item-label">{{ __('booking::booking.date') }}</div>
                                <div class="bw-summary-item-value">{{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</div>
                            </div>
                        </div>
                        <div class="bw-summary-item">
                            <div class="bw-summary-item-icon primary">
                                <i class="ti tabler-clock"></i>
                            </div>
                            <div>
                                <div class="bw-summary-item-label">{{ __('booking::booking.time') }}</div>
                                <div class="bw-summary-item-value">{{ $selectedTime }}</div>
                            </div>
                        </div>
                        <div class="bw-summary-item">
                            <div class="bw-summary-item-icon success">
                                <i class="ti tabler-hourglass"></i>
                            </div>
                            <div>
                                <div class="bw-summary-item-label">{{ __('booking::booking.duration') }}</div>
                                <div class="bw-summary-item-value">{{ $selectedAvailability?->slot_duration }} {{ __('booking::booking.minutes') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bw-notes">
                    <label class="bw-notes-label">
                        <i class="ti tabler-notes"></i>
                        {{ __('booking::booking.notes') }}
                        <small>({{ __('booking::booking.optional') }})</small>
                    </label>
                    <textarea wire:model="notes"
                              rows="3"
                              placeholder="{{ __('booking::booking.notes_placeholder') }}"></textarea>
                    @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="bw-actions">
                    <button class="bw-btn bw-btn-back" wire:click="previousStep">
                        <i class="ti tabler-arrow-left"></i>
                        {{ __('booking::booking.back') }}
                    </button>
                    <button class="bw-btn {{ $this->isLoggedIn ? 'bw-btn-success' : 'bw-btn-primary' }}"
                            wire:click="proceedToAuth"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="proceedToAuth">
                            @if($this->isLoggedIn)
                                <i class="ti tabler-check"></i>
                                {{ __('booking::booking.confirm_booking') }}
                            @else
                                {{ __('booking::booking.continue') }}
                                <i class="ti tabler-arrow-right"></i>
                            @endif
                        </span>
                        <span wire:loading wire:target="proceedToAuth">
                            <span class="spinner-border spinner-border-sm"></span>
                            {{ __('booking::booking.processing') }}
                        </span>
                    </button>
                </div>
            @endif

            <!-- Step 5: Login / Register -->
            @if($step === 5)
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="bw-header">
                            <div class="bw-header-icon">
                                <i class="ti tabler-user-circle"></i>
                            </div>
                            <h4>{{ __('booking::booking.login_or_register') }}</h4>
                            <p>{{ __('booking::booking.sign_in_to_complete') }}</p>
                        </div>

                        <!-- Auth Mode Tabs -->
                        <div class="bw-auth-tabs">
                            <div class="btn-group" role="group">
                                <button type="button"
                                        class="btn {{ $authMode === 'login' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="setAuthMode('login')">
                                    <i class="ti tabler-login me-2"></i>
                                    {{ __('booking::booking.login') }}
                                </button>
                                <button type="button"
                                        class="btn {{ $authMode === 'register' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="setAuthMode('register')">
                                    <i class="ti tabler-user-plus me-2"></i>
                                    {{ __('booking::booking.register') }}
                                </button>
                            </div>
                        </div>

                        @if($authError)
                            <div class="bw-alert bw-alert-danger">
                                <i class="ti tabler-alert-circle"></i>
                                <span>{{ $authError }}</span>
                                <button type="button" class="btn-close ms-auto" wire:click="$set('authError', null)"></button>
                            </div>
                        @endif

                        <!-- Login Form -->
                        @if($authMode === 'login')
                            <div class="bw-auth-form">
                                <form wire:submit="login">
                                    <div class="bw-form-group">
                                        <label class="bw-form-label">
                                            <i class="ti tabler-mail"></i>
                                            {{ __('booking::booking.auth_email') }}
                                        </label>
                                        <input type="email"
                                               wire:model="loginEmail"
                                               class="bw-form-input @error('loginEmail') is-invalid @enderror"
                                               placeholder="{{ __('booking::booking.auth_email_placeholder') }}">
                                        @error('loginEmail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="bw-form-group">
                                        <label class="bw-form-label">
                                            <i class="ti tabler-lock"></i>
                                            {{ __('booking::booking.auth_password') }}
                                        </label>
                                        <input type="password"
                                               wire:model="loginPassword"
                                               class="bw-form-input @error('loginPassword') is-invalid @enderror"
                                               placeholder="{{ __('booking::booking.auth_password_placeholder') }}">
                                        @error('loginPassword') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="bw-form-group">
                                        <div class="form-check">
                                            <input type="checkbox" wire:model="remember" class="form-check-input" id="remember">
                                            <label class="form-check-label" for="remember">
                                                {{ __('booking::booking.remember_me') }}
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="bw-btn bw-btn-primary w-100" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="login">
                                            <i class="ti tabler-login"></i>
                                            {{ __('booking::booking.login_and_book') }}
                                        </span>
                                        <span wire:loading wire:target="login">
                                            <span class="spinner-border spinner-border-sm"></span>
                                            {{ __('booking::booking.processing') }}
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Register Form -->
                        @if($authMode === 'register')
                            <div class="bw-auth-form">
                                <form wire:submit="register">
                                    <div class="bw-form-group">
                                        <label class="bw-form-label">
                                            <i class="ti tabler-user"></i>
                                            {{ __('booking::booking.auth_full_name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               wire:model="registerName"
                                               class="bw-form-input @error('registerName') is-invalid @enderror"
                                               placeholder="{{ __('booking::booking.auth_full_name_placeholder') }}">
                                        @error('registerName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="bw-form-group">
                                                <label class="bw-form-label">
                                                    <i class="ti tabler-mail"></i>
                                                    {{ __('booking::booking.auth_email') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="email"
                                                       wire:model="registerEmail"
                                                       class="bw-form-input @error('registerEmail') is-invalid @enderror"
                                                       placeholder="{{ __('booking::booking.auth_email_placeholder') }}">
                                                @error('registerEmail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bw-form-group">
                                                <label class="bw-form-label">
                                                    <i class="ti tabler-phone"></i>
                                                    {{ __('booking::booking.auth_phone') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="tel"
                                                       wire:model="registerPhone"
                                                       class="bw-form-input @error('registerPhone') is-invalid @enderror"
                                                       placeholder="{{ __('booking::booking.auth_phone_placeholder') }}">
                                                @error('registerPhone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="bw-form-group" x-data="{ show: false }">
                                                <label class="bw-form-label">
                                                    <i class="ti tabler-lock"></i>
                                                    {{ __('booking::booking.auth_password') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <input :type="show ? 'text' : 'password'"
                                                           wire:model.live.debounce.400ms="registerPassword"
                                                           class="bw-form-input @error('registerPassword') is-invalid @enderror"
                                                           style="padding-inline-end: 2.75rem;"
                                                           autocomplete="new-password"
                                                           placeholder="{{ __('booking::booking.auth_password_placeholder') }}">
                                                    <button type="button"
                                                            class="btn btn-link position-absolute top-50 translate-middle-y text-muted p-0"
                                                            style="inset-inline-end: 0.75rem;"
                                                            @click="show = !show"
                                                            :aria-label="show
                                                                ? '{{ __('booking::booking.auth_password_hide') }}'
                                                                : '{{ __('booking::booking.auth_password_show') }}'">
                                                        <i class="ti" :class="show ? 'tabler-eye-off' : 'tabler-eye'"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text small text-muted mt-1">
                                                    <i class="ti tabler-info-circle me-1"></i>{{ __('booking::booking.auth_password_hint') }}
                                                </div>
                                                @error('registerPassword') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bw-form-group" x-data="{ show: false }">
                                                <label class="bw-form-label">
                                                    <i class="ti tabler-lock-check"></i>
                                                    {{ __('booking::booking.auth_confirm_password') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <input :type="show ? 'text' : 'password'"
                                                           wire:model.live.debounce.400ms="registerPassword_confirmation"
                                                           class="bw-form-input"
                                                           style="padding-inline-end: 2.75rem;"
                                                           autocomplete="new-password"
                                                           placeholder="{{ __('booking::booking.auth_confirm_password_placeholder') }}">
                                                    <button type="button"
                                                            class="btn btn-link position-absolute top-50 translate-middle-y text-muted p-0"
                                                            style="inset-inline-end: 0.75rem;"
                                                            @click="show = !show"
                                                            :aria-label="show
                                                                ? '{{ __('booking::booking.auth_password_hide') }}'
                                                                : '{{ __('booking::booking.auth_password_show') }}'">
                                                        <i class="ti" :class="show ? 'tabler-eye-off' : 'tabler-eye'"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="bw-btn bw-btn-primary w-100" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="register">
                                            <i class="ti tabler-user-plus"></i>
                                            {{ __('booking::booking.register_and_book') }}
                                        </span>
                                        <span wire:loading wire:target="register">
                                            <span class="spinner-border spinner-border-sm"></span>
                                            {{ __('booking::booking.processing') }}
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="text-center mt-4">
                            <button class="btn btn-link text-muted" wire:click="previousStep">
                                <i class="ti tabler-arrow-left me-1"></i>
                                {{ __('booking::booking.back') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
