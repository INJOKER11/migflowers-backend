<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'delivery_date',
        'recipient_name',
        'card_message',
        'status',
        'total_amount',
        'payment_method',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
      'delivery_date' => 'date',
      'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
