<?php

return [
    'provider' => env('WA_PROVIDER', 'fonnte'),
    'api_key' => env('WA_API_KEY', ''),
    'admin_number' => env('WA_ADMIN_NUMBER', ''),
    'sender' => env('WA_SENDER', ''),

    'fonnte_url' => env('WA_FONNTE_URL', 'https://api.fonnte.com/send'),
];
