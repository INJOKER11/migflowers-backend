<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content', 'slug', 'subject'];
    protected $fillable = ['title', 'slug', 'content', 'image_url', 'subject'];
}
