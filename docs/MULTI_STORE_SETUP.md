# Multi-Store Setup Guide

## Overview

PabiliPOS now supports **multiple stores under a single owner account**. This allows:

- ✅ One client/owner to manage 100+ stores
- ✅ Each store has isolated inventory, sales, transactions
- ✅ Owner can view aggregated data across all stores
- ✅ Staff can only access their assigned store
- ✅ Real-time store performance monitoring

---

## Architecture

### Multi-Tenant Data Model

```
Owner (User)
  ├── Store 1 (Manila)
  │   ├── Products (Store-specific)
  │   ├── Transactions (Store-specific)
  │   ├── Shifts (Store-specific)
  │   └── Inventory (Store-specific)
  │
  ├── Store 2 (Quezon City)
  │   ├── Products (Store-specific)
  │   ├── Transactions (Store-specific)
  │   ├── Shifts (Store-specific)
  │   └── Inventory (Store-specific)
  │
  └── Store 3 (Cebu)
      ├── Products (Store-specific)
      ├── Transactions (Store-specific)
      ├── Shifts (Store-specific)
      └── Inventory (Store-specific)
```

### Database Changes

**New Table: `stores`**
- `id` - Store ID
- `owner_id` - FK to users (owner)
- `name` - Store name
- `code` - Unique store code (e.g., "STR001")
- `address`, `phone`, `email`, `city`, `province` - Store details
- `latitude`, `longitude` - GPS coordinates
- `status` - active, inactive, closed
- `opened_date`, `closed_date` - Operating dates
- `created_at`, `updated_at`, `deleted_at`

**Modified Tables: (Added `store_id`)**
- categories
- products
- product_barcodes
- suppliers
- purchase_orders
- goods_received_notes
- inventory_ledger
- stock_takes
- transactions
- shifts
- vouchers
- discount_types
- quotes
- sales_orders
- audit_log

Each record now belongs to a specific store, ensuring complete data isolation.

---

## Setup Instructions

### 1. Run New Migrations

```bash
# Create stores table and add store_id to existing tables
php artisan migrate
```

This will:
- Create `stores` table
- Add `store_id` column to 15+ tables
- Add foreign key constraints
- Add indexes for performance

### 2. Create Stores via API

```bash
# Login as owner
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@example.com",
    "password": "password123"
  }'

# Response includes token: "1|abc123..."
```

Create stores:
```bash
curl -X POST http://localhost:8000/api/stores \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Manila Store",
    "code": "STR001",
    "address": "123 Makati Ave, Makati",
    "phone": "+63212345678",
    "email": "manila@store.com",
    "city": "Manila",
    "province": "Metro Manila",
    "postal_code": "1226",
    "latitude": 14.5564,
    "longitude": 121.0177,
    "status": "active",
    "opened_date": "2024-01-01",
    "notes": "Main store"
  }'
```

---

## How It Works

### For Store Staff (Cashiers, Managers)

**Step 1: Login**
```bash
POST /api/auth/login
Body: {
  "email": "cashier@store.com",
  "password": "password123"
}
```

**Step 2: Specify Store in Header**
```bash
GET /api/products \
  -H "Authorization: Bearer {token}" \
  -H "X-Store-Id: 1"
```

All requests with `X-Store-Id: 1` will only see that store's data:
- Products from Store 1 only
- Transactions from Store 1 only
- Shifts from Store 1 only
- Inventory for Store 1 only

**Step 3: All Operations Scoped to Store**
```bash
# These all return ONLY Store 1 data
GET  /api/products?store_id=1
GET  /api/transactions?store_id=1
POST /api/transactions  # Creates transaction in Store 1
GET  /api/shifts?store_id=1
```

### For Owner (Multi-Store Visibility)

**View All Stores**
```bash
GET /api/stores \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Manila Store",
        "code": "STR001",
        "status": "active",
        "address": "123 Makati Ave, Makati"
      },
      {
        "id": 2,
        "name": "QC Store",
        "code": "STR002",
        "status": "active",
        "address": "456 Quezon Ave, QC"
      }
    ]
  }
}
```

**View Single Store Stats**
```bash
GET /api/stores/1/stats?date_from=2024-01-01&date_to=2024-01-31 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": {
    "store_id": 1,
    "store_name": "Manila Store",
    "total_sales": 150000.00,
    "total_transactions": 450,
    "total_inventory_value": 500000.00,
    "low_stock_items": 12,
    "active_shifts": 3
  }
}
```

---

## Dashboard Endpoints (Owner Only)

### 1. Overview - All Stores Combined

```bash
GET /api/dashboard/overview?date_from=2024-01-01&date_to=2024-01-31 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": {
    "total_stores": 100,
    "active_stores": 98,
    "total_sales": 15000000.00,
    "total_transactions": 45000,
    "total_customers": 5000,
    "total_products": 3000,
    "total_inventory_value": 5000000.00,
    "period": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

### 2. Sales Comparison - Top Performing Stores

```bash
GET /api/dashboard/sales-comparison?date_from=2024-01-01&date_to=2024-01-31 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "store_id": 1,
      "store_name": "Manila Store",
      "store_code": "STR001",
      "status": "active",
      "total_sales": 500000.00,
      "total_transactions": 1500,
      "average_transaction": 333.33
    },
    {
      "store_id": 2,
      "store_name": "QC Store",
      "store_code": "STR002",
      "status": "active",
      "total_sales": 450000.00,
      "total_transactions": 1350,
      "average_transaction": 333.33
    }
  ]
}
```

### 3. Inventory Status - Low Stock Across All Stores

```bash
GET /api/dashboard/inventory-status \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": {
    "low_stock_items_count": 45,
    "low_stock_items": [
      {
        "store_id": 1,
        "store_name": "Manila Store",
        "items": [
          {
            "id": 1,
            "name": "Coca Cola 1.5L",
            "sku": "COKE001",
            "quantity": 5,
            "reorder_level": 20
          }
        ]
      }
    ]
  }
}
```

### 4. Performance Metrics - Per Store Analysis

```bash
GET /api/dashboard/performance-metrics?date_from=2024-01-01&date_to=2024-01-31 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "store_id": 1,
      "store_name": "Manila Store",
      "total_sales": 500000.00,
      "total_transactions": 1500,
      "average_transaction_value": 333.33,
      "void_amount": 15000.00,
      "void_percentage": 3.0,
      "return_amount": 10000.00
    }
  ]
}
```

### 5. Staff Performance - Top Performers Across All Stores

```bash
GET /api/dashboard/staff-performance?date_from=2024-01-01&date_to=2024-01-31 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "cashier_id": 5,
      "cashier_name": "Maria Garcia",
      "total_transactions": 150,
      "total_sales": 50000.00,
      "average_sale": 333.33
    },
    {
      "cashier_id": 3,
      "cashier_name": "John Doe",
      "total_transactions": 140,
      "total_sales": 47000.00,
      "average_sale": 335.71
    }
  ]
}
```

### 6. Sales Trend - Daily Sales Across All Stores

```bash
GET /api/dashboard/sales-trend?days=30 \
  -H "Authorization: Bearer {owner-token}"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "date": "2024-01-01",
      "transactions": 150,
      "sales_amount": 50000.00
    },
    {
      "date": "2024-01-02",
      "transactions": 155,
      "sales_amount": 52000.00
    }
  ]
}
```

---

## API Integration for Frontend

### Store Selection (For Multi-Store Apps)

**Step 1: List Available Stores**
```javascript
const stores = await fetch('/api/stores', {
  headers: { 'Authorization': 'Bearer ' + token }
}).then(r => r.json());
```

**Step 2: Set Store in All Subsequent Requests**
```javascript
const headers = {
  'Authorization': 'Bearer ' + token,
  'X-Store-Id': storeId,  // ← Set this for each request
  'Content-Type': 'application/json'
};

// All requests will use this store
const products = await fetch('/api/products', { headers }).then(r => r.json());
const transactions = await fetch('/api/transactions', { headers }).then(r => r.json());
```

### Example React Hook for Store Context

```javascript
// useStoreContext.js
import { createContext, useContext, useState } from 'react';

const StoreContext = createContext();

export function StoreProvider({ children }) {
  const [currentStore, setCurrentStore] = useState(null);
  const [stores, setStores] = useState([]);

  useEffect(() => {
    // Load stores on mount
    fetchStores();
  }, []);

  const fetchStores = async () => {
    const res = await fetch('/api/stores', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    setStores(data.data.data);
  };

  const selectStore = (storeId) => {
    setCurrentStore(storeId);
    localStorage.setItem('selectedStoreId', storeId);
  };

  return (
    <StoreContext.Provider value={{ currentStore, stores, selectStore }}>
      {children}
    </StoreContext.Provider>
  );
}

export function useStore() {
  return useContext(StoreContext);
}
```

### Usage in Components

```javascript
// ProductList.js
import { useStore } from './useStoreContext';

function ProductList() {
  const { currentStore } = useStore();
  const [products, setProducts] = useState([]);

  useEffect(() => {
    if (!currentStore) return;
    
    fetchProducts();
  }, [currentStore]);

  const fetchProducts = async () => {
    const res = await fetch('/api/products', {
      headers: {
        'Authorization': 'Bearer ' + token,
        'X-Store-Id': currentStore  // ← Automatic scoping
      }
    });
    const data = await res.json();
    setProducts(data.data);
  };

  return (
    <div>
      {products.map(p => (
        <div key={p.id}>{p.name}</div>
      ))}
    </div>
  );
}
```

---

## Security & Authorization

### User Roles

| Role | Can Do |
|------|--------|
| **Owner** | View all stores, dashboard, manage stores |
| **Manager** | View assigned store, manage staff, approve transactions |
| **Cashier** | Access assigned store only, create transactions |

### Role-Based Restrictions

**Owners:**
- Can create/edit/delete stores
- Can view all stores' data
- Cannot access POS functionality

**Managers:**
- Can only access assigned store (via X-Store-Id header)
- Can approve returns, discounts, price overrides
- Can view store reports

**Cashiers:**
- Can only access assigned store (via X-Store-Id header)
- Can create transactions, record sales
- Cannot approve discounts or overrides

---

## Backend Logic for Store Scoping

### Middleware: SetStoreContext

```php
// Automatically extracts store_id from header or query param
$storeId = $request->header('X-Store-Id') ?? $request->query('store_id');
$request->attributes->set('store_id', $storeId);
```

### In Controllers: Auto-Scoped Queries

```php
public function index(Request $request)
{
    $storeId = $request->header('X-Store-Id');
    
    // All queries automatically filtered by store
    $products = Product::where('store_id', $storeId)
        ->paginate($request->query('per_page', 15));
    
    return $this->successResponse($products);
}
```

---

## Data Migration from Single-Store to Multi-Store

If you have existing data and want to move to multi-store:

```bash
# 1. Create stores
php artisan tinker
> Store::create(['owner_id' => 1, 'name' => 'Default Store', 'code' => 'STR001', 'status' => 'active'])

# 2. Update existing records to point to new store
> Product::update(['store_id' => 1])
> Transaction::update(['store_id' => 1])
> Category::update(['store_id' => 1])
# ... etc for all tables
```

---

## Performance Considerations

### Indexes for Multi-Store

All relevant tables have indexes on `store_id` and `created_at` for fast queries:

```sql
CREATE INDEX idx_products_store_id ON products(store_id);
CREATE INDEX idx_transactions_store_id ON transactions(store_id);
CREATE INDEX idx_shifts_store_id ON shifts(store_id);
-- etc
```

### Query Optimization

For dashboard queries spanning 100+ stores, use aggregation:

```bash
# Instead of N+1 queries, use single aggregated query
GET /api/dashboard/overview
```

This returns aggregated data for all stores with a single efficient query.

---

## Troubleshooting

### Error: "Store ID is required"
**Solution:** Add `X-Store-Id` header to your request
```bash
curl -H "X-Store-Id: 1" http://localhost:8000/api/products
```

### Error: "Unauthorized" when accessing store
**Solution:** Ensure user's role matches the store context
- Cashiers/Managers must have X-Store-Id header set to their store
- Owners don't need X-Store-Id for dashboard endpoints

### Missing store_id in transactions
**Solution:** Run migration to add store_id columns
```bash
php artisan migrate
```

---

## Next Steps

1. ✅ Test multi-store setup locally
2. ✅ Create demo stores via API
3. ✅ Test staff access with X-Store-Id header
4. ✅ Verify owner dashboard aggregation
5. ✅ Deploy to production with proper environment variables
6. ✅ Monitor performance with 100+ stores

---

**Last Updated:** 2024-01-20  
**Version:** 1.0  
**Status:** Ready for Production
