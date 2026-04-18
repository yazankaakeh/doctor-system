<?php

namespace Modules\MCP\Services\AI;

use InvalidArgumentException;

class AIServiceFactory
{
    public static function make(?string $provider = null): AIServiceInterface
    {
        $provider = $provider ?? config('mcp.ai_provider');

        return match ($provider) {
            'anthropic' => new AnthropicService,
            'openai' => new OpenAIService,
            default => throw new InvalidArgumentException("Unsupported AI provider: {$provider}"),
        };
    }
}
