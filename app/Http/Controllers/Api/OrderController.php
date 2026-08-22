<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private TelegramService $telegram
    ){}
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $lineItems = collect($validated['items'])->map(function ($item) {
            $product = Product::findOrFail($item['product_id']);
//            if ($product->stock < $item['quantity']) {
//                return response()->json(['message' => __('orders.insufficient_stock', ['product' => $product->name, 'stock' => $product->stock])], 422);
//            }
            return [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->discount_price ?? $product->price,
            ];
        });
        $cardFee = ($validated['with_card'] ?? false) ? 30 : 0;
        $itemsTotal = $lineItems->sum(fn ($item) => $item['unit_price'] * $item['quantity']);

        $deliveryFee = 0;
        if ($validated['delivery_method'] === 'delivery') {
            $deliveryFee = District::findOrFail($validated['district_id'])->price_for_delivery ?? 0;
        }

        $total = $itemsTotal + $deliveryFee + $cardFee;

        $order = DB::transaction(function () use ($validated, $lineItems, $total, $deliveryFee, $cardFee) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'total_amount' => $total,
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'district_id' => $validated['district_id'] ?? null,
                'delivery_method' => $validated['delivery_method'],
                'delivery_fee' => $deliveryFee,
                'card_fee' => $cardFee,
                'recipient_name' => $validated['recipient_name'] ?? null,
                'card_message' => $validated['card_message'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);

            foreach ($lineItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['unit_price'],
                ]);
//                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        $order->load('items.product', 'district');

        $items = $order->items
            ->map(fn ($item) => '• ' . e($item->product->name) . " × {$item->quantity}")
            ->implode("\n");

        $deliveryLines = $order->delivery_method === 'delivery'
            ? "📅 Дата: <b>{$order->delivery_date->format('d.m.Y')}</b>\n"
                . '📍 ' . e($order->delivery_address) . "\n"
                . ($order->district ? '🏘 Район: ' . e($order->district->name) . "\n" : '')
            : "🏃 Самовивіз\n";

        $paymentMethodLabels = [
            'online' => 'Оплата онлайн',
            'on_site' => 'Оплата на місці',
            'card' => 'Оплата карткою',
        ];
        $paymentMethodLabel = $paymentMethodLabels[$order->payment_method] ?? $order->payment_method;
        $isPaid = filled($order->payment_reference);

        $text = "🌸 <b>Нове замовлення</b>\n\n"
            . $deliveryLines
            . '👤 ' . e($order->customer_name) . "\n"
            . '📞 ' . e($order->customer_phone) . "\n"
            . '✉️ ' . e($order->customer_email) . "\n\n"
            . "<b>Товари:</b>\n{$items}\n\n"
            . ($order->card_fee > 0 ? "🎴 Листівка: +{$order->card_fee} ₴\n" : '')
            . ($order->card_message ? '💌 Текст листівки: ' . e($order->card_message) . "\n" : '')
            . ($order->delivery_fee > 0 ? "🚚 Доставка: +{$order->delivery_fee} ₴\n" : '')
            . "💰 Сума: <b>{$order->total_amount} ₴</b>\n"
            . '💳 ' . $paymentMethodLabel . "\n"
            . ($isPaid ? "✅ Оплачено\n" : "❌ Не оплачено\n");

        $this->telegram->sendMessage($text);

        return new OrderResource($order);
    }
}
