<?php

return [
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Makassar'),
    'business' => [
        'name' => env('BUSINESS_NAME', 'UMKM Pangkonan (Demo)'),
        'whatsapp' => env('BUSINESS_WHATSAPP'),
        'address' => env('BUSINESS_ADDRESS', 'Data alamat usaha belum dikonfirmasi'),
    ],
    'cod_enabled' => (bool) env('COD_ENABLED', true),
    'stock_reservation_minutes' => (int) env('STOCK_RESERVATION_MINUTES', 60),
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    ],
];
