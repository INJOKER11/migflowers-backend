<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function sendMessage($message): ?int
    {
        try {
            $response = Http::post('https://api.telegram.org/bot' . config('services.telegram.token') . '/sendMessage', [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $message,
                'parse_mode' => 'HTML',
            ])->throw();

            return $response->json('result.message_id');
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function updateMessage($message, $messageId): void
    {
        try {
            Http::post('https://api.telegram.org/bot' . config('services.telegram.token') . '/editMessageText', [
                'chat_id' => config('services.telegram.chat_id'),
                'message_id' => $messageId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ])->throw();
        } catch (\Throwable $e) {
            report($e);
        }

    }
}
