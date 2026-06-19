<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->orderBy('sort_order')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        $category = Category::create($validated);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    public function show(Category $category)
    {
        return $this->successResponse($category->load('products'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'unique:categories,name,' . $category->id . '|max:100',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
            'sort_order' => 'integer|min:0',
        ]);

        $category->update($validated);

        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $category->update(['status' => 'inactive']);

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
