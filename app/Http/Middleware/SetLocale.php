<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Switch the app locale based on the "lang" query parameter, so
     * translatable model attributes are returned in the requested language.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if (is_string($locale) && in_array($locale, config('app.supported_locales'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
