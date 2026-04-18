<?php

namespace Modules\Core\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Core\Actions\Video\GetMeetingConfigAction;
use Modules\Core\DataTransferObjects\MeetingParticipant;
use Modules\Core\DataTransferObjects\ParticipantRole;

class VideoRoom extends Component
{
    // Room properties
    public string $roomName;
    public ?string $roomUrl = null;
    public ?string $meetingPassword = null;

    // Participant properties
    public string $participantId;
    public string $participantName;
    public string $participantEmail;
    public string $participantRole = 'participant';
    public ?string $participantAvatar = null;

    // UI State
    public bool $isJoined = false;
    public bool $isLoading = true;
    public bool $hasError = false;
    public ?string $errorMessage = null;
    public bool $showPreJoinScreen = true;
    public bool $cameraEnabled = true;
    public bool $micEnabled = true;

    // Booking context (optional)
    public ?int $bookingId = null;
    public ?string $doctorName = null;
    public ?string $patientName = null;
    public ?string $appointmentTime = null;
    public ?int $patientId = null;
    public ?int $medicalExaminationId = null;

    // Callbacks
    public ?string $onEndRedirectUrl = null;

    /**
     * Mount the component.
     */
    public function mount(
        string $roomName,
        string $participantId,
        string $participantName,
        string $participantEmail,
        string $participantRole = 'participant',
        ?string $participantAvatar = null,
        ?string $roomUrl = null,
        ?string $meetingPassword = null,
        ?int $bookingId = null,
        ?string $doctorName = null,
        ?string $patientName = null,
        ?string $appointmentTime = null,
        ?string $onEndRedirectUrl = null,
    ): void {
        $this->roomName = $roomName;
        $this->roomUrl = $roomUrl;
        $this->meetingPassword = $meetingPassword;
        $this->participantId = $participantId;
        $this->participantName = $participantName;
        $this->participantEmail = $participantEmail;
        $this->participantRole = $participantRole;
        $this->participantAvatar = $participantAvatar;
        $this->bookingId = $bookingId;
        $this->doctorName = $doctorName;
        $this->patientName = $patientName;
        $this->appointmentTime = $appointmentTime;
        $this->onEndRedirectUrl = $onEndRedirectUrl;

        // Load booking details if booking ID provided
        if ($bookingId && class_exists('\Modules\Booking\Models\Booking')) {
            $booking = \Modules\Booking\Models\Booking::with('patient')->find($bookingId);
            if ($booking) {
                $this->patientId = $booking->patient_id;

                // Check if medical examination exists
                if (class_exists('\Modules\Doctor\Models\MedicalExamination')) {
                    $examination = \Modules\Doctor\Models\MedicalExamination::where([
                        'patient_id' => $booking->patient_id,
                        'doctor_id' => $booking->doctor_id,
                    ])->latest()->first();

                    if ($examination) {
                        $this->medicalExaminationId = $examination->id;
                    }
                }
            }
        }

        $this->isLoading = false;
    }

    /**
     * Get the participant DTO.
     */
    #[Computed]
    public function participant(): MeetingParticipant
    {
        $role = $this->participantRole === 'moderator'
            ? ParticipantRole::MODERATOR
            : ParticipantRole::PARTICIPANT;

        return new MeetingParticipant(
            id: $this->participantId,
            name: $this->participantName,
            email: $this->participantEmail,
            role: $role,
            avatar: $this->participantAvatar,
        );
    }

    /**
     * Get the meeting configuration.
     */
    #[Computed]
    public function meetingConfig(): array
    {
        $action = app(GetMeetingConfigAction::class);

        return $action->handle($this->roomName, $this->participant);
    }

    /**
     * Toggle camera before joining.
     */
    public function toggleCamera(): void
    {
        $this->cameraEnabled = !$this->cameraEnabled;
    }

    /**
     * Toggle microphone before joining.
     */
    public function toggleMic(): void
    {
        $this->micEnabled = !$this->micEnabled;
    }

    /**
     * Join the meeting.
     */
    public function joinMeeting(): void
    {
        $this->showPreJoinScreen = false;
        $this->isJoined = true;

        $this->dispatch('meeting-joined', [
            'roomName' => $this->roomName,
            'participant' => $this->participant->toArray(),
        ]);
    }

    /**
     * Leave the meeting.
     */
    public function leaveMeeting(): void
    {
        $this->isJoined = false;
        $this->showPreJoinScreen = true;

        $this->dispatch('meeting-left', [
            'roomName' => $this->roomName,
        ]);
    }

    /**
     * Handle meeting end event from JavaScript.
     */
    #[On('meeting-ended')]
    public function onMeetingEnded(): void
    {
        $this->isJoined = false;

        if ($this->onEndRedirectUrl) {
            $this->redirect($this->onEndRedirectUrl);
        }
    }

    /**
     * Handle error event from JavaScript.
     */
    #[On('meeting-error')]
    public function onMeetingError(string $message): void
    {
        $this->hasError = true;
        $this->errorMessage = $message;
        $this->isJoined = false;
    }

    /**
     * Copy meeting link to clipboard.
     */
    public function copyMeetingLink(): void
    {
        $this->dispatch('copy-to-clipboard', ['text' => $this->roomUrl ?? $this->meetingConfig['roomUrl']]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('core::livewire.video-room');
    }
}
