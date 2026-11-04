<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleMerchantFeed;
use Illuminate\Http\Response;

class GoogleMerchantFeedController extends Controller
{
    public function __invoke(GoogleMerchantFeed $feed): Response
    {
        return response($feed->render(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            /* Merchant Center fetches once a day; an hour of caching only
               spares the database from repeated manual checks. */
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
