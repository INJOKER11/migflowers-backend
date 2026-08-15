<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'price_at_purchase' => $this->price_at_purchase,
            'subtotal' => $this->quantity * $this->price_at_purchase,
        ];
    }
}
