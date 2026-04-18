<?php

namespace Modules\Messaging\Channels;

use InvalidArgumentException;
use Modules\Messaging\Contracts\ChannelInterface;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;

class ChannelFactory
{
    /**
     * Channel implementation class map.
     */
    protected static array $channelMap = [
        'whatsapp' => WhatsAppChannel::class,
        'telegram' => TelegramChannel::class,
        'webchat' => WebChatChannel::class,
    ];

    /**
     * Create a channel instance from a Channel model.
     */
    public static function make(Channel $channel): ChannelInterface
    {
        $type = $channel->type->value;

        if (! isset(self::$channelMap[$type])) {
            throw new InvalidArgumentException("Unknown channel type: {$type}");
        }

        $class = self::$channelMap[$type];

        return new $class($channel);
    }

    /**
     * Create a channel instance from a channel type enum.
     */
    public static function makeFromType(ChannelTypeEnum $type): ChannelInterface
    {
        $channel = Channel::where('type', $type)->first();

        if (! $channel) {
            throw new InvalidArgumentException("Channel not found for type: {$type->value}");
        }

        return self::make($channel);
    }

    /**
     * Get all available channel instances.
     */
    public static function all(): array
    {
        $channels = [];

        foreach (Channel::active()->get() as $channel) {
            try {
                $channels[$channel->type->value] = self::make($channel);
            } catch (InvalidArgumentException $e) {
                // Skip unknown channel types
                continue;
            }
        }

        return $channels;
    }

    /**
     * Get all configured channel instances.
     */
    public static function configured(): array
    {
        return array_filter(self::all(), fn (ChannelInterface $channel) => $channel->isConfigured());
    }

    /**
     * Register a custom channel implementation.
     */
    public static function register(string $type, string $class): void
    {
        if (! is_subclass_of($class, ChannelInterface::class)) {
            throw new InvalidArgumentException('Channel class must implement ChannelInterface');
        }

        self::$channelMap[$type] = $class;
    }

    /**
     * Check if a channel type is registered.
     */
    public static function has(string $type): bool
    {
        return isset(self::$channelMap[$type]);
    }
}
