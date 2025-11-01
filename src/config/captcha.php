<?php

return [

    'type' => env('CAPTCHA_TYPE', 'math'), // Options: math, word, image

    'expires_minutes' => env('CAPTCHA_EXPIRES_MINUTES', 10),
    'max_attempts' => env('CAPTCHA_MAX_ATTEMPTS', 5),
    'case_sensitive' => env('CAPTCHA_CASE_SENSITIVE', true),

    'allowed_chars' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
    'length' => [
        'word' => 6, //replace with your length for word types
        'image' => 5, //replace with your length for image types
    ],

    'fonts' => [
        base_path('vendor/justchill/laravel-captcha/src/fonts/Roboto-Bold.ttf'),
    ],

    'image' => [
        'width' => 150,
        'height' => 50,
        'font_size' => 24,
        'bg_color' => '#ffffff',
        'text_color' => '#000000',
        'noise' => true,
        'lines' => 3,
    ],

    'math_difficulty' => 'easy',

];
