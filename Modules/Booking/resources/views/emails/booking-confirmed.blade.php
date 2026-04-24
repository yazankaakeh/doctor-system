{{--
    Booking confirmed email template (Markdown-mail).
    Rendered by the Mailable that wraps BookingConfirmedNotification@toMail
    when the template-based variant is preferred over the fluent builder.
    Variables expected: $patient, $doctor, $booking, $joinUrl.
--}}
<x-mail::message>
{{-- Localized subject / heading. --}}
# {{ trans('booking::booking.email.booking_confirmed_subject') }}

{{ trans('booking::booking.email.greeting', ['name' => $patient->name]) }}

{{ trans('booking::booking.email.booking_confirmed_line1') }}

---

{{-- Appointment summary table. --}}
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
</x-mail::table>

---

{{-- Video consultation CTA + tips. Only rendered when a meeting link exists. --}}
@if($booking->meeting_link)
## {{ trans('booking::booking.email.video_consultation') }}

{{ trans('booking::booking.email.video_consultation_ready') }}

<x-mail::button :url="$joinUrl" color="success">
{{ trans('booking::booking.email.join_consultation') }}
</x-mail::button>

<x-mail::panel>
**{{ trans('core::video.tip_connection') }}:** {{ trans('core::video.tip_connection_desc') }}

**{{ trans('core::video.tip_audio') }}:** {{ trans('core::video.tip_audio_desc') }}

**{{ trans('core::video.tip_lighting') }}:** {{ trans('core::video.tip_lighting_desc') }}
</x-mail::panel>
@endif

---

{{ trans('booking::booking.email.confirmed_footer') }}

{{ trans('booking::booking.email.thank_you') }}

{{ config('app.name') }}
</x-mail::message>
