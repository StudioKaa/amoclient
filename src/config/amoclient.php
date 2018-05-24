<?php

return [
    'client_id' => env('AMO_CLIENT_ID', null),
    'client_secret' => env('AMO_CLIENT_SECRET', null),
    'app_for' => env('AMO_APP_FOR', 'teachers'),
    'use_migration' => env('AMO_USE_MIGRATION', true),
    'api_log' => env('AMO_API_LOG', false)
];
