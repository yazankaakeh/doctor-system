<?php

namespace Modules\Messaging\DataTransferObjects;

use Illuminate\Database\Eloquent\Model;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationPriorityEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Models\Channel;

class CreateConversationDTO
{
    public function __construct(
        public readonly ChannelTypeEnum|Channel|int $channel,
        public readonly string $participantIdentifier,
        public readonly ?string $participantName = null,
        public readonly ?Model $conversable = null,
        public readonly ?int $assignedUserId = null,
        public readonly ConversationStatusEnum $status = ConversationStatusEnum::OPEN,
        public readonly ConversationPriorityEnum $priority = ConversationPriorityEnum::NORMAL,
        public readonly ?string $externalConversationId = null,
        public readonly array $metadata = [],
    ) {}

    public static function make(
        ChannelTypeEnum|Channel|int $channel,
        string $participantIdentifier,
        ?string $participantName = null,
        ?Model $conversable = null,
        ?int $assignedUserId = null,
        ConversationStatusEnum $status = ConversationStatusEnum::OPEN,
        ConversationPriorityEnum $priority = ConversationPriorityEnum::NORMAL,
        ?string $externalConversationId = null,
        array $metadata = [],
    ): self {
        return new self(
            channel: $channel,
            participantIdentifier: $participantIdentifier,
            participantName: $participantName,
            conversable: $conversable,
            assignedUserId: $assignedUserId,
            status: $status,
            priority: $priority,
            externalConversationId: $externalConversationId,
            metadata: $metadata,
        );
    }

    /**
     * Create from an inbound message DTO.
     */
    public static function fromInbound(InboundMessageDTO $inbound, Channel $channel): self
    {
        return new self(
            channel: $channel,
            participantIdentifier: $inbound->participantIdentifier,
            participantName: $inbound->participantName,
            externalConversationId: $inbound->externalConversationId,
            metadata: $inbound->metadata,
        );
    }

    public function getChannelId(): int
    {
        if ($this->channel instanceof Channel) {
            return $this->channel->id;
        }

        if ($this->channel instanceof ChannelTypeEnum) {
            return Channel::where('type', $this->channel)->firstOrFail()->id;
        }

        return $this->channel;
    }

    public function toArray(): array
    {
        $data = [
            'channel_id' => $this->getChannelId(),
            'participant_identifier' => $this->participantIdentifier,
            'participant_name' => $this->participantName,
            'assigned_user_id' => $this->assignedUserId,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'external_conversation_id' => $this->externalConversationId,
            'metadata' => $this->metadata,
        ];

        if ($this->conversable) {
            $data['conversable_type'] = get_class($this->conversable);
            $data['conversable_id'] = $this->conversable->getKey();
        }

        return $data;
    }
}
