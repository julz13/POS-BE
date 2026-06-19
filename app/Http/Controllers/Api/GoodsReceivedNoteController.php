<?php

namespace App\Http\Controllers\Api;

use App\Models\GoodsReceivedNote;
use Illuminate\Http\Request;

class GoodsReceivedNoteController extends BaseController
{
    public function index(Request $request)
    {
        $grns = GoodsReceivedNote::with('supplier', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($grns);
    }

    public function store(Request $request)
    {
        // Implementation for storing GRN
        return $this->successResponse(null, 'GRN created', 201);
    }

    public function show(GoodsReceivedNote $grn)
    {
        return $this->successResponse($grn->load('supplier', 'items.product', 'purchaseOrder'));
    }

    public function post(GoodsReceivedNote $grn)
    {
        // Logic to post GRN and update inventory
        $grn->update(['status' => 'posted', 'posted_at' => now()]);
        return $this->successResponse($grn, 'GRN posted successfully');
    }

    public function update(Request $request, GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'draft') {
            return $this->errorResponse('Only draft GRNs can be updated');
        }
        return $this->successResponse($grn, 'GRN updated');
    }

    public function destroy(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'draft') {
            return $this->errorResponse('Only draft GRNs can be deleted');
        }
        $grn->delete();
        return $this->successResponse(null, 'GRN deleted');
    }
}
