<?php

namespace Modules\Core\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class MeetingRoom implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $provider,
        public readonly ?string $password = null,
        public readonly ?string $token = null,
        public readonly array $config = [],
        public readonly ?\DateTimeInterface $expiresAt = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            url: $data['url'],
            provider: $data['provider'],
            password: $data['password'] ?? null,
            token: $data['token'] ?? null,
            config: $data['config'] ?? [],
            expiresAt: isset($data['expires_at']) ? new \DateTimeImmutable($data['expires_at']) : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
            'provider' => $this->provider,
            'password' => $this->password,
            'token' => $this->token,
            'config' => $this->config,
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Serialize to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Check if room has password protection.
     */
    public function isPasswordProtected(): bool
    {
        return $this->password !== null;
    }

    /**
     * Check if room has expired.
     */
    public function hasExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new \DateTimeImmutable();
    }
}
