<?php

namespace App\Http\Controllers\Api;

use App\Models\Return_;
use Illuminate\Http\Request;

class ReturnController extends BaseController
{
    public function index(Request $request)
    {
        $returns = Return_::with('customer', 'transaction', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($returns);
    }

    public function store(Request $request)
    {
        return $this->successResponse(null, 'Return created', 201);
    }

    public function show(Return_ $return)
    {
        return $this->successResponse($return->load('customer', 'transaction', 'items'));
    }

    public function approve(Return_ $return)
    {
        $return->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return $this->successResponse($return, 'Return approved');
    }

    public function complete(Return_ $return)
    {
        $return->update(['status' => 'completed', 'completed_by' => auth()->id(), 'completed_at' => now()]);
        return $this->successResponse($return, 'Return completed');
    }

    public function reject(Return_ $return, Request $request)
    {
        $validated = $request->validate(['reject_reason' => 'required|string']);
        $return->update(['status' => 'rejected', 'rejected_by' => auth()->id(), 'rejected_at' => now(), 'reject_reason' => $validated['reject_reason']]);
        return $this->successResponse($return, 'Return rejected');
    }

    public function update(Request $request, Return_ $return)
    {
        return $this->successResponse($return, 'Return updated');
    }

    public function destroy(Return_ $return)
    {
        $return->delete();
        return $this->successResponse(null, 'Return deleted');
    }
}
