<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryLedger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends BaseController
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('cashier', 'customer', 'items', 'payments')
            ->paginate($request->per_page ?? 15);
        return $this->successResponse($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_id'                      => 'nullable|exists:shifts,id',
            'customer_id'                   => 'nullable|exists:customers,id',
            'customer_name'                 => 'nullable|string|max:200',
            'subtotal'                      => 'required|numeric|min:0',
            'line_discounts_total'          => 'numeric|min:0',
            'transaction_discount_pct'      => 'numeric|min:0|max:100',
            'transaction_discount_amount'   => 'numeric|min:0',
            'transaction_discount_type'     => 'nullable|string|max:30',
            'tax_amount'                    => 'numeric|min:0',
            'tax_rate'                      => 'numeric|min:0',
            'tax_type'                      => 'nullable|in:inclusive,exclusive',
            'total'                         => 'required|numeric|min:0',
            'amount_paid'                   => 'required|numeric|min:0',
            'change_given'                  => 'numeric|min:0',
            'source'                        => 'nullable|string|max:20',
            'source_id'                     => 'nullable|integer',
            'items'                         => 'required|array|min:1',
            'items.*.product_id'            => 'nullable|exists:products,id',
            'items.*.product_name'          => 'required|string|max:255',
            'items.*.sku'                   => 'required|string|max:50',
            'items.*.category'              => 'nullable|string|max:100',
            'items.*.unit'                  => 'required|string|max:30',
            'items.*.quantity'              => 'required|integer|min:1',
            'items.*.unit_price'            => 'required|numeric|min:0',
            'items.*.original_unit_price'   => 'nullable|numeric|min:0',
            'items.*.line_discount_pct'     => 'numeric|min:0|max:100',
            'items.*.line_discount_type'    => 'nullable|string|max:30',
            'items.*.line_discount_amount'  => 'numeric|min:0',
            'items.*.line_total'            => 'required|numeric|min:0',
            'items.*.tax_code'              => 'nullable|string|max:20',
            'items.*.price_overridden'      => 'boolean',
            'payments'                      => 'required|array|min:1',
            'payments.*.payment_method'     => 'required|string|max:30',
            'payments.*.amount'             => 'required|numeric|min:0',
            'payments.*.reference_number'   => 'nullable|string|max:100',
            'payments.*.voucher_code'       => 'nullable|string|max:20',
            'payments.*.is_electronic'      => 'boolean',
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            $receiptNumber = 'RCP-' . str_pad(Transaction::count() + 1, 7, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'receipt_number'                => $receiptNumber,
                'cashier_id'                    => auth()->id(),
                'shift_id'                      => $validated['shift_id'] ?? null,
                'customer_id'                   => $validated['customer_id'] ?? null,
                'customer_name'                 => $validated['customer_name'] ?? null,
                'subtotal'                      => $validated['subtotal'],
                'line_discounts_total'          => $validated['line_discounts_total'] ?? 0,
                'transaction_discount_pct'      => $validated['transaction_discount_pct'] ?? 0,
                'transaction_discount_amount'   => $validated['transaction_discount_amount'] ?? 0,
                'transaction_discount_type'     => $validated['transaction_discount_type'] ?? null,
                'tax_amount'                    => $validated['tax_amount'] ?? 0,
                'tax_rate'                      => $validated['tax_rate'] ?? 0,
                'tax_type'                      => $validated['tax_type'] ?? 'exclusive',
                'total'                         => $validated['total'],
                'amount_paid'                   => $validated['amount_paid'],
                'change_given'                  => max(0, $validated['amount_paid'] - $validated['total']),
                'status'                        => 'completed',
                'source'                        => $validated['source'] ?? 'pos',
                'source_id'                     => $validated['source_id'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $transaction->items()->create([
                    'product_id'            => $item['product_id'] ?? null,
                    'product_name'          => $item['product_name'],
                    'sku'                   => $item['sku'],
                    'category'              => $item['category'] ?? null,
                    'unit'                  => $item['unit'],
                    'quantity'              => $item['quantity'],
                    'unit_price'            => $item['unit_price'],
                    'original_unit_price'   => $item['original_unit_price'] ?? null,
                    'line_discount_pct'     => $item['line_discount_pct'] ?? 0,
                    'line_discount_type'    => $item['line_discount_type'] ?? null,
                    'line_discount_amount'  => $item['line_discount_amount'] ?? 0,
                    'line_total'            => $item['line_total'],
                    'tax_code'              => $item['tax_code'] ?? null,
                    'price_overridden'      => $item['price_overridden'] ?? false,
                ]);

                if (!empty($item['product_id'])) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if ($product && $product->track_inventory) {
                        $newStock = $product->stock - $item['quantity'];
                        $product->update(['stock' => $newStock]);

                        InventoryLedger::create([
                            'product_id'        => $product->id,
                            'type'              => 'sale',
                            'reference_type'    => 'transaction',
                            'reference_id'      => $transaction->id,
                            'reference_number'  => $transaction->receipt_number,
                            'qty_in'            => 0,
                            'qty_out'           => $item['quantity'],
                            'balance_after'     => $newStock,
                            'user_id'           => auth()->id(),
                        ]);
                    }
                }
            }

            $paymentBase = Payment::count();
            foreach ($validated['payments'] as $i => $payment) {
                $transaction->payments()->create([
                    'payment_number'    => 'PAY-' . str_pad($paymentBase + $i + 1, 7, '0', STR_PAD_LEFT),
                    'payment_method'    => $payment['payment_method'],
                    'amount'            => $payment['amount'],
                    'reference_number'  => $payment['reference_number'] ?? null,
                    'voucher_code'      => $payment['voucher_code'] ?? null,
                    'is_electronic'     => $payment['is_electronic'] ?? false,
                    'cashier_id'        => auth()->id(),
                    'status'            => 'completed',
                ]);
            }

            return $transaction->load('items', 'payments', 'cashier', 'customer');
        });

        return $this->successResponse($transaction, 'Transaction created', 201);
    }

    public function show(Transaction $transaction)
    {
        return $this->successResponse($transaction->load('cashier', 'customer', 'items.product', 'payments'));
    }

    public function void(Transaction $transaction, Request $request)
    {
        $validated = $request->validate(['void_reason' => 'required|string']);

        $transaction->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => auth()->id(),
            'void_reason' => $validated['void_reason'],
        ]);

        return $this->successResponse($transaction, 'Transaction voided');
    }

    public function getByReceiptNumber($receipt_number)
    {
        $transaction = Transaction::where('receipt_number', $receipt_number)
            ->with('cashier', 'customer', 'items.product', 'payments')
            ->firstOrFail();

        return $this->successResponse($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        return $this->errorResponse('Transactions cannot be updated after creation');
    }

    public function destroy(Transaction $transaction)
    {
        return $this->errorResponse('Transactions cannot be deleted. Use void instead.');
    }
}
