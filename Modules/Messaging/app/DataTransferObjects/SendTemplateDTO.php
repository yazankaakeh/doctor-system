<?php

namespace Modules\Messaging\DataTransferObjects;

use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Template;

class SendTemplateDTO
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly Template $template,
        public readonly array $variables = [],
        public readonly ?int $senderId = null,
        public readonly ?string $headerMediaUrl = null,
        public readonly array $metadata = [],
    ) {}

    public static function make(
        Conversation $conversation,
        Template $template,
        array $variables = [],
        ?int $senderId = null,
        ?string $headerMediaUrl = null,
        array $metadata = [],
    ): self {
        return new self(
            conversation: $conversation,
            template: $template,
            variables: $variables,
            senderId: $senderId,
            headerMediaUrl: $headerMediaUrl,
            metadata: $metadata,
        );
    }

    public function getRecipientIdentifier(): string
    {
        return $this->conversation->participant_identifier;
    }

    public function getTemplateName(): string
    {
        return $this->template->name;
    }

    public function getTemplateLanguage(): string
    {
        return $this->template->language;
    }

    public function hasHeaderMedia(): bool
    {
        return $this->headerMediaUrl !== null;
    }

    public function getResolvedContent(): string
    {
        return $this->template->buildContent($this->variables);
    }

    public function toArray(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'template_id' => $this->template->id,
            'template_name' => $this->template->name,
            'template_language' => $this->template->language,
            'variables' => $this->variables,
            'sender_id' => $this->senderId,
            'header_media_url' => $this->headerMediaUrl,
            'metadata' => $this->metadata,
        ];
    }
}
