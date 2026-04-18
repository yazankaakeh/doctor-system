<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Booking\Models\Booking;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;
use Modules\Messaging\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports for the Messaging module.
|
*/

// Conversation channel - for real-time message updates
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    // Check if user is a Doctor
    if ($user instanceof Doctor) {
        // Check if this conversation belongs to a booking for this doctor
        if ($conversation->conversable_type === Booking::class) {
            $booking = $conversation->conversable;
            return $booking && $booking->doctor_id === $user->id;
        }
        // Check metadata for doctor_id
        $metadata = $conversation->metadata ?? [];
        return isset($metadata['doctor_id']) && $metadata['doctor_id'] === $user->id;
    }

    // Check if user is a Patient
    if ($user instanceof Patient) {
        // Check if this conversation belongs to a booking for this patient
        if ($conversation->conversable_type === Booking::class) {
            $booking = $conversation->conversable;
            return $booking && $booking->patient_id === $user->id;
        }
        // Check metadata for patient_id
        $metadata = $conversation->metadata ?? [];
        return isset($metadata['patient_id']) && $metadata['patient_id'] === $user->id;
    }

    // Admins can access all conversations
    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
        return true;
    }

    // Assigned users can access their conversations
    if ($conversation->assigned_user_id === $user->id) {
        return true;
    }

    // User is the participant (by phone or email)
    if (isset($user->full_mobile) && $conversation->participant_identifier === $user->full_mobile) {
        return true;
    }
    if (isset($user->email) && $conversation->participant_identifier === $user->email) {
        return true;
    }

    // Team leaders can access their team's conversations
    if (is_object($user) && method_exists($user, 'isTeamLeader') && $user->isTeamLeader()) {
        $teamMemberIds = $user->getTeamMemberIds();
        if (in_array($conversation->assigned_user_id, $teamMemberIds)) {
            return true;
        }
    }

    return false;
});

// Agent channel - for agent-specific notifications
Broadcast::channel('agent.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Doctor channel - for doctor-specific notifications
Broadcast::channel('doctor.{doctorId}', function ($user, $doctorId) {
    if ($user instanceof Doctor) {
        return (int) $user->id === (int) $doctorId;
    }
    return false;
});

// Patient channel - for patient-specific notifications
Broadcast::channel('patient.{patientId}', function ($user, $patientId) {
    if ($user instanceof Patient) {
        return (int) $user->id === (int) $patientId;
    }
    return false;
});

// Messaging admins channel - for all admin notifications
Broadcast::channel('messaging.admins', function ($user) {
    return method_exists($user, 'isAdmin') && $user->isAdmin();
});

// Unassigned conversations channel
Broadcast::channel('messaging.unassigned', function ($user) {
    return method_exists($user, 'isAdmin') && $user->isAdmin();
});
