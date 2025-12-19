<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram bot token from @BotFather
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Admin Chat ID
    |--------------------------------------------------------------------------
    |
    | The Telegram chat ID where notifications will be sent
    |
    */
    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'login' => [
            'enabled' => env('TELEGRAM_LOGIN_NOTIFICATIONS', true),
        ],
    ],
];
