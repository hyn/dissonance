<?php

return [
    'debug' => env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'utc'),
    'log' => env('APP_LOG', 'single'),
    'log_level' => env('APP_DEBUG', false) ? 'debug' : 'info',
    'providers' => [
        Dissonance\Providers\DiscordProvider::class,
    ],
];
