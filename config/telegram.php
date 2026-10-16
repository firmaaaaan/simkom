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

    'timeout' => (int) env('TELEGRAM_TIMEOUT', 5),

    'parse_mode' => 'HTML',
];
