<x-mail::message>
# {{ trans('booking::booking.email.booking_created_subject') }}

{{ trans('booking::booking.email.greeting', ['name' => $patient->name]) }}

{{ trans('booking::booking.email.booking_created_line1') }}

---

## {{ trans('booking::booking.email.appointment_details') }}

<x-mail::table>
| {{ trans('booking::booking.email.doctor_label') }} | **Dr. {{ $doctor->name }}** |
|:--|:--|
@if($doctor->medicalSpecialty)
| {{ trans('booking::booking.email.specialty_label') }} | {{ $doctor->medicalSpecialty->name }} |
@endif
| {{ trans('booking::booking.email.date_label') }} | **{{ $booking->booking_date->format('l, F j, Y') }}** |
| {{ trans('booking::booking.email.time_label') }} | **{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}** |
| {{ trans('booking::booking.email.duration_label') }} | {{ $booking->duration ?? 0 }} {{ trans('booking::booking.minutes') }} |
| {{ trans('booking::booking.email.fee_label') }} | **${{ number_format($booking->consultation_fee, 2) }}** |
| {{ trans('booking::booking.email.status_label') }} | {{ $booking->status->label() }} |
</x-mail::table>

@if($booking->notes)
---

### {{ trans('booking::booking.email.your_notes') }}

{{ $booking->notes }}
@endif

---

<x-mail::button :url="$viewUrl" color="primary">
{{ trans('booking::booking.email.view_booking') }}
</x-mail::button>

{{ trans('booking::booking.email.booking_created_footer') }}

{{ trans('booking::booking.email.thank_you') }}

{{ config('app.name') }}
</x-mail::message>
