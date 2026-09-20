<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class VeraRsvpController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('vera_rsvp.bot_token');
        $chat = (string) config('vera_rsvp.chat_id');
        $secret = (string) config('vera_rsvp.invitation_key');
        if (! config('vera_rsvp.enabled') || $token === '' || $chat === '' || (config('vera_rsvp.require_invitation_key') && strlen($secret) < 32)) {
            return $this->error('not_configured', 503);
        }

        $cache = Cache::store(config('vera_rsvp.cache_store', 'file'));
        $limiter = new RateLimiter($cache);
        $ipKey = 'vera-rsvp:ip:'.hash('sha256', (string) $request->ip());
        if ($limiter->tooManyAttempts($ipKey, 5)) {
            return $this->error('rate_limited', 429)->header('Retry-After', $limiter->availableIn($ipKey));
        }
        $limiter->hit($ipKey, 60);

        if (config('vera_rsvp.require_invitation_key') && ! hash_equals($secret, (string) $request->header('X-Vera-Invitation'))) {
            return $this->error('invalid_invitation', 403);
        }

        $validator = Validator::make($request->all(), [
            'request_id' => ['required', 'uuid'],
            'activities' => ['present', 'array', 'max:4'],
            'activities.*' => ['required', 'string', 'distinct', Rule::in([
                'Посмотреть Инстаграм', 'Посмотреть «Беременна в 16»',
                'Просто поговорить', 'Поиграть во что-то',
                'Посмотреть YouTube', 'Посмотреть фильм', 'Посмотреть сериал', 'Выбрать вместе',
            ])],
            'idea' => ['nullable', 'string', 'max:180'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);
        if ($validator->fails()) {
            return response()->json(['ok' => false, 'error' => 'validation_failed', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $payload = [
            'activities' => $data['activities'],
            'idea' => trim($data['idea'] ?? ''),
            'date' => $data['date'] ?? null,
            'time' => $data['time'] ?? null,
            'timezone' => $data['timezone'],
        ];
        sort($payload['activities']);
        $fingerprint = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $key = 'vera-rsvp:receipt:'.hash('sha256', $secret.'|'.strtolower($data['request_id']));
        $lock = $cache->lock($key.':lock', 30);
        if (! $lock->get()) {
            return $this->error('in_progress', 409)->header('Retry-After', 3);
        }

        try {
            $previous = $cache->get($key);
            if ($previous !== null) {
                if (! hash_equals($previous['fingerprint'], $fingerprint)) {
                    return $this->error('request_id_conflict', 409);
                }
                if ($previous['state'] === 'sent') {
                    return response()->json(['ok' => true, 'duplicate' => true]);
                }
                // Telegram has no idempotency key. Never resend an uncertain delivery automatically.
                return $this->error('delivery_unknown', 409);
            }

            if ($payload['date']) {
                $now = CarbonImmutable::now($payload['timezone']);
                if ($payload['date'] < $now->format('Y-m-d')) {
                    return $this->error('date_in_past', 422);
                }
                if ($payload['time']) {
                    $local = $payload['date'].' '.$payload['time'];
                    $instant = CarbonImmutable::createFromFormat('!Y-m-d H:i', $local, $payload['timezone']);
                    if ($instant->format('Y-m-d H:i') !== $local || $instant->lessThanOrEqualTo($now)) {
                        return $this->error('time_invalid_or_in_past', 422);
                    }
                }
            }

            $globalKey = 'vera-rsvp:send:'.hash('sha256', $secret);
            if ($limiter->tooManyAttempts($globalKey, 10)) {
                return $this->error('rate_limited', 429)->header('Retry-After', $limiter->availableIn($globalKey));
            }
            $limiter->hit($globalKey, 3600);

            // Persist before sending: a timeout/crash must not silently cause a duplicate message.
            $receipt = ['fingerprint' => $fingerprint, 'state' => 'sending'];
            if (! $cache->put($key, $receipt, 86400)) {
                return $this->error('temporarily_unavailable', 503);
            }
            try {
                $result = Http::asJson()->connectTimeout(3)->timeout(10)->post(
                    'https://api.telegram.org/bot'.$token.'/sendMessage',
                    [
                        'chat_id' => $chat,
                        'text' => $this->message($payload),
                        'link_preview_options' => ['is_disabled' => true],
                    ]
                );
            } catch (Throwable) {
                // Do not log the exception: its URL may contain the bot token.
                return $this->error('delivery_unknown', 409);
            }

            if ($result->successful() && $result->json('ok') === true && is_int($result->json('result.message_id'))) {
                $receipt['state'] = 'sent';
                $cache->put($key, $receipt, 86400);

                return response()->json(['ok' => true, 'duplicate' => false]);
            }
            if ($result->json('ok') === false) {
                $cache->forget($key);

                return $this->error('telegram_unavailable', 502);
            }

            return $this->error('delivery_unknown', 409);
        } finally {
            $lock->release();
        }
    }

    private function message(array $payload): string
    {
        $date = $payload['date']
            ? CarbonImmutable::createFromFormat('!Y-m-d', $payload['date'])->format('d.m.Y')
            : 'Дату выберем вместе';
        $time = $payload['time'] ?: 'Время уточним';
        $activities = $payload['activities'] ? implode(', ', $payload['activities']) : 'Решим вместе';

        // Plain text only: user input cannot inject Telegram HTML or Markdown.
        return "🍿 Верочка согласилась посмотреть вместе!\n\n"
            ."📅 {$date}\n🕐 {$time} ({$payload['timezone']})\n"
            ."✨ План: {$activities}\n"
            .($payload['idea'] !== '' ? "💬 Её пожелание: {$payload['idea']}\n" : '')
            ."\n💐 Главное — чтобы с тобой!";
    }

    private function error(string $code, int $status): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $code], $status);
    }
}
