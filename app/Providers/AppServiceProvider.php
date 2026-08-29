<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Missing translations (e.g. "ru" not filled in yet) fall back to
        // Ukrainian rather than being rendered blank.
        app(Translatable::class)->fallback(
            fallbackLocale: 'uk',
            fallbackAny: true,
        );
    }
}
