<?php

return [
    'name' => 'Messaging',

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'messages_per_page' => 50,
        'conversations_per_page' => 20,
        'max_attachment_size' => 10 * 1024 * 1024, // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration (Meta Cloud API)
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'api_version' => env('MESSAGING_WHATSAPP_API_VERSION', 'v18.0'),
        'phone_number_id' => env('MESSAGING_WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('MESSAGING_WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('MESSAGING_WHATSAPP_ACCESS_TOKEN'),
        'verify_token' => env('MESSAGING_WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('MESSAGING_WHATSAPP_APP_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Configuration
    |--------------------------------------------------------------------------
    */
    'telegram' => [
        'bot_token' => env('MESSAGING_TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('MESSAGING_TELEGRAM_BOT_USERNAME'),
        'webhook_secret' => env('MESSAGING_TELEGRAM_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebChat Configuration
    |--------------------------------------------------------------------------
    */
    'webchat' => [
        'enabled' => env('MESSAGING_WEBCHAT_ENABLED', true),
        'widget_color' => env('MESSAGING_WEBCHAT_WIDGET_COLOR', '#6366f1'),
        'welcome_message' => env('MESSAGING_WEBCHAT_WELCOME_MESSAGE', 'Hello! How can we help you today?'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-time Broadcasting
    |--------------------------------------------------------------------------
    */
    'broadcasting' => [
        'connection' => env('BROADCAST_CONNECTION', 'reverb'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Retry Settings
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => 3,
        'retry_after_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'verify_signatures' => env('MESSAGING_VERIFY_WEBHOOK_SIGNATURES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('MESSAGING_STORAGE_DISK', 'public'),
        'path' => 'messaging/attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types for Attachments
    |--------------------------------------------------------------------------
    */
    'allowed_attachments' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'video' => ['mp4', 'mov', 'avi', 'webm'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Assignment Settings
    |--------------------------------------------------------------------------
    */
    'assignment' => [
        'enabled' => env('MESSAGING_AUTO_ASSIGNMENT', true),
        'strategy' => env('MESSAGING_ASSIGNMENT_STRATEGY', 'round_robin'), // round_robin, least_busy, random, manual
    ],

    /*
    |--------------------------------------------------------------------------
    | Chatbot Settings
    |--------------------------------------------------------------------------
    */
    'chatbot' => [
        'enabled' => env('MESSAGING_CHATBOT_ENABLED', false),
        'greeting' => env('MESSAGING_CHATBOT_GREETING', 'Hello! How can I help you today?'),
        'fallback' => env('MESSAGING_CHATBOT_FALLBACK', "I'm sorry, I didn't understand that. Please type 'menu' for options or wait for an agent."),
        'transfer_keywords' => ['agent', 'human', 'help', 'support', 'person'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'enabled' => env('MESSAGING_RATE_LIMIT_ENABLED', true),
        'per_conversation_per_minute' => 80,
        'per_channel_per_minute' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Deduplication
    |--------------------------------------------------------------------------
    */
    'deduplication' => [
        'enabled' => env('MESSAGING_DEDUPLICATION_ENABLED', true),
        'ttl_seconds' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Auto-Linking
    |--------------------------------------------------------------------------
    */
    'contact_linking' => [
        'enabled' => env('MESSAGING_CONTACT_LINKING_ENABLED', true),
        'create_lead_if_not_found' => env('MESSAGING_CREATE_LEAD', false),
    ],
];
