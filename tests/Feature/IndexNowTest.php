<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDefer();
        config([
            'app.frontend_url' => 'https://www.example.test/',
            'services.indexnow.enabled' => true,
            'services.indexnow.key' => 'testkey123',
            'services.indexnow.endpoint' => self::ENDPOINT,
        ]);
        Http::fake([self::ENDPOINT => Http::response('', 200)]);
    }

    private function product(): Product
    {
        return Product::create([
            'name' => ['uk' => 'Букет Латте', 'ru' => 'Букет Латте'],
            'slug' => ['uk' => 'buket-latte', 'ru' => 'buket-latte-ru'],
            'price' => 2000,
            'is_active' => true,
        ]);
    }

    private function sentUrls(): array
    {
        $urls = [];
        Http::assertSent(function (Request $request) use (&$urls) {
            if ($request->url() !== self::ENDPOINT) {
                return false;
            }
            $this->assertSame('www.example.test', $request['host']);
            $this->assertSame('testkey123', $request['key']);
            $this->assertSame('https://www.example.test/testkey123.txt', $request['keyLocation']);
            $urls = array_merge($urls, $request['urlList']);

            return true;
        });

        return $urls;
    }

    public function test_saving_a_product_announces_its_pages_in_both_locales_and_the_shop(): void
    {
        $this->product();

        $this->assertEqualsCanonicalizing([
            'https://www.example.test/product/buket-latte',
            'https://www.example.test/ru/product/buket-latte-ru',
            'https://www.example.test/shop',
            'https://www.example.test/ru/shop',
        ], $this->sentUrls());
    }

    public function test_posts_and_categories_announce_their_own_pages(): void
    {
        Post::create([
            'title' => ['uk' => 'Стаття'], 'slug' => ['uk' => 'stattia', 'ru' => 'statya'],
            'content' => ['uk' => 'Текст'], 'subject' => ['uk' => 'Тема'],
        ]);
        Category::create(['name' => ['uk' => 'Троянди'], 'slug' => ['uk' => 'troiandy'], 'is_active' => true]);

        $urls = $this->sentUrls();
        foreach (['/blog/stattia', '/ru/blog/statya', '/blog', '/ru/blog', '/category/troiandy'] as $path) {
            $this->assertContains('https://www.example.test'.$path, $urls);
        }
        /* No Russian slug → no /ru/category URL guessed. */
        $this->assertNotContains('https://www.example.test/ru/category/', $urls);
    }

    public function test_nothing_is_sent_when_disabled(): void
    {
        config(['services.indexnow.enabled' => false]);

        $this->product();

        Http::assertNothingSent();
    }

    public function test_a_failing_endpoint_never_breaks_saving(): void
    {
        Http::fake([self::ENDPOINT => Http::response('nope', 500)]);

        $product = $this->product();

        $this->assertTrue($product->exists);
    }

    public function test_the_sitemap_command_submits_every_url(): void
    {
        Http::fake([
            'https://www.example.test/sitemap.xml' => Http::response(
                '<urlset><url><loc>https://www.example.test/</loc></url><url><loc>https://www.example.test/shop</loc></url></urlset>'
            ),
            self::ENDPOINT => Http::response('', 202),
        ]);

        $this->artisan('indexnow:sitemap')->assertSuccessful();

        Http::assertSent(fn (Request $r) => $r->url() === self::ENDPOINT
            && $r['urlList'] === ['https://www.example.test/', 'https://www.example.test/shop']);
    }
}
