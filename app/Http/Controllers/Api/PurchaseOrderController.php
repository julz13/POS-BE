<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends BaseController
{
    public function index(Request $request)
    {
        $pos = PurchaseOrder::with('supplier', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($pos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'po_number'     => 'PO-' . str_pad(PurchaseOrder::count() + 1, 5, '0', STR_PAD_LEFT),
            'supplier_id'   => $validated['supplier_id'],
            'order_date'    => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'created_by'    => auth()->id(),
            'status'        => 'draft',
        ]);

        foreach ($validated['items'] as $item) {
            $po->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['quantity'] * $item['unit_cost'],
            ]);
        }

        return $this->successResponse($po->load('items'), 'Purchase order created', 201);
    }

    public function show(PurchaseOrder $po)
    {
        return $this->successResponse($po->load('supplier', 'items.product', 'createdBy'));
    }

    public function update(Request $request, PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            return $this->errorResponse('Only draft POs can be updated');
        }

        $validated = $request->validate([
            'expected_date' => 'nullable|date',
            'notes' => 'nullable',
        ]);

        $po->update($validated);
        return $this->successResponse($po, 'Purchase order updated');
    }

    public function approve(PurchaseOrder $po)
    {
        $po->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $this->successResponse($po, 'Purchase order approved');
    }

    public function cancel(PurchaseOrder $po)
    {
        $po->update(['status' => 'cancelled']);
        return $this->successResponse($po, 'Purchase order cancelled');
    }

    public function destroy(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            return $this->errorResponse('Only draft POs can be deleted');
        }

        $po->delete();
        return $this->successResponse(null, 'Purchase order deleted');
    }
}
