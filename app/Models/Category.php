<?php

namespace App\Models;

use App\Services\IndexNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description', 'slug'];

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /** Changed pages are announced to IndexNow; see App\Services\IndexNow. */
    protected static function booted(): void
    {
        static::saved(fn (self $model) => IndexNow::notify($model));
        static::deleted(fn (self $model) => IndexNow::notify($model));
    }
}
