<?php

namespace App\Models\Concerns;

trait HasDefaultPerProduct
{
    protected static function bootHasDefaultPerProduct(): void
    {
        static::saving(function ($model) {
            if ($model->is_default) {
                static::where('product_id', $model->product_id)
                    ->when($model->exists, fn ($query) => $query->whereKeyNot($model->getKey()))
                    ->update(['is_default' => false]);
            }
        });
    }
}
