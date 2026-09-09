<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'stock' => $this->stock,
            'is_available' => $this->is_active,
            'image_url' => $this->image ? asset('storage/'.$this->image) : null,
            'categories' => $this->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]),
            'sizes' => $this->sizes->where('is_active', true)->sortBy(fn ($productSize) => $productSize->size->sort_order)->values()->map(fn ($productSize) => [
                'id' => $productSize->size->id,
                'name' => $productSize->size->name,
                'price_adjustment' => $productSize->price_adjustment,
                'is_default' => $productSize->is_default,
            ]),
            'colors' => $this->colors->where('is_active', true)->values()->map(fn ($productColor) => [
                'id' => $productColor->id,
                'name' => $productColor->name,
                'image_url' => $productColor->image ? asset('storage/'.$productColor->image) : null,
                'price_adjustment' => $productColor->price_adjustment,
                'is_default' => $productColor->is_default,
            ]),
        ];
    }
}
