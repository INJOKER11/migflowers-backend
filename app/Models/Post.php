<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content', 'slug', 'subject', 'seo_title', 'meta_description'];

    protected $fillable = ['title', 'slug', 'content', 'image_url', 'subject', 'seo_title', 'meta_description'];

    /**
     * An SEO override in the request's locale, or null when that locale has
     * none. No fallback to another language: the storefront falls back to the
     * article's own title and text in the same language, which is better
     * than a Ukrainian description on a Russian page.
     */
    public function seoValue(string $key): ?string
    {
        $value = $this->getTranslation($key, app()->getLocale(), false);

        return filled($value) ? $value : null;
    }
}
