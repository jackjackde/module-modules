<?php

return [
    'skeleton' => 'https://github.com/jackjackde/laravel-module-skeleton/archive/main.zip',
    'middlewares' => [
        'api' => [
            'api',
            'auth:sanctum',
        ],
        'web' => [
            'web',
        ],
        'admin' => [
            'web',
            'web.admin.auth',
        ],
    ],

    'paths' => [
        'source' => 'views/Pages',
    ],
];
