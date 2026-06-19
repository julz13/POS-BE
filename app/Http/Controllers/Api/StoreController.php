<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    // List all stores for the authenticated owner
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 15);

        // Only owner can see their stores
        if ($user->role === 'owner') {
            $stores = Store::where('owner_id', $user->id)
                ->paginate($perPage);
        } else {
            // Managers/cashiers can only see their assigned store
            $storeId = $request->header('X-Store-Id');
            if (!$storeId) {
                return $this->errorResponse('Store ID is required', 400);
            }
            $stores = Store::where('id', $storeId)->paginate($perPage);
        }

        return $this->successResponse($stores, 'Stores retrieved successfully');
    }

    // Create a new store
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return $this->errorResponse('Only owners can create stores', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:stores',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'city' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,inactive,closed',
            'opened_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $store = Store::create([
            ...$validated,
            'owner_id' => $user->id,
        ]);

        return $this->successResponse($store, 'Store created successfully', 201);
    }

    // Get a specific store
    public function show(Request $request, Store $store)
    {
        $user = $request->user();

        // Authorization check
        if ($user->role === 'owner' && $store->owner_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($user->role !== 'owner') {
            $storeId = $request->header('X-Store-Id');
            if ($store->id != $storeId) {
                return $this->errorResponse('Unauthorized', 403);
            }
        }

        $storeData = $store->load([
            'owner:id,first_name,last_name,email',
        ]);

        return $this->successResponse($storeData, 'Store retrieved successfully');
    }

    // Update a store
    public function update(Request $request, Store $store)
    {
        $user = $request->user();

        if ($user->role !== 'owner' || $store->owner_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'city' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,closed',
            'closed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $store->update($validated);

        return $this->successResponse($store, 'Store updated successfully');
    }

    // Delete a store
    public function destroy(Request $request, Store $store)
    {
        $user = $request->user();

        if ($user->role !== 'owner' || $store->owner_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $store->delete();

        return $this->successResponse(null, 'Store deleted successfully');
    }

    // Get store statistics
    public function stats(Request $request, Store $store)
    {
        $user = $request->user();

        if ($user->role === 'owner' && $store->owner_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $stats = [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'total_sales' => $store->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_transactions' => $store->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->count(),
            'total_inventory_value' => $store->products()
                ->sum(\DB::raw('quantity * cost')),
            'low_stock_items' => $store->products()
                ->whereRaw('quantity <= reorder_level')
                ->count(),
            'active_shifts' => $store->shifts()
                ->where('status', 'open')
                ->count(),
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ];

        return $this->successResponse($stats, 'Store statistics retrieved successfully');
    }
}
