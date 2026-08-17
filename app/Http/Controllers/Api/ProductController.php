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
            'category' => 'sometimes|string|exists:categories,slug',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ]);

        $categorySlug = $validated['category'] ?? null;
        $ids = $validated['ids'] ?? null;
        $perPage = $validated['per_page'] ?? 20;

        $products = Product::with('category')
            ->where('is_active', true)
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            })
            ->when($ids, fn ($query) => $query->whereIn('id', $ids));

        if($ids) {
            return ProductResource::collection($products->get());
        }

        return ProductResource::collection($products->paginate($perPage));
    }

    public function show(string $slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
