<?php

// config for ItsRafsanJani/Bkash
return [
    'sandbox' => env('BKASH_SANDBOX', true),

    'app_key' => env('BKASH_APP_KEY', ''),
    'app_secret' => env('BKASH_APP_SECRET', ''),
    'username' => env('BKASH_USERNAME', ''),
    'password' => env('BKASH_PASSWORD', ''),

    'callbackURL' => env('BKASH_CALLBACK_URL', 'http://127.0.0.1:8000/bkash/callback'),
    'timezone' => 'Asia/Dhaka',
];
