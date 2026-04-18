<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ trans('patient::patient.prescription') }} - #{{ $examination->id }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        * {
            font-family: DejaVu Sans, sans-serif;
        }
        body {
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        .header h1 {
            margin: 0;
            color: #4CAF50;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #4CAF50;
            font-size: 16px;
        }
        .info-row {
            margin: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .section-title {
            margin: 25px 0 10px 0;
            padding: 10px;
            background: #4CAF50;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        .diagnosis-list {
            padding-left: 20px;
        }
        .diagnosis-list li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #4CAF50;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #333;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ trans('patient::patient.medical_prescription') }}</h1>
        <p>{{ trans('patient::patient.prescription_id') }}: #{{ $examination->id }}</p>
        <p>{{ trans('patient::patient.date') }}: {{ $examination->created_at->format('Y-m-d') }}</p>
    </div>

    <!-- Doctor Information -->
    <div class="info-section">
        <h3>{{ trans('patient::patient.doctor_information') }}</h3>
        <div class="info-row">
            <span class="label">{{ trans('patient::patient.doctor_name') }}:</span>
            Dr. {{ $examination->doctor->name }}
        </div>
        @if($examination->clinic)
            <div class="info-row">
                <span class="label">{{ trans('patient::patient.clinic') }}:</span>
                {{ $examination->clinic->name }}
            </div>
        @endif
    </div>

    <!-- Patient Information -->
    <div class="info-section">
        <h3>{{ trans('patient::patient.patient_information') }}</h3>
        <div class="info-row">
            <span class="label">{{ trans('patient::patient.patient_name') }}:</span>
            {{ $examination->patient->name }}
        </div>
        <div class="info-row">
            <span class="label">{{ trans('patient::patient.age') }}:</span>
            {{ $examination->patient->age ?? '-' }} {{ trans('patient::patient.years') }}
        </div>
        @if($examination->patient->gender)
            <div class="info-row">
                <span class="label">{{ trans('patient::patient.gender') }}:</span>
                {{ $examination->patient->gender->label() }}
            </div>
        @endif
    </div>

    <!-- Vital Signs -->
    @if($examination->vitalSigns && $examination->vitalSigns->count() > 0)
        <div class="section-title">{{ trans('patient::patient.vital_signs') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('patient::patient.vital_sign') }}</th>
                    <th>{{ trans('patient::patient.value') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examination->vitalSigns as $vitalSign)
                    <tr>
                        <td>{{ $vitalSign->name ?? '-' }}</td>
                        <td>{{ $vitalSign->pivot?->value ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Diagnosis -->
    @if($examination->finalDiagnosis && $examination->finalDiagnosis->count() > 0)
        <div class="section-title">{{ trans('patient::patient.diagnosis') }}</div>
        <ul class="diagnosis-list">
            @foreach($examination->finalDiagnosis as $diagnosis)
                <li>{{ $diagnosis->name }}</li>
            @endforeach
        </ul>
    @endif

    <!-- Prescribed Medicines -->
    @if($examination->medicines && $examination->medicines->count() > 0)
        <div class="section-title">{{ trans('patient::patient.prescribed_medicines') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('patient::patient.medicine') }}</th>
                    <th>{{ trans('patient::patient.dosage_form') }}</th>
                    <th>{{ trans('patient::patient.dose') }}</th>
                    <th>{{ trans('patient::patient.dosage') }}</th>
                    <th>{{ trans('patient::patient.duration') }}</th>
                    <th>{{ trans('patient::patient.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examination->medicines as $medicine)
                    <tr>
                        <td>{{ $medicine->name }}</td>
                        <td>{{ $medicine->pivot->dosageForm?->name ?? '-' }}</td>
                        <td>{{ $medicine->pivot?->dose ?? '-' }}</td>
                        <td>{{ $medicine->pivot?->dosage ?? '-' }}</td>
                        <td>{{ $medicine->pivot?->duration ?? '-' }}</td>
                        <td>{{ $medicine->pivot?->note ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Medical Tests -->
    @if($examination->medicalTests && $examination->medicalTests->count() > 0)
        <div class="section-title">{{ trans('patient::patient.requested_medical_tests') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('patient::patient.test_name') }}</th>
                    <th>{{ trans('patient::patient.result') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examination->medicalTests as $test)
                    <tr>
                        <td>{{ $test->name }}</td>
                        <td>{{ $test->pivot?->value ?? trans('patient::patient.pending') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Additional Notes -->
    @if($examination->note)
        <div class="section-title">{{ trans('patient::patient.additional_notes') }}</div>
        <div style="padding: 10px; background: #f9f9f9;">
            {{ $examination->note }}
        </div>
    @endif

    <!-- Signature -->
    <div class="signature-section">
        <div>{{ trans('patient::patient.doctor_signature') }}</div>
        <div class="signature-line"></div>
        <div>Dr. {{ $examination->doctor->name }}</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>{{ trans('patient::patient.prescription_footer') }}</p>
        <p>{{ trans('patient::patient.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
