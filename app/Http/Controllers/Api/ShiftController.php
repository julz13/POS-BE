<?php

namespace App\Http\Controllers\Api;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    public function index(Request $request)
    {
        $shifts = Shift::with('cashier')->paginate($request->per_page ?? 15);
        return $this->successResponse($shifts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cashier_id' => 'required|exists:users,id',
            'opening_cash' => 'numeric|min:0',
        ]);

        $shift = Shift::create([
            'shift_number' => 'SFT-' . str_pad(Shift::count() + 1, 5, '0', STR_PAD_LEFT),
            'cashier_id' => $validated['cashier_id'],
            'opened_at' => now(),
            'opening_cash' => $validated['opening_cash'] ?? 0,
            'status' => 'open',
        ]);

        return $this->successResponse($shift, 'Shift opened', 201);
    }

    public function show(Shift $shift)
    {
        return $this->successResponse($shift->load('cashier', 'transactions', 'movements'));
    }

    public function close(Shift $shift, Request $request)
    {
        $validated = $request->validate(['counted_cash' => 'numeric']);

        $shift->update([
            'closed_at' => now(),
            'closed_by' => auth()->id(),
            'counted_cash' => $validated['counted_cash'],
            'variance' => $validated['counted_cash'] - ($shift->expected_cash ?? 0),
            'status' => 'closed',
        ]);

        return $this->successResponse($shift, 'Shift closed');
    }

    public function getCurrentShift()
    {
        $shift = Shift::where('cashier_id', auth()->id())
            ->where('status', 'open')
            ->first();

        return $this->successResponse($shift);
    }

    public function addMovement(Shift $shift, Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        $movement = $shift->movements()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'recorded_by' => auth()->id(),
        ]);

        return $this->successResponse($movement, 'Movement recorded', 201);
    }
}
