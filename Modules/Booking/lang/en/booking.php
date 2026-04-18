<?php

return [
    'module' => 'Booking',
    'availability' => 'Availability',
    'bookings' => 'Bookings',
    'my_bookings' => 'My Bookings',
    'book_appointment' => 'Book Appointment',
    'book_appointment_subtitle' => 'Schedule your consultation with our expert doctors',

    // Availability
    'add_availability' => 'Add Availability',
    'edit_availability' => 'Edit Availability',
    'date' => 'Date',
    'start_time' => 'Start Time',
    'end_time' => 'End Time',
    'slot_duration' => 'Slot Duration (minutes)',
    'consultation_fee' => 'Consultation Fee',
    'is_active' => 'Active',

    // Booking wizard
    'step_1' => 'Specialty',
    'step_2' => 'Doctor',
    'step_3' => 'Date & Time',
    'step_4' => 'Confirm',
    'step_5' => 'Account',
    'select_specialty' => 'Select a Specialty',
    'choose_medical_specialty' => 'Choose the medical specialty you need',
    'select_doctor' => 'Select a Doctor',
    'choose_preferred_doctor' => 'Choose your preferred healthcare provider',
    'select_date' => 'Select Date & Time',
    'choose_date_time' => 'Pick your preferred appointment slot',
    'select_time' => 'Select a Time Slot',
    'available_dates' => 'Available Dates',
    'available_slots' => 'Available Time Slots',
    'available' => 'Available',
    'unavailable' => 'Unavailable',
    'no_available_slots' => 'No available slots for this doctor',
    'no_slots_for_date' => 'No slots available for this date',
    'select_date_first' => 'Please select a date to see available time slots',
    'no_specialties' => 'No specialties available at the moment',
    'no_doctors' => 'No doctors available for this specialty',
    'review_appointment' => 'Review your appointment details before confirming',
    'sign_in_to_complete' => 'Sign in or create an account to complete your booking',
    'notes' => 'Notes',
    'notes_placeholder' => 'Add any notes for the doctor (symptoms, questions, etc.)',
    'optional' => 'optional',
    'booking_summary' => 'Booking Summary',
    'continue' => 'Continue',
    'back' => 'Back',
    'confirm_booking' => 'Confirm Booking',
    'processing' => 'Processing...',
    'minutes' => 'minutes',

    // Calendar view
    'table_view' => 'Table View',
    'calendar_view' => 'Calendar View',
    'today' => 'Today',
    'month' => 'Month',
    'week' => 'Week',
    'day' => 'Day',
    'list' => 'List',
    'complete' => 'Complete',
    'no_show' => 'No Show',

    // Status labels
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'cancelled' => 'Cancelled',
    'completed' => 'Completed',

    // Auth step
    'login_or_register' => 'Login or Create Account',
    'login' => 'Login',
    'register' => 'Register',
    'auth_email' => 'Email Address',
    'auth_email_placeholder' => 'Enter your email',
    'auth_password' => 'Password',
    'auth_password_placeholder' => 'Enter your password',
    'remember_me' => 'Remember me',
    'auth_full_name' => 'Full Name',
    'auth_full_name_placeholder' => 'Enter your full name',
    'auth_phone' => 'Phone Number',
    'auth_phone_placeholder' => 'Enter your phone number',
    'auth_confirm_password' => 'Confirm Password',
    'auth_confirm_password_placeholder' => 'Confirm your password',
    'login_and_book' => 'Login & Book',
    'register_and_book' => 'Register & Book',

    // Booking details
    'booking_details' => 'Booking Details',
    'doctor' => 'Doctor',
    'patient' => 'Patient',
    'booking_date' => 'Appointment Date',
    'time' => 'Time',
    'duration' => 'Duration',
    'fee' => 'Fee',
    'status' => 'Status',
    'meeting_link' => 'Meeting Link',
    'join_consultation' => 'Join Consultation',
    'no_meeting_scheduled' => 'No meeting room scheduled yet',
    'cancel_booking' => 'Cancel Booking',
    'cancellation_reason' => 'Cancellation Reason (optional)',

    // Chat
    'chat_with_doctor' => 'Chat with Doctor',
    'chat_with_patient' => 'Chat with Patient',
    'conversation_welcome' => 'Conversation started for appointment with Dr. :doctor on :date at :time. You can now communicate with each other.',
    'no_messages_yet' => 'No messages yet. Start the conversation!',
    'type_message' => 'Type a message...',
    'chat_not_available' => 'Chat is not available for this booking.',
    'system' => 'System',

    // Messages
    'availability_created' => 'Availability created successfully.',
    'availability_updated' => 'Availability updated successfully.',
    'availability_deleted' => 'Availability deleted successfully.',
    'booking_created' => 'Booking created successfully. Please proceed to payment.',
    'booking_confirmed' => 'Your booking has been confirmed.',
    'booking_cancelled' => 'Booking cancelled successfully.',
    'booking_completed' => 'Booking marked as completed.',
    'marked_no_show' => 'Booking marked as no-show.',
    'slot_not_available' => 'This slot is no longer available.',
    'cannot_cancel' => 'This booking cannot be cancelled.',
    'cannot_complete' => 'This booking cannot be marked as completed.',
    'cannot_mark_no_show' => 'This booking cannot be marked as no-show.',
    'already_processed' => 'This booking has already been processed.',

    // Enum
    'enum' => [
        'BookingStatusEnum' => [
            1 => 'Pending',
            2 => 'Confirmed',
            3 => 'Cancelled',
            4 => 'Completed',
            5 => 'No Show',
        ],
    ],

    // Email
    'email' => [
        'greeting' => 'Hello :name,',
        'thank_you' => 'Thank you for using our service!',

        // Booking Created (Patient)
        'booking_created_subject' => 'Your Appointment Has Been Scheduled',
        'booking_created_line1' => 'Thank you for booking an appointment with us. Here are your appointment details:',
        'booking_created_footer' => 'You will receive a confirmation email once your appointment is confirmed.',

        // Booking Confirmed (Patient)
        'booking_confirmed_subject' => 'Your Booking is Confirmed',
        'booking_confirmed_line1' => 'Great news! Your appointment has been confirmed.',
        'booking_details' => 'Doctor: :doctor | Date: :date | Time: :time',
        'confirmed_footer' => 'Please join the video consultation at the scheduled time. We recommend joining a few minutes early.',
        'video_consultation' => 'Video Consultation',
        'video_consultation_ready' => 'Your video consultation room is ready. Click the button below to join at the scheduled time.',
        'join_consultation' => 'Join Video Consultation',

        // Booking Cancelled
        'booking_cancelled_subject' => 'Booking Cancelled',
        'booking_cancelled_line1' => 'A booking has been cancelled.',
        'cancelled_details' => 'Patient: :patient | Date: :date | Time: :time',
        'cancellation_reason' => 'Reason: :reason',
        'slot_available_again' => 'The slot is now available for other patients.',

        // Patient Reminders
        'reminder_24h_subject' => 'Appointment Reminder - Tomorrow',
        'reminder_24h_line1' => 'This is a friendly reminder that your appointment is scheduled for tomorrow.',
        'reminder_1h_subject' => 'Appointment Starting Soon',
        'reminder_1h_line1' => 'Your appointment is starting in about 1 hour. Please prepare to join the consultation.',
        'reminder_join_instruction' => 'Please click the button below to join your video consultation at the scheduled time.',
        'reminder_footer' => 'Please ensure you have a stable internet connection and your camera/microphone are working properly.',

        // Doctor Reminders
        'doctor_reminder_24h_subject' => 'Appointment Reminder - Tomorrow',
        'doctor_reminder_24h_line1' => 'This is a reminder about your upcoming appointment scheduled for tomorrow.',
        'doctor_reminder_1h_subject' => 'Appointment Starting Soon',
        'doctor_reminder_1h_line1' => 'Your appointment is starting in about 1 hour. Please prepare for the consultation.',
        'doctor_reminder_footer' => 'Please review the patient details and prepare for the consultation.',

        // New Booking (Doctor)
        'new_booking_subject' => 'New Appointment Booking Received',
        'new_booking_line1' => 'You have received a new appointment booking. Please review the details below:',
        'new_booking_details' => 'Patient: :patient | Date: :date | Time: :time | Duration: :duration min',
        'new_booking_footer' => 'Please review the booking and prepare for the consultation.',

        // Common Labels
        'appointment_details' => 'Appointment Details',
        'patient_details' => 'Patient Information',
        'doctor_label' => 'Doctor',
        'specialty_label' => 'Specialty',
        'date_label' => 'Date',
        'time_label' => 'Time',
        'duration_label' => 'Duration',
        'fee_label' => 'Consultation Fee',
        'status_label' => 'Status',
        'patient_name_label' => 'Patient Name',
        'phone_label' => 'Phone',
        'email_label' => 'Email',
        'your_notes' => 'Your Notes',
        'patient_notes' => 'Patient Notes',
        'view_booking' => 'View Booking Details',
    ],

    // Notifications (Database)
    'notification' => [
        'booking_created' => 'Your appointment has been scheduled successfully.',
        'booking_confirmed' => 'Your booking has been confirmed. Video consultation link is ready.',
        'booking_cancelled' => 'Your booking has been cancelled.',
        'new_booking' => 'You have received a new appointment booking.',
        'reminder_24h' => 'Reminder: Your appointment is tomorrow. Don\'t forget to join on time!',
        'reminder_1h' => 'Your appointment starts in 1 hour. Please prepare to join the consultation.',
        'doctor_reminder_24h' => 'Reminder: You have an appointment scheduled for tomorrow.',
        'doctor_reminder_1h' => 'Your appointment starts in 1 hour. Please prepare for the consultation.',
    ],

    // Push notifications (Firebase)
    'push' => [
        // Patient notifications
        'booking_created_title' => 'Appointment Scheduled',
        'booking_created_body' => 'Your appointment with Dr. :doctor on :date at :time has been scheduled.',
        'booking_confirmed_title' => 'Booking Confirmed',
        'booking_confirmed_body' => 'Your appointment with Dr. :doctor on :date at :time is confirmed.',

        // Doctor notifications
        'new_booking_title' => 'New Appointment',
        'new_booking_body' => ':patient has booked an appointment on :date at :time.',

        // Patient reminders
        'reminder_24h_title' => 'Appointment Tomorrow',
        'reminder_24h_body' => 'Reminder: Your appointment with Dr. :doctor is tomorrow at :time.',
        'reminder_1h_title' => 'Appointment in 1 Hour',
        'reminder_1h_body' => 'Your appointment with Dr. :doctor starts in 1 hour. Get ready!',

        // Doctor reminders
        'doctor_reminder_24h_title' => 'Appointment Tomorrow',
        'doctor_reminder_24h_body' => 'Reminder: Appointment with :patient tomorrow at :time.',
        'doctor_reminder_1h_title' => 'Appointment in 1 Hour',
        'doctor_reminder_1h_body' => 'Your appointment with :patient starts in 1 hour.',
    ],
];
