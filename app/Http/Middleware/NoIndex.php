<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps everything Laravel serves on api.migflowers.com out of search
 * results: the JSON API, the admin and its login page, /up. Google had
 * crawled the bare API root and listed it under "crawled, not indexed".
 * Product photos in /storage are served by nginx straight from disk and
 * never reach this middleware, so they stay eligible for Google Images.
 */
class NoIndex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
