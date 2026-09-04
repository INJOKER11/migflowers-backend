<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'delivery_date',
        'district_id',
        'delivery_method',
        'delivery_fee',
        'card_fee',
        'recipient_name',
        'card_message',
        'status',
        'total_amount',
        'payment_method',
        'payment_reference',
        'payment_invoice_id',
        'quantity',
        'price_at_purchase',
        'product_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'card_fee' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = (string) Str::uuid();
        });
    }
}
