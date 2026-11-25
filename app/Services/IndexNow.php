<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Tells IndexNow (Bing, Yandex, Seznam, Naver and others that share the
 * protocol) which storefront pages changed, so they recrawl them instead of
 * waiting to rediscover them. Bing's index also feeds Microsoft Copilot.
 *
 * The key is public by design: IndexNow checks it against the file the
 * storefront serves at FRONTEND_URL/<key>.txt.
 */
class IndexNow
{
    /** IndexNow accepts at most this many URLs per request. */
    private const BATCH = 10000;

    /** The storefront paths a saved or deleted record shows up on, uk and ru. */
    public static function pathsFor(Model $model): array
    {
        $slug = fn (string $locale) => (string) $model->getTranslation('slug', $locale, false);
        $pair = fn (string $base) => array_filter([
            $slug('uk') !== '' ? "{$base}/".rawurlencode($slug('uk')) : null,
            $slug('ru') !== '' ? "/ru{$base}/".rawurlencode($slug('ru')) : null,
        ]);

        return match (true) {
            $model instanceof Product => [...$pair('/product'), '/shop', '/ru/shop'],
            $model instanceof Category => $pair('/category'),
            $model instanceof Post => [...$pair('/blog'), '/blog', '/ru/blog'],
            default => [],
        };
    }

    /** After the response is sent: an admin save never waits on the ping. */
    public static function notify(Model $model): void
    {
        $paths = self::pathsFor($model);
        defer(fn () => app(self::class)->submit($paths));
    }

    /** @param  string[]  $paths  storefront paths ("/shop") or absolute URLs */
    public function submit(array $paths): void
    {
        $key = (string) config('services.indexnow.key');
        $site = rtrim((string) config('app.frontend_url'), '/');
        if (! config('services.indexnow.enabled') || $key === '' || $site === '') {
            return;
        }

        $urls = array_values(array_unique(array_map(
            fn (string $p) => str_starts_with($p, 'http') ? $p : $site.$p,
            $paths,
        )));

        foreach (array_chunk($urls, self::BATCH) as $batch) {
            try {
                Http::asJson()->connectTimeout(3)->timeout(10)->post(config('services.indexnow.endpoint'), [
                    'host' => parse_url($site, PHP_URL_HOST),
                    'key' => $key,
                    'keyLocation' => "{$site}/{$key}.txt",
                    'urlList' => $batch,
                ])->throw();
            } catch (Throwable $e) {
                /* A failed ping only means the search engine finds the change
                   on its next crawl; nothing the editor did should fail. */
                Log::warning('IndexNow submission failed', ['error' => $e->getMessage(), 'urls' => count($batch)]);
            }
        }
    }
}
