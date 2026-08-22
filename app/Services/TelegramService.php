<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function sendMessage($message): void
    {
        try {
            Http::post('https://api.telegram.org/bot' . config('services.telegram.token') . '/sendMessage', [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $message,
                'parse_mode' => 'HTML',
            ])->throw();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
