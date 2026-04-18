<?php

namespace Modules\Core\Actions\Video;

use Modules\Core\Contracts\VideoServiceInterface;
use Modules\Core\DataTransferObjects\MeetingParticipant;

class GetMeetingConfigAction
{
    public function __construct(
        protected VideoServiceInterface $videoService
    ) {}

    /**
     * Get the meeting configuration for embedding.
     */
    public function handle(string $roomName, MeetingParticipant $participant): array
    {
        $embedConfig = $this->videoService->getEmbedConfig($roomName, $participant);

        return [
            'domain' => $this->videoService->getDomain(),
            'provider' => $this->videoService->getProviderName(),
            'roomName' => $roomName,
            'roomUrl' => $this->videoService->getRoomUrl($roomName, $participant),
            'participant' => $participant->toArray(),
            'embedConfig' => $embedConfig,
            'isActive' => $this->videoService->isRoomActive($roomName),
        ];
    }

    /**
     * Get configuration for a doctor.
     */
    public function forDoctor(string $roomName, object $doctor): array
    {
        $participant = MeetingParticipant::doctor(
            id: (string) $doctor->id,
            name: 'Dr. '.$doctor->name,
            email: $doctor->email,
            avatar: $doctor->avatar ?? null,
        );

        return $this->handle($roomName, $participant);
    }

    /**
     * Get configuration for a patient.
     */
    public function forPatient(string $roomName, object $patient): array
    {
        $participant = MeetingParticipant::patient(
            id: (string) $patient->id,
            name: $patient->name,
            email: $patient->email,
            avatar: $patient->avatar ?? null,
        );

        return $this->handle($roomName, $participant);
    }
}
