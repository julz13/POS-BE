<?php

namespace App\Http\Controllers\Api;

use App\Models\StockTake;
use Illuminate\Http\Request;

class StockTakeController extends BaseController
{
    public function index(Request $request)
    {
        $stockTakes = StockTake::with('createdBy', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($stockTakes);
    }

    public function store(Request $request)
    {
        $st = StockTake::create([
            'st_number' => 'ST-' . str_pad(StockTake::count() + 1, 5, '0', STR_PAD_LEFT),
            'date' => now(),
            'created_by' => auth()->id(),
            'status' => 'in_progress',
        ]);

        return $this->successResponse($st, 'Stock take created', 201);
    }

    public function show(StockTake $stockTake)
    {
        return $this->successResponse($stockTake->load('items.product', 'createdBy'));
    }

    public function complete(StockTake $st)
    {
        $st->update(['status' => 'completed', 'completed_at' => now()]);
        return $this->successResponse($st, 'Stock take completed');
    }

    public function post(StockTake $st)
    {
        // Logic to post stock take adjustments to inventory
        $st->update(['status' => 'posted', 'posted_at' => now(), 'posted_by' => auth()->id()]);
        return $this->successResponse($st, 'Stock take posted');
    }

    public function update(Request $request, StockTake $st)
    {
        return $this->successResponse($st, 'Stock take updated');
    }

    public function destroy(StockTake $st)
    {
        $st->delete();
        return $this->successResponse(null, 'Stock take deleted');
    }
}
