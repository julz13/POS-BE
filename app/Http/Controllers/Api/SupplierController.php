<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends BaseController
{
    public function index(Request $request)
    {
        $suppliers = Supplier::where('status', 'active')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($suppliers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'code' => 'required|unique:suppliers',
            'contact_person' => 'nullable|max:200',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        $supplier = Supplier::create($validated);
        return $this->successResponse($supplier, 'Supplier created successfully', 201);
    }

    public function show(Supplier $supplier)
    {
        return $this->successResponse($supplier->load('purchaseOrders'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'max:255',
            'contact_person' => 'nullable|max:200',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'status' => 'in:active,inactive',
        ]);

        $supplier->update($validated);
        return $this->successResponse($supplier, 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
