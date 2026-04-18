<?php

namespace Modules\MCP\Services\AI;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService implements AIServiceInterface
{
    protected string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('mcp.openai.api_key');
        $this->model = config('mcp.openai.model');
        $this->maxTokens = config('mcp.openai.max_tokens');
        $this->temperature = config('mcp.openai.temperature');
    }

    public function generateResponse(string $userMessage, array $context = []): array
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt($context)],
            ];

            $messages = array_merge(
                $messages,
                $this->buildContext($context['messages'] ?? [], $context['knowledge'] ?? [])
            );

            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Failed to get response from OpenAI API');
            }

            $data = $response->json();

            return [
                'message' => $data['choices'][0]['message']['content'] ?? '',
                'model' => $data['model'] ?? $this->model,
                'tokens_used' => $data['usage']['completion_tokens'] ?? 0,
                'metadata' => [
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'total_tokens' => $data['usage']['total_tokens'] ?? 0,
                    'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('OpenAI Service Error', [
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
                'role' => 'system',
                'content' => "Business Knowledge Base:\n\n{$knowledgeText}",
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
