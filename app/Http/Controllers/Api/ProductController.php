<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('status', 'active');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate($request->per_page ?? 15);

        return $this->successResponse($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'numeric|min:0',
            'unit' => 'required',
            'status' => 'in:active,inactive,archived',
        ]);

        $validated['created_by'] = auth()->id();

        $product = Product::create($validated);

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    public function show(Product $product)
    {
        return $this->successResponse($product->load('category', 'barcodes'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'unique:products,sku,' . $product->id,
            'name' => 'max:255',
            'category_id' => 'exists:categories,id',
            'selling_price' => 'numeric|min:0',
            'cost_price' => 'numeric|min:0',
            'unit' => 'string',
            'status' => 'in:active,inactive,archived',
        ]);

        $product->update($validated);

        return $this->successResponse($product, 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }

    public function search($term)
    {
        $products = Product::where('name', 'like', "%$term%")
            ->orWhere('sku', 'like', "%$term%")
            ->where('status', 'active')
            ->limit(10)
            ->get();

        return $this->successResponse($products);
    }

    public function searchByBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        return $this->successResponse($product);
    }
}
