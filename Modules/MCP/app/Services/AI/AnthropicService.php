<?php

namespace Modules\MCP\Services\AI;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicService implements AIServiceInterface
{
    protected string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected string $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('mcp.anthropic.api_key');
        $this->model = config('mcp.anthropic.model');
        $this->maxTokens = config('mcp.anthropic.max_tokens');
        $this->temperature = config('mcp.anthropic.temperature');
    }

    public function generateResponse(string $userMessage, array $context = []): array
    {
        try {
            $messages = $this->buildContext($context['messages'] ?? [], $context['knowledge'] ?? []);
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'system' => $this->getSystemPrompt($context),
                'messages' => $messages,
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Failed to get response from Anthropic API');
            }

            $data = $response->json();

            return [
                'message' => $data['content'][0]['text'] ?? '',
                'model' => $data['model'] ?? $this->model,
                'tokens_used' => $data['usage']['output_tokens'] ?? 0,
                'metadata' => [
                    'input_tokens' => $data['usage']['input_tokens'] ?? 0,
                    'stop_reason' => $data['stop_reason'] ?? null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Anthropic Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'message' => config('mcp.system_prompts.fallback'),
                'model' => $this->model,
                'tokens_used' => 0,
                'metadata' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function buildContext(array $messages, array $knowledge = []): array
    {
        $contextMessages = [];

        // Add knowledge base context if available
        if (! empty($knowledge)) {
            $knowledgeText = $this->formatKnowledge($knowledge);
            $contextMessages[] = [
                'role' => 'assistant',
                'content' => "I have access to the following business knowledge:\n\n{$knowledgeText}",
            ];
        }

        // Add conversation history
        $maxContextMessages = config('mcp.context.max_context_messages', 10);
        $recentMessages = array_slice($messages, -$maxContextMessages);

        foreach ($recentMessages as $msg) {
            $contextMessages[] = [
                'role' => $msg['role'] ?? ($msg['is_from_bot'] ? 'assistant' : 'user'),
                'content' => $msg['message'] ?? $msg['content'],
            ];
        }

        return $contextMessages;
    }

    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    protected function getSystemPrompt(array $context): string
    {
        $systemPrompt = config('mcp.system_prompts.default');

        // Add custom context if provided
        if (isset($context['custom_prompt'])) {
            $systemPrompt .= "\n\n".$context['custom_prompt'];
        }

        return $systemPrompt;
    }

    protected function formatKnowledge(array $knowledge): string
    {
        $formatted = [];

        foreach ($knowledge as $item) {
            $question = is_array($item['question']) ? $item['question'][app()->getLocale()] ?? $item['question']['en'] : $item['question'];
            $answer = is_array($item['answer']) ? $item['answer'][app()->getLocale()] ?? $item['answer']['en'] : $item['answer'];

            $formatted[] = "Q: {$question}\nA: {$answer}";
        }

        return implode("\n\n", $formatted);
    }
}
