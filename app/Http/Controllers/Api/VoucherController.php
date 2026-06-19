<?php

namespace App\Http\Controllers\Api;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends BaseController
{
    public function index(Request $request)
    {
        $vouchers = Voucher::paginate($request->per_page ?? 15);
        return $this->successResponse($vouchers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_amount' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'issued_to_name' => 'nullable|string',
            'issued_to_customer_id' => 'nullable|exists:customers,id',
        ]);

        $voucher = Voucher::create([
            'code' => 'GV-' . strtoupper(bin2hex(random_bytes(4))),
            'original_amount' => $validated['original_amount'],
            'remaining_balance' => $validated['original_amount'],
            'issued_by' => auth()->id(),
            'expires_at' => $validated['expires_at'] ?? null,
            'issued_to_name' => $validated['issued_to_name'] ?? null,
            'issued_to_customer_id' => $validated['issued_to_customer_id'] ?? null,
        ]);

        return $this->successResponse($voucher, 'Voucher created', 201);
    }

    public function show(Voucher $voucher)
    {
        return $this->successResponse($voucher->load('usages'));
    }

    public function redeem(Voucher $voucher, Request $request)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:0']);

        if ($voucher->remaining_balance < $validated['amount']) {
            return $this->errorResponse('Insufficient voucher balance');
        }

        $voucher->remaining_balance -= $validated['amount'];
        $voucher->status = $voucher->remaining_balance == 0 ? 'redeemed' : 'partially_used';
        $voucher->save();

        return $this->successResponse($voucher, 'Voucher redeemed');
    }

    public function cancel(Voucher $voucher, Request $request)
    {
        $validated = $request->validate(['reason' => 'required|string']);
        $voucher->update(['status' => 'cancelled']);
        return $this->successResponse($voucher, 'Voucher cancelled');
    }

    public function update(Request $request, Voucher $voucher)
    {
        return $this->successResponse($voucher, 'Voucher updated');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return $this->successResponse(null, 'Voucher deleted');
    }
}
