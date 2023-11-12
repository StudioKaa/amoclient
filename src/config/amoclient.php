<?php

return [
    'client_id' => env('AMO_CLIENT_ID', null),
    'client_secret' => env('AMO_CLIENT_SECRET', null),
    'app_for' => env('AMO_APP_FOR', 'teachers'),
    'use_migration' => env('AMO_USE_MIGRATION', 'yes'),
    'api_log' => env('AMO_API_LOG', 'no'),
    'ssl_verify_peer' => env('AMO_SSL_VERIFYPEER', 'yes'),
];
