<?php

use DefStudio\Telegraph\Telegraph;

return [
    'token' => env('TELEGRAM_TOKEN','token'),
    'api_endpoint' => env('TELEGRAM_API_ENDPOINT','https://api.telegram.org'),
];
