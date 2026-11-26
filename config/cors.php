<?php

return [
    'paths' => ['users', 'users/*', 'api', 'api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map('trim', explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:8000,https://softmex.xyz',
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
