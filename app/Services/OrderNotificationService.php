<?php

namespace App\Services;

use App\Models\Order;

class OrderNotificationService
{

    private const PAYMENT_METHOD_LABELS = [
        'online' => 'Оплата онлайн',
        'on_site' => 'Оплата на місці',
        'card' => 'Оплата карткою',
    ];

    public function __construct(
        private TelegramService $telegram,
    ){}

    public function notifyNewOrder(Order $order): void
    {
        $messageId = $this->telegram->sendMessage($this->buildMessage($order));

        if($messageId) {
            $order->update(['telegram_message_id' => $messageId]);
        }
    }

    public function notifyUpdatedOrder(Order $order): void
    {
        $text = $this->buildMessage($order);

        if($order->telegram_message_id) {
            $this->telegram->updateMessage($text, $order->telegram_message_id);

            return;
        }

        $this->notifyNewOrder($order);
    }

    public function buildMessage(Order $order): string
    {
       return "🌸 <b>Нове замовлення</b>\n\n"
            . '🧾 Замовлення: #' . e($order->order_number) . "\n\n"
            . $this->deliveryLines($order)
            . '👤 ' . e($order->customer_name) . "\n"
            . '📞 ' . e($order->customer_phone) . "\n"
            . '✉️ ' . e($order->customer_email) . "\n\n"
            . "<b>Товари:</b>\n{$this->itemLines($order)}\n\n"
            . ($order->card_fee > 0 ? "🎴 Листівка: +{$order->card_fee} ₴\n" : '')
            . ($order->card_message ? '💌 Текст листівки: ' . e($order->card_message) . "\n" : '')
            . ($order->delivery_fee > 0 ? "🚚 Доставка: +{$order->delivery_fee} ₴\n" : '')
            . ($order->discount_amount > 0 ? "🏷 Знижка: -{$order->discount_amount} ₴\n" : '')
            . "💰 Сума: <b>{$order->total_amount} ₴</b>\n"
            . '💳 ' . $this->paymentMethodLabel($order) . "\n"
            . ($this->isPaid($order) ? "✅ Оплачено\n" : "❌ Не оплачено\n");
    }

    public function isPaid(Order $order): bool
    {
        return filled($order->payment_reference);
    }
    public function paymentMethodLabel(Order $order): string
    {
        return self::PAYMENT_METHOD_LABELS[$order->payment_method] ?? $order->payment_method;

    }
    public function itemLines(Order $order): string
    {
        return $order->items
            ->map(fn ($item) => '• ' . e($item->product->getTranslation('name', 'uk')) . " × {$item->quantity}" . " * {$item->price_at_purchase}")
            ->implode("\n");
    }
    public function deliveryLines(Order $order): string
    {
        if($order->delivery_method !== 'delivery'){
            return "🏃 Самовивіз\n";
        }

       return "📅 Дата: <b>{$order->delivery_date->format('d.m.Y')}</b>\n"
            . '📍 ' . e($order->delivery_address) . "\n"
            . ($order->district ? '🏘 Район: ' . e($order->district->getTranslation('name', 'uk')) . "\n" : '');
    }
}
