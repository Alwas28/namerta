<?php
return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    ],
    'fallback' => [
        'enabled' => env('AI_FALLBACK_ENABLED', true),
        'response_delay' => env('AI_FALLBACK_DELAY', 1500),
    ],
    'cache_enabled' => env('AI_CACHE_ENABLED', false),
    'check_api_health' => env('AI_CHECK_API_HEALTH', false),
];