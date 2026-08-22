<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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

        return new OrderResource($order->load('items.product', 'district'));
    }
}
