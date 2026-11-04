<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SimpleXMLElement;
use Tests\TestCase;

class GoogleMerchantFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.frontend_url' => 'https://www.example.test/',
        ]);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create($attributes + [
            'name' => ['uk' => 'Букет Романтик', 'ru' => 'Букет Романтик'],
            'slug' => ['uk' => 'buket-romantyk', 'ru' => 'buket-romantik'],
            'description' => ['uk' => '<p>Ніжний  букет з ромашок.</p>', 'ru' => 'Нежный букет'],
            'price' => 1200,
            'is_active' => true,
            'image' => 'products/romantyk.jpg',
        ]);
    }

    /** @return array<string, SimpleXMLElement> items keyed by g:id */
    private function items(): array
    {
        $response = $this->get('/api/feeds/google-merchant.xml');
        $response->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));

        $xml = new SimpleXMLElement($response->getContent());
        $items = [];
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            $items[(string) $g->id] = $g;
        }

        return $items;
    }

    public function test_an_active_product_becomes_an_item_with_the_required_fields(): void
    {
        $product = $this->product();
        $product->categories()->attach(Category::create([
            'name' => ['uk' => 'Букети', 'ru' => 'Букеты'],
            'slug' => ['uk' => 'bukety', 'ru' => 'bukety'],
            'is_active' => true,
        ]));

        $g = $this->items()[(string) $product->id];

        $this->assertSame('Букет Романтик', (string) $g->title);
        $this->assertSame('Ніжний букет з ромашок.', (string) $g->description);
        $this->assertSame('https://www.example.test/product/buket-romantyk', (string) $g->link);
        /* asset(), like ProductResource: the host the API is reached on. */
        $this->assertSame(asset('storage/products/romantyk.jpg'), (string) $g->image_link);
        $this->assertSame('in_stock', (string) $g->availability);
        $this->assertSame('new', (string) $g->condition);
        $this->assertSame('1200.00 UAH', (string) $g->price);
        $this->assertSame('MIG Flowers', (string) $g->brand);
        $this->assertSame('no', (string) $g->identifier_exists);
        $this->assertSame('2899', (string) $g->google_product_category);
        $this->assertSame('Букети', (string) $g->product_type);
        $this->assertCount(0, $g->sale_price);
    }

    public function test_inactive_products_and_products_without_a_photo_are_left_out(): void
    {
        $this->product(['is_active' => false]);
        $this->product(['image' => null, 'slug' => ['uk' => 'no-photo']]);

        $this->assertSame([], $this->items());
    }

    public function test_price_matches_the_product_page_with_default_options(): void
    {
        $product = $this->product(['price' => 60]);
        $size = Size::create(['name' => ['uk' => '60см'], 'sort_order' => 1]);
        $product->sizes()->create(['size_id' => $size->id, 'price_adjustment' => 60, 'is_active' => true, 'is_default' => true]);
        $product->colors()->create(['name' => ['uk' => 'Біла'], 'price_adjustment' => 10, 'is_active' => true, 'is_default' => true]);
        $product->colors()->create(['name' => ['uk' => 'Червона'], 'price_adjustment' => 500, 'is_active' => true, 'is_default' => false]);

        $this->assertSame('130.00 UAH', (string) $this->items()[(string) $product->id]->price);
    }

    public function test_a_real_discount_is_a_sale_price_and_a_bogus_one_is_ignored(): void
    {
        $sale = $this->product(['price' => 1000, 'discount_price' => 800]);
        $zero = $this->product(['price' => 1000, 'discount_price' => 0, 'slug' => ['uk' => 'zero']]);
        $higher = $this->product(['price' => 1000, 'discount_price' => 1200, 'slug' => ['uk' => 'higher']]);

        $items = $this->items();

        $this->assertSame('1000.00 UAH', (string) $items[(string) $sale->id]->price);
        $this->assertSame('800.00 UAH', (string) $items[(string) $sale->id]->sale_price);
        $this->assertCount(0, $items[(string) $zero->id]->sale_price);
        $this->assertCount(0, $items[(string) $higher->id]->sale_price);
    }

    public function test_a_product_without_a_description_gets_a_plain_one(): void
    {
        $product = $this->product(['description' => ['uk' => '']]);

        $this->assertSame(
            'Букет Романтик — квіти з родинної майстерні MIG Flowers в Одесі.',
            (string) $this->items()[(string) $product->id]->description,
        );
    }

    public function test_markup_in_names_is_escaped_not_broken(): void
    {
        $product = $this->product(['name' => ['uk' => 'Букет «Мрія» & <друзі>']]);

        $this->assertSame('Букет «Мрія» & <друзі>', (string) $this->items()[(string) $product->id]->title);
    }
}
