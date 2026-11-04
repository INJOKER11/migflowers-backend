<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use XMLWriter;

/**
 * The catalogue as a Google Merchant Center product feed (RSS 2.0 with the
 * `g:` namespace). Merchant Center fetches it by URL on a schedule, so it is
 * built fresh from the database on every request — there is nothing to
 * regenerate when a product changes.
 *
 * Google disapproves an item whose feed price differs from the price on its
 * landing page, so `price` here is exactly what the storefront's product page
 * shows on load: the discount only when it is a real one (as `priceOf` in the
 * frontend decides), plus the surcharge of the size and colour preselected as
 * default.
 */
class GoogleMerchantFeed
{
    /** The feed is the Ukrainian storefront: bare paths, Ukrainian copy. */
    private const LOCALE = 'uk';

    private const BRAND = 'MIG Flowers';

    /** Arts & Entertainment > Party & Celebration > Gift Giving > Fresh Cut Flowers */
    private const GOOGLE_CATEGORY = '2899';

    private const TITLE_MAX = 150;

    private const DESCRIPTION_MAX = 5000;

    public function render(): string
    {
        $site = rtrim((string) config('app.frontend_url'), '/');

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xml->startElement('channel');
        $xml->writeElement('title', self::BRAND);
        $xml->writeElement('link', $site);
        $xml->writeElement('description', 'Квіти та букети з доставкою по Одесі');

        foreach ($this->products() as $product) {
            $this->writeItem($xml, $product, $site);
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /** Active products with a photo — `image_link` is required, so an item
        without one would only be disapproved. */
    private function products(): Collection
    {
        return Product::with(['categories', 'sizes', 'colors'])
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('id')
            ->get();
    }

    private function writeItem(XMLWriter $xml, Product $product, string $site): void
    {
        $name = trim((string) $product->getTranslation('name', self::LOCALE, false));
        $slug = (string) $product->getTranslation('slug', self::LOCALE, false);
        if ($name === '' || $slug === '') {
            return;
        }

        [$price, $salePrice] = $this->landingPrices($product);

        $xml->startElement('item');
        $xml->writeElement('g:id', (string) $product->id);
        $xml->writeElement('g:title', Str::limit($name, self::TITLE_MAX, ''));
        $xml->writeElement('g:description', $this->description($product, $name));
        $xml->writeElement('g:link', $site.'/product/'.rawurlencode($slug));
        $xml->writeElement('g:image_link', asset('storage/'.$product->image));
        $xml->writeElement('g:availability', 'in_stock');
        $xml->writeElement('g:condition', 'new');
        $xml->writeElement('g:price', $this->money($price));
        if ($salePrice !== null) {
            $xml->writeElement('g:sale_price', $this->money($salePrice));
        }
        $xml->writeElement('g:brand', self::BRAND);
        /* Bouquets are made in the shop: there is no GTIN or MPN to give. */
        $xml->writeElement('g:identifier_exists', 'no');
        $xml->writeElement('g:google_product_category', self::GOOGLE_CATEGORY);
        foreach ($product->categories->take(5) as $category) {
            $type = trim((string) $category->getTranslation('name', self::LOCALE, false));
            if ($type !== '') {
                $xml->writeElement('g:product_type', $type);
            }
        }
        $xml->endElement();
    }

    /**
     * [regular price, sale price or null], both with the default size and
     * colour surcharges — what the product page shows before anything is
     * clicked.
     */
    private function landingPrices(Product $product): array
    {
        $size = $product->sizes->first(fn ($s) => $s->is_active && $s->is_default);
        $color = $product->colors->first(fn ($c) => $c->is_active && $c->is_default);
        $surcharge = (float) ($size->price_adjustment ?? 0) + (float) ($color->price_adjustment ?? 0);

        $regular = (float) $product->price;
        $discount = $product->discount_price !== null ? (float) $product->discount_price : null;
        $discounted = $discount !== null && $discount > 0 && $discount < $regular;

        return [$regular + $surcharge, $discounted ? $discount + $surcharge : null];
    }

    /** Two products have no description in the admin; Google requires one, so
        they get a plain line naming the product and the shop instead. */
    private function description(Product $product, string $name): string
    {
        $text = (string) $product->getTranslation('description', self::LOCALE, false);
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));

        if ($text === '') {
            $text = $name.' — квіти з родинної майстерні '.self::BRAND.' в Одесі.';
        }

        return Str::limit($text, self::DESCRIPTION_MAX, '');
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '').' UAH';
    }
}
