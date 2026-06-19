<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    // Get owner's multi-store dashboard
    public function overview(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        // Get all stores for owner
        $stores = Store::where('owner_id', $user->id)
            ->select('id', 'name', 'code', 'status', 'created_at')
            ->get();

        $storeIds = $stores->pluck('id')->toArray();

        // Aggregate data from all stores
        $overview = [
            'total_stores' => $stores->count(),
            'active_stores' => $stores->where('status', 'active')->count(),
            'total_sales' => DB::table('transactions')
                ->whereIn('store_id', $storeIds)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_transactions' => DB::table('transactions')
                ->whereIn('store_id', $storeIds)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->count(),
            'total_customers' => DB::table('customers')
                ->whereIn('store_id', $storeIds)
                ->count(),
            'total_products' => DB::table('products')
                ->whereIn('store_id', $storeIds)
                ->count(),
            'total_inventory_value' => DB::table('products')
                ->whereIn('store_id', $storeIds)
                ->sum(DB::raw('quantity * cost')),
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        return $this->successResponse($overview, 'Dashboard overview retrieved successfully');
    }

    // Get sales comparison across stores
    public function salesComparison(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $stores = Store::where('owner_id', $user->id)
            ->get();

        $comparison = $stores->map(function ($store) use ($dateFrom, $dateTo) {
            return [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'store_code' => $store->code,
                'status' => $store->status,
                'total_sales' => $store->transactions()
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'total_transactions' => $store->transactions()
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->count(),
                'average_transaction' => $store->transactions()
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->avg('total_amount') ?? 0,
            ];
        })->sortByDesc('total_sales')->values();

        return $this->successResponse($comparison, 'Sales comparison retrieved successfully');
    }

    // Get inventory across all stores
    public function inventoryStatus(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $stores = Store::where('owner_id', $user->id)
            ->pluck('id')->toArray();

        $lowStock = DB::table('products')
            ->whereIn('store_id', $stores)
            ->whereRaw('quantity <= reorder_level')
            ->select('id', 'store_id', 'name', 'sku', 'quantity', 'reorder_level')
            ->get();

        $inventory = [
            'low_stock_items_count' => $lowStock->count(),
            'low_stock_items' => $lowStock->groupBy('store_id')->map(function ($items, $storeId) {
                $store = Store::find($storeId);
                return [
                    'store_id' => $storeId,
                    'store_name' => $store->name,
                    'items' => $items->toArray(),
                ];
            })->values(),
        ];

        return $this->successResponse($inventory, 'Inventory status retrieved successfully');
    }

    // Get performance metrics per store
    public function performanceMetrics(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $stores = Store::where('owner_id', $user->id)->get();

        $metrics = $stores->map(function ($store) use ($dateFrom, $dateTo) {
            $totalSales = $store->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('total_amount');

            $totalTransactions = $store->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->count();

            $totalVoided = $store->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'voided')
                ->sum('total_amount');

            $returnAmount = DB::table('returns')
                ->whereIn('id', function ($query) use ($store, $dateFrom, $dateTo) {
                    $query->select('id')->from('returns')
                        ->whereHas('transaction', function ($q) use ($store, $dateFrom, $dateTo) {
                            $q->where('store_id', $store->id)
                                ->whereBetween('created_at', [$dateFrom, $dateTo]);
                        });
                })
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'average_transaction_value' => $totalTransactions > 0 ? $totalSales / $totalTransactions : 0,
                'void_amount' => $totalVoided,
                'void_percentage' => $totalSales > 0 ? ($totalVoided / $totalSales) * 100 : 0,
                'return_amount' => $returnAmount,
            ];
        })->sortByDesc('total_sales')->values();

        return $this->successResponse($metrics, 'Performance metrics retrieved successfully');
    }

    // Get staff performance across stores
    public function staffPerformance(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $storeIds = Store::where('owner_id', $user->id)
            ->pluck('id')->toArray();

        $staffPerformance = DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->whereIn('transactions.store_id', $storeIds)
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.status', 'completed')
            ->groupBy('transactions.cashier_id', 'users.first_name', 'users.last_name')
            ->select(
                'transactions.cashier_id',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as cashier_name"),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_sales'),
                DB::raw('AVG(transactions.total_amount) as average_sale')
            )
            ->orderByDesc('total_sales')
            ->get();

        return $this->successResponse($staffPerformance, 'Staff performance retrieved successfully');
    }

    // Get daily sales trend across all stores
    public function salesTrend(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can access dashboard', 403);
        }

        $days = $request->query('days', 30);
        $dateFrom = now()->subDays($days)->toDateString();

        $storeIds = Store::where('owner_id', $user->id)
            ->pluck('id')->toArray();

        $trend = DB::table('transactions')
            ->whereIn('store_id', $storeIds)
            ->where('status', 'completed')
            ->where('created_at', '>=', $dateFrom)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as sales_amount')
            )
            ->orderBy('date')
            ->get();

        return $this->successResponse($trend, 'Sales trend retrieved successfully');
    }
}
