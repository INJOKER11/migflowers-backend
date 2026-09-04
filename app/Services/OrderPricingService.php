<?php

namespace App\Services;

use App\Models\District;
use App\Models\Product;
use Illuminate\Support\Collection;

class OrderPricingService
{
    public const CARD_FEE = 30;

    public function buildLineItems(array $items): Collection
    {
        return collect($items)->map(function ($item) {
           $product = Product::findOrFail($item['product_id']);

           return [
             'product_id' => $product->id,
             'product' => $product,
             'quantity' => $item['quantity'],
             'unit_price' => $product->discount_price ?? $product->price,
           ];
        });
    }

    public function deliveryFeeFor(array $validated): int
    {
        if($validated['delivery_method'] !== 'delivery') {
            return 0;
        }

        return District::findOrFail($validated['district_id'])->price_for_delivery ?? 0;
    }

    public function cardFeeFor(array $validated): int
    {
        return ($validated['with_card'] ?? false) ? self::CARD_FEE : 0;
    }

    public function itemsTotal(Collection $lineItems): int
    {
        return $lineItems->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
    }

    public function calculate(array $validated): array
    {
        $lineItems = $this->buildLineItems($validated['items']);
        $deliveryFee = $this->deliveryFeeFor($validated);
        $cardFee = $this->cardFeeFor($validated);
        $itemsTotal = $this->itemsTotal($lineItems);

        return [
            'lineItems' => $lineItems,
            'deliveryFee' => $deliveryFee,
            'cardFee' => $cardFee,
            'itemsTotal' => $itemsTotal,
            'total' => $itemsTotal  + $deliveryFee + $cardFee,
        ];
    }

}
