<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends BaseController
{
    public function index(Request $request)
    {
        $orders = SalesOrder::with('customer', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'order_date' => 'required|date',
            'items' => 'required|array',
        ]);

        $so = SalesOrder::create([
            'so_number' => 'SO-' . str_pad(SalesOrder::count() + 1, 5, '0', STR_PAD_LEFT),
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'order_date' => $validated['order_date'],
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return $this->successResponse($so->load('items'), 'Sales order created', 201);
    }

    public function show(SalesOrder $so)
    {
        return $this->successResponse($so->load('customer', 'items'));
    }

    public function convertToTransaction(SalesOrder $so)
    {
        // Logic to convert sales order to transaction
        return $this->successResponse($so, 'Sales order converted to transaction');
    }

    public function update(Request $request, SalesOrder $so)
    {
        return $this->successResponse($so, 'Sales order updated');
    }

    public function destroy(SalesOrder $so)
    {
        $so->delete();
        return $this->successResponse(null, 'Sales order deleted');
    }
}
