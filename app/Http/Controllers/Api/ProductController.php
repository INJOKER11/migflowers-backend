<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'sometimes|array',
            'ids.*' => 'integer|exists:products,id',
            'category' => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'max_price' => 'sometimes|numeric|min:0',
            'sort' => 'sometimes|in:popular,newest,price_asc,price_desc',
        ]);

        $categorySlug = $validated['category'] ?? null;
        $ids = $validated['ids'] ?? null;
        $perPage = $validated['per_page'] ?? 20;
        $maxPrice = $validated['max_price'] ?? null;
        $sort = $validated['sort'] ?? null;

        $products = Product::with('category')
            ->where('is_active', true)
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($q) => $q->whereJsonContainsLocales('slug', [app()->getLocale(), 'uk'], $categorySlug));
            })
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->when($maxPrice, fn ($query) => $query->where('price', '<=', $maxPrice))
            ->when($sort, function ($query) use ($sort) {
                match ($sort) {
                    'popular' => $query->withSum('orderItems as units_sold', 'quantity')->orderByRaw('units_sold DESC NULLS LAST'),
                    'newest' => $query->orderBy('created_at', 'desc'),
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                };
            })
            ->orderByDesc('created_at');

        if ($ids) {
            return ProductResource::collection($products->get());
        }

        return ProductResource::collection($products->paginate($perPage));
    }

    public function show(string $slug)
    {
        $product = Product::with('category')
            ->whereJsonContainsLocales('slug', [app()->getLocale(), 'uk'], $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
