<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VeraRsvpCors
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/vera-rsvp')) {
            return $next($request);
        }

        $origin = $request->headers->get('Origin');
        if ($origin !== null && ! in_array($origin, config('vera_rsvp.allowed_origins', []), true)) {
            return response()->json(['ok' => false, 'error' => 'origin_not_allowed'], 403);
        }

        if ($request->isMethod('OPTIONS')) {
            $method = $request->headers->get('Access-Control-Request-Method');
            $headers = array_filter(array_map('trim', explode(',', strtolower(
                (string) $request->headers->get('Access-Control-Request-Headers', '')
            ))));
            if ($origin === null || $method !== 'POST' || array_diff($headers, ['content-type', 'accept', 'x-vera-invitation'])) {
                return response()->json(['ok' => false, 'error' => 'invalid_preflight'], 403);
            }
            $response = response()->noContent();
            $response->headers->set('Access-Control-Allow-Methods', 'POST');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Vera-Invitation');
            $response->headers->set('Access-Control-Max-Age', '600');
            $response->setVary(['Access-Control-Request-Method', 'Access-Control-Request-Headers'], false);
        } elseif (strlen($request->getContent()) > 8192) {
            $response = response()->json(['ok' => false, 'error' => 'payload_too_large'], 413);
        } else {
            $response = $next($request);
        }

        if ($origin !== null) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        $response->setVary('Origin', false);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
