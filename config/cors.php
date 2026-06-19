<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure CORS settings for your Laravel application.
    | This configuration is used by the fruitcake/laravel-cors
    | package for handling Cross-Origin requests.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 'allowed_origins' => [
    //     'http://localhost:3000',
    //     'http://localhost:5173',
    //     'http://localhost:5176',
    //     'http://127.0.0.1:3000',
    //     'http://127.0.0.1:5173',
    //     'http://127.0.0.1:5176',
    //     'http://127.0.0.1:8000'
    // ],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [
        // Add production domain patterns here
        // 'https://*.pabili.com',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
