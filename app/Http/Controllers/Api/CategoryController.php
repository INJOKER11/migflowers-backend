<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $validated = $request->validate([
           'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $perPage = $validated['per_page'] ?? 100;
        $categories = Category::where('is_active', true);

        return CategoryResource::collection($categories->paginate($perPage));
    }

    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return new CategoryResource($category);
    }
}
