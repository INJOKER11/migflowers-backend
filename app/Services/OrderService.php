<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderPricingService $pricing,
        private PromoCodeService $promoCodes,
    ){}

    public function create(array $validated): Order
    {
        $order = DB::transaction(function () use ($validated) {
            $promoCode = filled($validated['promo_code'] ?? null)
                ? $this->promoCodes->validate($validated['promo_code'], $validated['customer_email'])
                : null;

            $calc = $this->pricing->calculate($validated, $promoCode);

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'total_amount' => $calc['total'],
                'discount_amount' => $calc['discount'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'district_id' => $validated['district_id'] ?? null,
                'delivery_method' => $validated['delivery_method'],
                'delivery_fee' => $calc['deliveryFee'],
                'card_fee' => $calc['cardFee'],
                'recipient_name' => $validated['recipient_name'] ?? null,
                'card_message' => $validated['card_message'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);

            $this->attachItems($order, $calc['lineItems']);

            if ($promoCode) {
                $this->promoCodes->redeem($promoCode, $order);
            }

            return $order;
        });

        return $order->load('items.product', 'district', 'promoCode');
    }

    private function attachItems(Order $order, Collection $lineItems): void
    {
        foreach ($lineItems as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price_at_purchase' => $item['unit_price'],
            ]);
        }
    }
}
