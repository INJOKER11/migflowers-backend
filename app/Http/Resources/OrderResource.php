<?php

namespace App\Http\Resources;

use App\Http\Resources\DistrictResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $request->customer_phone,
            'delivery_method' => $this->delivery_method,
            'delivery_address' => $this->delivery_address,
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'district' => $this->district ? new DistrictResource($this->district) : null,
            'delivery_fee' => $this->delivery_fee,
            'recipient_name' => $this->recipient_name,
            'card_message' => $this->card_message,
            'card_fee' => $this->card_fee,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment_invoice_id' => $this->payment_invoice_id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
