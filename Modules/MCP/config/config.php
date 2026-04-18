<?php

return [
    'name' => 'MCP',

    // AI Provider Configuration
    'ai_provider' => env('MCP_AI_PROVIDER', 'anthropic'), // anthropic, openai

    // Anthropic Configuration
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 4096),
        'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
    ],

    // OpenAI Configuration
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2048),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    // Chat Configuration
    'chat' => [
        'session_timeout' => env('MCP_SESSION_TIMEOUT', 30), // minutes
        'max_messages_per_session' => env('MCP_MAX_MESSAGES', 100),
        'enable_guest_chat' => env('MCP_ENABLE_GUEST_CHAT', true),
        'rate_limit' => env('MCP_RATE_LIMIT', 20), // requests per minute
    ],

    // Knowledge Base Configuration
    'knowledge_base' => [
        'search_limit' => env('MCP_KNOWLEDGE_SEARCH_LIMIT', 5),
        'relevance_threshold' => env('MCP_RELEVANCE_THRESHOLD', 0.7),
        'cache_ttl' => env('MCP_KNOWLEDGE_CACHE_TTL', 3600), // seconds
    ],

    // System Prompts
    'system_prompts' => [
        'default' => 'You are a helpful AI assistant for this application. Provide accurate, professional, and friendly responses. Always maintain user privacy and data security.',
        'greeting' => 'Hello! I am your AI assistant. How can I help you today?',
        'fallback' => 'I apologize, but I don\'t have enough information to answer that question accurately. Would you like to speak with a human representative?',
    ],

    // Context Configuration
    'context' => [
        'include_knowledge_base' => true,
        'include_conversation_history' => true,
        'max_context_messages' => 10,
    ],
];
