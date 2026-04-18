<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Video Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "jitsi", "daily", "custom"
    |
    */
    'default' => env('VIDEO_PROVIDER', 'jitsi'),

    /*
    |--------------------------------------------------------------------------
    | Video Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'jitsi' => [
            'domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
            'app_id' => env('JITSI_APP_ID'),
            'secret' => env('JITSI_SECRET'),
            'self_hosted' => env('JITSI_SELF_HOSTED', false),
        ],

        'daily' => [
            'domain' => env('DAILY_DOMAIN'),
            'api_key' => env('DAILY_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Room Settings
    |--------------------------------------------------------------------------
    */
    'room' => [
        'prefix' => env('VIDEO_ROOM_PREFIX', 'MedConsult'),
        // Lobby requires JWT authentication - set to false for public Jitsi
        'lobby_enabled' => env('VIDEO_LOBBY_ENABLED', false),
        'password_protected' => env('VIDEO_PASSWORD_PROTECTED', false),
        'recording_enabled' => env('VIDEO_RECORDING_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Meeting Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'start_with_audio_muted' => false,
        'start_with_video_muted' => false,
        'enable_screen_sharing' => true,
        'enable_chat' => true,
        'enable_raise_hand' => true,
        'enable_tile_view' => true,
        'max_participants' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'show_watermark' => false,
        'show_brand_watermark' => false,
        'toolbar_buttons' => [
            'microphone',
            'camera',
            'desktop',
            'fullscreen',
            'hangup',
            'chat',
            'settings',
            'videoquality',
            'tileview',
        ],
        'theme' => 'dark', // 'dark' or 'light'
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'require_display_name' => true,
        'disable_deep_linking' => true,
        // Lobby/prejoin requires JWT authentication - disable for public Jitsi
        'enable_lobby' => false,
        'enable_password' => false,
    ],
];
