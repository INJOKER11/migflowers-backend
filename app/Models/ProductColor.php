<?php

namespace App\Models;

use App\Models\Concerns\HasDefaultPerProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProductColor extends Model
{
    use HasTranslations, HasDefaultPerProduct;

    public array $translatable = ['name'];

    protected $fillable = ['product_id', 'name', 'image', 'price_adjustment', 'is_active', 'is_default'];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
