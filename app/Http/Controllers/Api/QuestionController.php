<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Services\TelegramService;

class QuestionController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
    ) {}

    public function store(StoreQuestionRequest $request)
    {
        $data = $request->validated();

        $message = "❓ <b>Нове питання</b>\n\n"
            . '👤 ' . e($data['name']) . "\n"
            . '📞 ' . e($data['contact']) . "\n"
            . (filled($data['order_number'] ?? null) ? '🧾 Замовлення: #' . e($data['order_number']) . "\n" : '')
            . "\n💬 " . e($data['question']);

        $this->telegram->sendMessage($message);

        return response()->noContent();
    }
}
