<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],
    
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
    
    'serp' => [
        'api_key' => env('SERP_API_KEY'),
    ],
    
    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),
    ],
];


