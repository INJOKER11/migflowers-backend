<?php

use App\Services\IndexNow;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Submits every URL in the storefront's sitemap to IndexNow — once after
   setting the key up, or after large catalogue changes. Day to day, saving a
   product, category or post announces its own pages. */
Artisan::command('indexnow:sitemap', function (IndexNow $indexNow) {
    $sitemap = rtrim((string) config('app.frontend_url'), '/').'/sitemap.xml';
    preg_match_all('#<loc>([^<]+)</loc>#', Http::timeout(20)->get($sitemap)->throw()->body(), $m);
    $indexNow->submit($m[1]);
    $this->info('Submitted '.count($m[1]).' URLs from '.$sitemap);
})->purpose('Submit every storefront sitemap URL to IndexNow');
