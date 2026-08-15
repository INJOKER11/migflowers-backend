<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_date' => 'required|date|after_or_equal:today',
            'recipient_name' => 'nullable|string',
            'card_message' => 'nullable|string',
            'payment_method' => 'required|in:online,cash_on_delivery',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total = 0;


        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if($product->stock < $item['quantity'] ) {
                return response()->json(['message' => "Not enough stock for {$product->name}. Only {$product->stock} available."], 422);            }
            $total += $product->price * $item['quantity'];
        }

        $order = DB::transaction(function () use ($validated, $total) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'total_amount' => $total,
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_date' => $validated['delivery_date'],
                'recipient_name' => $validated['recipient_name'] ?? null,
                'card_message' => $validated['card_message'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);


            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $product->price,
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });
        return new OrderResource($order->load('items.product'));
    }
}
