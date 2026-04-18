<?php

return [
    'name' => 'Booking',

    /*
    |--------------------------------------------------------------------------
    | Default Slot Duration
    |--------------------------------------------------------------------------
    | Default consultation slot duration in minutes
    */
    'default_slot_duration' => 30,

    /*
    |--------------------------------------------------------------------------
    | Default Consultation Fee
    |--------------------------------------------------------------------------
    | Default consultation fee if not set by doctor
    */
    'default_consultation_fee' => 50.00,

    /*
    |--------------------------------------------------------------------------
    | Meeting Link Generation
    |--------------------------------------------------------------------------
    | Provider for generating meeting links (null for manual entry)
    */
    'meeting_provider' => env('BOOKING_MEETING_PROVIDER', null),
];
