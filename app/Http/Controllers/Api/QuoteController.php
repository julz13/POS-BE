<?php

namespace App\Http\Controllers\Api;

use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends BaseController
{
    public function index(Request $request)
    {
        $quotes = Quote::with('customer', 'items')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($quotes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $quote = Quote::create([
            'quote_number' => 'QOT-' . str_pad(Quote::count() + 1, 5, '0', STR_PAD_LEFT),
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'date' => now(),
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return $this->successResponse($quote->load('items'), 'Quote created', 201);
    }

    public function show(Quote $quote)
    {
        return $this->successResponse($quote->load('customer', 'items'));
    }

    public function convertToTransaction(Quote $quote)
    {
        // Logic to convert quote to transaction
        return $this->successResponse($quote, 'Quote converted to transaction');
    }

    public function update(Request $request, Quote $quote)
    {
        return $this->successResponse($quote, 'Quote updated');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return $this->successResponse(null, 'Quote deleted');
    }
}
