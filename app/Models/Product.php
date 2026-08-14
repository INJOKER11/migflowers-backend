<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
      'name',
      'slug',
      'description',
      'price',
      'is_active',
      'stock',
      'category_id',
      'image',
    ];

    protected $casts = [
      'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
