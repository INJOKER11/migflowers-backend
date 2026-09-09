<?php

namespace App\Services;

use App\Models\District;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\PromoCode;
use Illuminate\Support\Collection;

class OrderPricingService
{
    public const CARD_FEE = 30;

    public function buildLineItems(array $items): Collection
    {
        return collect($items)->map(function ($item) {
            $product = Product::findOrFail($item['product_id']);

            $productSize = ! empty($item['size_id'])
                ? ProductSize::where('product_id', $product->id)->where('size_id', $item['size_id'])->where('is_active', true)->first()
                : null;

            $productColor = ! empty($item['color_id'])
                ? ProductColor::where('id', $item['color_id'])->where('product_id', $product->id)->where('is_active', true)->first()
                : null;

            $unitPrice = ($product->discount_price ?? $product->price)
                + ($productSize->price_adjustment ?? 0)
                + ($productColor->price_adjustment ?? 0);

            return [
                'product_id' => $product->id,
                'product' => $product,
                'size_id' => $item['size_id'] ?? null,
                'color_id' => $item['color_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
            ];
        });
    }

    public function deliveryFeeFor(array $validated): int
    {
        if ($validated['delivery_method'] !== 'delivery') {
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

    public function calculate(array $validated, ?PromoCode $promoCode = null): array
    {
        $lineItems = $this->buildLineItems($validated['items']);
        $deliveryFee = $this->deliveryFeeFor($validated);
        $cardFee = $this->cardFeeFor($validated);
        $itemsTotal = $this->itemsTotal($lineItems);
        $discount = $promoCode ? $promoCode->discountFor($itemsTotal) : 0;

        return [
            'lineItems' => $lineItems,
            'deliveryFee' => $deliveryFee,
            'cardFee' => $cardFee,
            'itemsTotal' => $itemsTotal,
            'discount' => $discount,
            'total' => $itemsTotal - $discount + $deliveryFee + $cardFee,
        ];
    }
}
