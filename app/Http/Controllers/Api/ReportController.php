<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\InventoryLedger;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function sales(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $base = Transaction::where('status', 'completed');

        if ($startDate) $base->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $base->whereDate('created_at', '<=', $endDate);

        $summary = [
            'total_transactions' => (clone $base)->count(),
            'total_sales'        => (clone $base)->sum('total'),
            'total_discounts'    => (clone $base)->sum('transaction_discount_amount'),
            'total_tax'          => (clone $base)->sum('tax_amount'),
        ];

        $transactions = (clone $base)
            ->with('cashier', 'customer')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 25);

        return $this->successResponse(array_merge($summary, ['transactions' => $transactions]));
    }

    public function inventory(Request $request)
    {
        $report = [
            'total_products' => InventoryLedger::distinct('product_id')->count(),
            'total_movements' => InventoryLedger::count(),
            'movements_by_type' => InventoryLedger::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get(),
        ];

        return $this->successResponse($report);
    }

    public function auditLog(Request $request)
    {
        $logs = AuditLog::with('user')
            ->orderByDesc('timestamp')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($logs);
    }
}
