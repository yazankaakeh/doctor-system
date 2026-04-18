<?php

namespace Modules\Messaging\DataTransferObjects;

use Modules\Messaging\Enums\ChannelTypeEnum;

class WebhookPayloadDTO
{
    public function __construct(
        public readonly ChannelTypeEnum $channelType,
        public readonly array $payload,
        public readonly array $headers = [],
        public readonly ?string $rawBody = null,
    ) {}

    public static function make(
        ChannelTypeEnum $channelType,
        array $payload,
        array $headers = [],
        ?string $rawBody = null,
    ): self {
        return new self(
            channelType: $channelType,
            payload: $payload,
            headers: $headers,
            rawBody: $rawBody,
        );
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }

    public function has(string $key): bool
    {
        return data_get($this->payload, $key) !== null;
    }

    public function getHeader(string $key, $default = null)
    {
        // Headers are case-insensitive
        $key = strtolower($key);

        foreach ($this->headers as $headerKey => $value) {
            if (strtolower($headerKey) === $key) {
                return is_array($value) ? ($value[0] ?? $default) : $value;
            }
        }

        return $default;
    }

    public function hasHeader(string $key): bool
    {
        return $this->getHeader($key) !== null;
    }

    public function toArray(): array
    {
        return [
            'channel_type' => $this->channelType->value,
            'payload' => $this->payload,
            'headers' => $this->headers,
        ];
    }
}
