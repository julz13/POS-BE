<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function index(Request $request)
    {
        $payments = Payment::with('transaction', 'cashier')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($payments);
    }

    public function show(Payment $payment)
    {
        return $this->successResponse($payment->load('transaction', 'cashier'));
    }

    public function void(Payment $payment, Request $request)
    {
        $validated = $request->validate(['void_reason' => 'required|string']);

        $payment->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => auth()->id(),
            'void_reason' => $validated['void_reason'],
        ]);

        return $this->successResponse($payment, 'Payment voided');
    }
}
