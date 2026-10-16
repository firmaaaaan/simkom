<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Notification
    |--------------------------------------------------------------------------
    |
    | Notifikasi lonceng (in-app) diteruskan ke Telegram lewat Bot API.
    | Kosongkan bot_token / chat_id untuk menonaktifkan pengiriman.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    'chat_id' => env('TELEGRAM_CHAT_ID'),

    // Timeout per percobaan (detik). Koneksi ke api.telegram.org yang lambat
    // butuh lebih dari 5 detik agar tidak timeout diam-diam.
    'timeout' => (int) env('TELEGRAM_TIMEOUT', 15),

    // Jumlah maksimal percobaan kirim (termasuk yang pertama).
    'max_attempts' => (int) env('TELEGRAM_MAX_ATTEMPTS', 2),

    // Jeda antar percobaan (milidetik).
    'retry_delay_ms' => (int) env('TELEGRAM_RETRY_DELAY_MS', 500),

    'parse_mode' => 'HTML',
];
