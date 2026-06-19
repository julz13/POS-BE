<?php

namespace App\Http\Controllers\Api;

use App\Models\DiscountType;
use Illuminate\Http\Request;

class DiscountTypeController extends BaseController
{
    public function index(Request $request)
    {
        $discounts = DiscountType::where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($discounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:discount_types',
            'name' => 'required|string',
            'default_pct' => 'numeric|min:0|max:100',
            'max_pct' => 'numeric|min:0|max:100',
        ]);

        $discount = DiscountType::create($validated);
        return $this->successResponse($discount, 'Discount type created', 201);
    }

    public function show(DiscountType $discountType)
    {
        return $this->successResponse($discountType);
    }

    public function update(Request $request, DiscountType $discountType)
    {
        $validated = $request->validate([
            'name' => 'string',
            'default_pct' => 'numeric|min:0|max:100',
            'max_pct' => 'numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $discountType->update($validated);
        return $this->successResponse($discountType, 'Discount type updated');
    }

    public function destroy(DiscountType $discountType)
    {
        $discountType->update(['is_active' => false]);
        return $this->successResponse(null, 'Discount type deleted');
    }
}
