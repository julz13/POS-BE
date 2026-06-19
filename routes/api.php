<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    ProductController,
    CategoryController,
    CustomerController,
    SupplierController,
    PurchaseOrderController,
    GoodsReceivedNoteController,
    TransactionController,
    PaymentController,
    ReturnController,
    ShiftController,
    VoucherController,
    DiscountTypeController,
    QuoteController,
    SalesOrderController,
    StockTakeController,
    SettingController,
    StoreController,
    DashboardController,
};

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok']));

// Authentication
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum')->name('refresh');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');

// Protected routes
Route::middleware(['auth:sanctum', 'set.store'])->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('products/search/{term}', [ProductController::class, 'search']);
    Route::get('products/barcode/{barcode}', [ProductController::class, 'searchByBarcode']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/search/{term}', [CustomerController::class, 'search']);
    Route::post('customers/{customer}/credit', [CustomerController::class, 'updateCredit']);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{po}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('purchase-orders/{po}/cancel', [PurchaseOrderController::class, 'cancel']);

    // Goods Received Notes
    Route::apiResource('goods-received-notes', GoodsReceivedNoteController::class);
    Route::post('goods-received-notes/{grn}/post', [GoodsReceivedNoteController::class, 'post']);

    // Transactions (POS Sales)
    Route::apiResource('transactions', TransactionController::class);
    Route::post('transactions/{transaction}/void', [TransactionController::class, 'void']);
    Route::get('transactions/receipt/{receipt_number}', [TransactionController::class, 'getByReceiptNumber']);

    // Payments
    Route::apiResource('payments', PaymentController::class, ['only' => ['index', 'show']]);
    Route::post('payments/{payment}/void', [PaymentController::class, 'void']);

    // Returns
    Route::apiResource('returns', ReturnController::class);
    Route::post('returns/{return}/approve', [ReturnController::class, 'approve']);
    Route::post('returns/{return}/complete', [ReturnController::class, 'complete']);
    Route::post('returns/{return}/reject', [ReturnController::class, 'reject']);

    // Shifts
    Route::apiResource('shifts', ShiftController::class, ['only' => ['index', 'show', 'store']]);
    Route::post('shifts/{shift}/close', [ShiftController::class, 'close']);
    Route::get('shifts/active/current', [ShiftController::class, 'getCurrentShift']);
    Route::post('shifts/{shift}/movements', [ShiftController::class, 'addMovement']);

    // Vouchers
    Route::apiResource('vouchers', VoucherController::class);
    Route::post('vouchers/{voucher}/redeem', [VoucherController::class, 'redeem']);
    Route::post('vouchers/{voucher}/cancel', [VoucherController::class, 'cancel']);

    // Discount Types
    Route::apiResource('discount-types', DiscountTypeController::class);

    // Quotes
    Route::apiResource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToTransaction']);

    // Sales Orders
    Route::apiResource('sales-orders', SalesOrderController::class);
    Route::post('sales-orders/{so}/convert', [SalesOrderController::class, 'convertToTransaction']);

    // Stock Takes
    Route::apiResource('stock-takes', StockTakeController::class);
    Route::post('stock-takes/{st}/complete', [StockTakeController::class, 'complete']);
    Route::post('stock-takes/{st}/post', [StockTakeController::class, 'post']);

    // Stores (Multi-store management)
    Route::apiResource('stores', StoreController::class);
    Route::get('stores/{store}/stats', [StoreController::class, 'stats']);

    // Dashboard & Analytics (Owner view across all stores)
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);
    Route::get('dashboard/sales-comparison', [DashboardController::class, 'salesComparison']);
    Route::get('dashboard/inventory-status', [DashboardController::class, 'inventoryStatus']);
    Route::get('dashboard/performance-metrics', [DashboardController::class, 'performanceMetrics']);
    Route::get('dashboard/staff-performance', [DashboardController::class, 'staffPerformance']);
    Route::get('dashboard/sales-trend', [DashboardController::class, 'salesTrend']);

    // Settings
    Route::get('settings', [SettingController::class, 'show']);
    Route::put('settings', [SettingController::class, 'update']);

    // Reports & Analytics
    Route::get('/reports/sales', [\App\Http\Controllers\Api\ReportController::class, 'sales']);
    Route::get('/reports/inventory', [\App\Http\Controllers\Api\ReportController::class, 'inventory']);
    Route::get('/reports/audit-log', [\App\Http\Controllers\Api\ReportController::class, 'auditLog']);
});
