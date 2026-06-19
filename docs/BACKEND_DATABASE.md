# PabiliPOS — Backend Database Reference

> **Stack:** Laravel · PHP · MySQL · Eloquent  
> **Conventions:** snake_case · BIGSERIAL primary keys (switch to UUID for multi-tenant SaaS) · soft deletes via `deleted_at` · every table includes `created_at` and `updated_at`

---

## Table of Contents

1. [Users & Auth](#1-users--auth)
2. [Customers](#2-customers)
3. [Suppliers](#3-suppliers)
4. [Categories](#4-categories)
5. [Products](#5-products)
6. [Product Barcodes](#6-product_barcodes)
7. [Purchase Orders](#7-purchase_orders)
8. [Purchase Order Items](#8-purchase_order_items)
9. [Goods Received Notes](#9-goods_received_notes)
10. [Goods Received Items](#10-goods_received_items)
11. [Inventory Ledger](#11-inventory_ledger)
12. [Stock Takes](#12-stock_takes)
13. [Stock Take Items](#13-stock_take_items)
14. [Quotes](#14-quotes)
15. [Quote Items](#15-quote_items)
16. [Sales Orders](#16-sales_orders)
17. [Sales Order Items](#17-sales_order_items)
18. [Transactions](#18-transactions)
19. [Transaction Items](#19-transaction_items)
20. [Payments](#20-payments)
21. [Returns](#21-returns)
22. [Return Items](#22-return_items)
23. [Vouchers](#23-vouchers)
24. [Voucher Usages](#24-voucher_usages)
25. [Discount Types](#25-discount_types)
26. [Discount Audit](#26-discount_audit)
27. [Shifts](#27-shifts)
28. [Shift Movements](#28-shift_movements)
29. [Audit Log](#29-audit_log)
30. [Settings](#30-settings)

---

## Entity Relationship Summary

```
users ──────────────────────────────────────────────────────────┐
  │                                                              │
  ├── shifts ──── shift_movements                               │
  │                                                             │
customers ──── transactions ──── transaction_items              │
  │                  │                  │                       │
  │                  ├── payments       └── products            │
  │                  └── discount_audit      │                  │
  │                                          ├── categories     │
  ├── sales_orders ── sales_order_items      ├── product_barcodes
  └── vouchers ─── voucher_usages            │                  │
                                             ├── purchase_orders │
                                             │       └── purchase_order_items
                                             │                  │
                                             ├── goods_received_notes
                                             │       └── goods_received_items
                                             │                  │
suppliers ─────────────────────────────────┘    inventory_ledger
                                                     │
                                              stock_takes ── stock_take_items

returns ── return_items
quotes  ── quote_items
discount_types ── discount_audit
audit_log (cross-cutting — references all modules)
settings (singleton — one row per store / future: per branch)
```

---

## 1. users

Stores all staff accounts: owners, managers, and cashiers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | Auto-increment primary key |
| `first_name` | VARCHAR(100) | NOT NULL | First name |
| `last_name` | VARCHAR(100) | NOT NULL | Last name |
| `email` | VARCHAR(255) | NOT NULL · UNIQUE | Login email |
| `password_hash` | VARCHAR(255) | NOT NULL | bcrypt hash — never store plain text |
| `pin` | CHAR(4) | NULLABLE | 4-digit manager override PIN |
| `role` | VARCHAR(20) | NOT NULL · CHECK (`owner`, `manager`, `cashier`) | Access role |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` · CHECK (`active`, `inactive`) | Account status |
| `last_login_at` | TIMESTAMPTZ | NULLABLE | Last successful login |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `deleted_at` | TIMESTAMPTZ | NULLABLE | Soft delete |

**Indexes:** `email` (unique)  
**Notes:**
- `pin` is used for manager override approvals at POS terminal (not for login)
- Store `pin` as bcrypt hash in production, not plain text

---

## 2. customers

Customer profiles with credit, loyalty, and store credit tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `code` | VARCHAR(20) | NOT NULL · UNIQUE | Auto-generated e.g. `CUST-0001` |
| `first_name` | VARCHAR(100) | NOT NULL | |
| `last_name` | VARCHAR(100) | NOT NULL | |
| `phone` | VARCHAR(20) | NOT NULL · UNIQUE | Primary contact — used for lookup |
| `email` | VARCHAR(255) | NULLABLE · UNIQUE | Optional |
| `address` | TEXT | NULLABLE | |
| `customer_type` | VARCHAR(20) | NOT NULL · DEFAULT `regular` · CHECK (`regular`, `vip`, `credit`, `wholesale`) | |
| `credit_limit` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Maximum credit/utang allowed |
| `current_balance` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Outstanding amount owed (utang) |
| `store_credit_balance` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Store credit available for payment |
| `loyalty_points` | INTEGER | NOT NULL · DEFAULT 0 | Accumulated loyalty points |
| `total_spent` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Lifetime purchase total |
| `total_transactions` | INTEGER | NOT NULL · DEFAULT 0 | Lifetime transaction count |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` | `active` · `inactive` |
| `notes` | TEXT | NULLABLE | Internal staff notes |
| `last_visit_at` | DATE | NULLABLE | Date of last purchase |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `deleted_at` | TIMESTAMPTZ | NULLABLE | Soft delete |

**Indexes:** `phone` (unique) · `email` (unique, partial where not null) · `code` (unique) · `customer_type` · `status`  
**Notes:**
- `current_balance` increases on account sales; decreases on payments
- `store_credit_balance` increases when store credit is issued; decreases when used as payment

---

## 3. suppliers

Vendor/supplier records linked to purchase orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `code` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `SUP-0001` |
| `name` | VARCHAR(255) | NOT NULL | Company or individual name |
| `contact_person` | VARCHAR(200) | NULLABLE | |
| `phone` | VARCHAR(20) | NULLABLE | |
| `email` | VARCHAR(255) | NULLABLE | |
| `address` | TEXT | NULLABLE | |
| `payment_terms` | VARCHAR(100) | NULLABLE | e.g. `NET 30`, `COD` |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` | `active` · `inactive` |
| `notes` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `deleted_at` | TIMESTAMPTZ | NULLABLE | |

**Indexes:** `code` (unique) · `name` · `status`

---

## 4. categories

Product categories — flat list (add `parent_id` for tree structure later).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `name` | VARCHAR(100) | NOT NULL · UNIQUE | e.g. `Beverages` |
| `description` | TEXT | NULLABLE | |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` | `active` · `inactive` |
| `sort_order` | INTEGER | NOT NULL · DEFAULT 0 | Display sort order |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `name` (unique) · `status`

---

## 5. products

The product catalog — master table for all inventory items.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `sku` | VARCHAR(50) | NOT NULL · UNIQUE | Stock-keeping unit |
| `barcode` | VARCHAR(50) | NULLABLE · UNIQUE | Primary barcode (EAN/UPC) |
| `name` | VARCHAR(255) | NOT NULL | Product name |
| `description` | TEXT | NULLABLE | |
| `category_id` | BIGINT | FK → categories.id | |
| `brand` | VARCHAR(100) | NULLABLE | Brand name |
| `unit` | VARCHAR(30) | NOT NULL · DEFAULT `Piece` | e.g. `Bottle`, `Pack`, `Kg` |
| `cost_price` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Purchase/landed cost |
| `selling_price` | NUMERIC(12,2) | NOT NULL | Retail selling price |
| `tax_code` | VARCHAR(20) | NOT NULL · DEFAULT `VAT` · CHECK (`VAT`, `ZERO_RATED`, `EXEMPT`) | Tax classification |
| `stock` | INTEGER | NOT NULL · DEFAULT 0 | Current on-hand quantity |
| `reorder_level` | INTEGER | NOT NULL · DEFAULT 0 | Alert when stock ≤ this |
| `track_inventory` | BOOLEAN | NOT NULL · DEFAULT TRUE | FALSE for service items |
| `allow_negative_stock` | BOOLEAN | NOT NULL · DEFAULT FALSE | Override negative stock rule |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` · CHECK (`active`, `inactive`, `archived`) | |
| `image_url` | VARCHAR(500) | NULLABLE | Product image |
| `created_by` | BIGINT | FK → users.id | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `deleted_at` | TIMESTAMPTZ | NULLABLE | |

**Indexes:** `sku` (unique) · `barcode` (unique) · `category_id` · `status` · `name` (GIN/tsvector for full-text search)  
**Notes:**
- `stock` is a denormalized cache — the true balance is always the sum of `inventory_ledger`. Keep in sync via triggers or application logic.
- `tax_code` drives VAT calculation per transaction item

---

## 6. product_barcodes

Allows multiple barcodes per product (supplier barcode, internal barcode, alternate sizes).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `product_id` | BIGINT | NOT NULL · FK → products.id · ON DELETE CASCADE | |
| `barcode` | VARCHAR(50) | NOT NULL · UNIQUE | Must be globally unique |
| `barcode_type` | VARCHAR(30) | NOT NULL · DEFAULT `ean13` | `ean13`, `upc_a`, `qr`, `internal` |
| `is_primary` | BOOLEAN | NOT NULL · DEFAULT FALSE | One primary per product |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `barcode` (unique) · `product_id`

---

## 7. purchase_orders

PO header — an order placed to a supplier.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `po_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `PO-00001` |
| `supplier_id` | BIGINT | NOT NULL · FK → suppliers.id | |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `draft` · CHECK (`draft`, `submitted`, `approved`, `partial`, `received`, `cancelled`) | |
| `order_date` | DATE | NOT NULL | |
| `expected_date` | DATE | NULLABLE | Expected delivery date |
| `subtotal` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Sum of line items before tax |
| `tax_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `notes` | TEXT | NULLABLE | |
| `created_by` | BIGINT | FK → users.id | |
| `approved_by` | BIGINT | NULLABLE · FK → users.id | |
| `approved_at` | TIMESTAMPTZ | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `po_number` (unique) · `supplier_id` · `status` · `order_date`

---

## 8. purchase_order_items

Line items for a purchase order.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `po_id` | BIGINT | NOT NULL · FK → purchase_orders.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `quantity` | INTEGER | NOT NULL · CHECK (> 0) | Ordered quantity |
| `unit_cost` | NUMERIC(12,2) | NOT NULL | Cost at time of order |
| `subtotal` | NUMERIC(12,2) | NOT NULL | `quantity × unit_cost` |
| `received_qty` | INTEGER | NOT NULL · DEFAULT 0 | How many have been received via GRN |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `po_id` · `product_id`

---

## 9. goods_received_notes

GRN header — records actual stock received against a PO.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `grn_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `GRN-00001` |
| `po_id` | BIGINT | NULLABLE · FK → purchase_orders.id | NULL if received without PO |
| `supplier_id` | BIGINT | NOT NULL · FK → suppliers.id | |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `draft` · CHECK (`draft`, `received`, `posted`) | |
| `received_date` | DATE | NOT NULL | |
| `invoice_number` | VARCHAR(100) | NULLABLE | Supplier invoice ref |
| `total_cost` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `notes` | TEXT | NULLABLE | |
| `received_by` | BIGINT | NOT NULL · FK → users.id | |
| `posted_at` | TIMESTAMPTZ | NULLABLE | When stock was posted to inventory |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `grn_number` (unique) · `po_id` · `supplier_id` · `status`

---

## 10. goods_received_items

Line items for a GRN.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `grn_id` | BIGINT | NOT NULL · FK → goods_received_notes.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `po_item_id` | BIGINT | NULLABLE · FK → purchase_order_items.id | Link back to PO line |
| `received_qty` | INTEGER | NOT NULL · CHECK (> 0) | Actual qty received |
| `unit_cost` | NUMERIC(12,2) | NOT NULL | Cost at receiving |
| `subtotal` | NUMERIC(12,2) | NOT NULL | |
| `expiry_date` | DATE | NULLABLE | For perishables |
| `batch_number` | VARCHAR(50) | NULLABLE | Lot/batch tracking |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `grn_id` · `product_id`

---

## 11. inventory_ledger

**The most important inventory table.** One immutable row per stock movement. Never update or delete rows — only append.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `date` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | When movement occurred |
| `type` | VARCHAR(30) | NOT NULL · CHECK (see below) | Movement type |
| `reference_type` | VARCHAR(30) | NULLABLE | `transaction`, `grn`, `return`, `adjustment`, `stock_take`, `transfer` |
| `reference_id` | BIGINT | NULLABLE | FK to the source record's id |
| `reference_number` | VARCHAR(30) | NULLABLE | Human-readable ref e.g. `TXN-0001` |
| `qty_in` | INTEGER | NOT NULL · DEFAULT 0 | Stock added |
| `qty_out` | INTEGER | NOT NULL · DEFAULT 0 | Stock removed |
| `balance_after` | INTEGER | NOT NULL | Running balance after this entry |
| `unit_cost` | NUMERIC(12,2) | NULLABLE | Cost at time of movement (for valuation) |
| `user_id` | BIGINT | NULLABLE · FK → users.id | Who triggered this movement |
| `notes` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**`type` CHECK values:**
`sale` · `void_reversal` · `purchase` · `return` · `adjustment` · `stock_take` · `manual_in` · `manual_out` · `transfer_in` · `transfer_out` · `damage` · `expired`

**Indexes:** `product_id` · `date` · `type` · `reference_number`  
**Notes:**
- `balance_after` = previous balance + `qty_in` - `qty_out`
- This is an **append-only** table — no UPDATE or DELETE allowed
- Use this table as the source of truth for stock counts; `products.stock` is a cache

---

## 12. stock_takes

Physical count session header.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `st_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `ST-00001` |
| `date` | DATE | NOT NULL | Date of count |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `in_progress` · CHECK (`in_progress`, `completed`, `posted`, `cancelled`) | |
| `notes` | TEXT | NULLABLE | |
| `created_by` | BIGINT | NOT NULL · FK → users.id | |
| `completed_at` | TIMESTAMPTZ | NULLABLE | When all items were counted |
| `posted_at` | TIMESTAMPTZ | NULLABLE | When adjustments were applied |
| `posted_by` | BIGINT | NULLABLE · FK → users.id | Manager who approved and posted |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `st_number` (unique) · `status` · `date`

---

## 13. stock_take_items

One row per product per stock take session.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `stock_take_id` | BIGINT | NOT NULL · FK → stock_takes.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `system_qty` | INTEGER | NOT NULL | Stock quantity at time of count creation |
| `counted_qty` | INTEGER | NULLABLE | Physically counted — NULL = not yet counted |
| `variance` | INTEGER | NULLABLE | `counted_qty - system_qty` — computed on save |
| `notes` | TEXT | NULLABLE | e.g. `2 bottles broken` |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `stock_take_id` · `product_id`  
**Unique constraint:** `(stock_take_id, product_id)`

---

## 14. quotes

Price estimate/quotation for a customer.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `quote_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `QOT-00001` |
| `customer_name` | VARCHAR(200) | NULLABLE | Not FK — quotes can be for walk-ins |
| `customer_id` | BIGINT | NULLABLE · FK → customers.id | |
| `phone` | VARCHAR(20) | NULLABLE | |
| `date` | DATE | NOT NULL | Quote date |
| `valid_until` | DATE | NULLABLE | Expiry date |
| `subtotal` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `discount_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `tax_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `draft` · CHECK (`draft`, `sent`, `expired`, `converted`, `cancelled`) | |
| `notes` | TEXT | NULLABLE | |
| `converted_transaction_id` | BIGINT | NULLABLE · FK → transactions.id | Set when converted to sale |
| `created_by` | BIGINT | NOT NULL · FK → users.id | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `quote_number` (unique) · `status` · `customer_id` · `date`

---

## 15. quote_items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `quote_id` | BIGINT | NOT NULL · FK → quotes.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `product_name` | VARCHAR(255) | NOT NULL | Snapshot at quote time |
| `unit` | VARCHAR(30) | NOT NULL | |
| `quantity` | INTEGER | NOT NULL · CHECK (> 0) | |
| `unit_price` | NUMERIC(12,2) | NOT NULL | Selling price at quote time |
| `line_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | |
| `line_discount_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `subtotal` | NUMERIC(12,2) | NOT NULL | After discount |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `quote_id` · `product_id`

---

## 16. sales_orders

Customer reservation — stock is earmarked but not yet sold.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `so_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `SO-00001` |
| `customer_name` | VARCHAR(200) | NULLABLE | |
| `customer_id` | BIGINT | NULLABLE · FK → customers.id | |
| `phone` | VARCHAR(20) | NULLABLE | |
| `order_date` | DATE | NOT NULL | |
| `expected_pickup` | DATE | NULLABLE | |
| `deposit_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Deposit paid |
| `subtotal` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `balance_due` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | `total - deposit_amount` |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `draft` · CHECK (`draft`, `confirmed`, `converted`, `cancelled`) | |
| `notes` | TEXT | NULLABLE | |
| `converted_transaction_id` | BIGINT | NULLABLE · FK → transactions.id | |
| `created_by` | BIGINT | NOT NULL · FK → users.id | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `so_number` (unique) · `status` · `customer_id` · `order_date`

---

## 17. sales_order_items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `so_id` | BIGINT | NOT NULL · FK → sales_orders.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NOT NULL · FK → products.id | |
| `product_name` | VARCHAR(255) | NOT NULL | Snapshot |
| `unit` | VARCHAR(30) | NOT NULL | |
| `quantity` | INTEGER | NOT NULL · CHECK (> 0) | |
| `unit_price` | NUMERIC(12,2) | NOT NULL | |
| `line_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | |
| `subtotal` | NUMERIC(12,2) | NOT NULL | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `so_id` · `product_id`

---

## 18. transactions

**The heart of the POS.** One row per completed sale.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `receipt_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `TXN-00001` |
| `date` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | Transaction timestamp |
| `cashier_id` | BIGINT | NOT NULL · FK → users.id | Who processed the sale |
| `customer_id` | BIGINT | NULLABLE · FK → customers.id | |
| `customer_name` | VARCHAR(200) | NULLABLE | Snapshot (customer may not be registered) |
| `shift_id` | BIGINT | NULLABLE · FK → shifts.id | Which shift this belongs to |
| `subtotal` | NUMERIC(12,2) | NOT NULL | Sum of item line totals before txn discount |
| `line_discounts_total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Sum of all per-item discounts |
| `transaction_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | Whole-transaction discount % |
| `transaction_discount_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Computed |
| `transaction_discount_type` | VARCHAR(30) | NULLABLE | FK or enum for discount type |
| `tax_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `tax_rate` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | VAT rate applied |
| `tax_type` | VARCHAR(20) | NOT NULL · DEFAULT `exclusive` · CHECK (`inclusive`, `exclusive`) | |
| `total` | NUMERIC(12,2) | NOT NULL | Final amount charged |
| `amount_paid` | NUMERIC(12,2) | NOT NULL | Sum of all payments |
| `change_given` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | `amount_paid - total` |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `completed` · CHECK (`completed`, `voided`, `held`) | |
| `source` | VARCHAR(20) | NULLABLE · DEFAULT `pos` | `pos`, `so` (from sales order), `quote` |
| `source_id` | BIGINT | NULLABLE | FK to `sales_orders.id` or `quotes.id` |
| `voided_at` | TIMESTAMPTZ | NULLABLE | |
| `voided_by` | BIGINT | NULLABLE · FK → users.id | |
| `void_reason` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `receipt_number` (unique) · `date` · `cashier_id` · `customer_id` · `shift_id` · `status`  
**Notes:**
- `subtotal` = sum of `transaction_items.line_total` before `transaction_discount_amount`
- `total` = `subtotal - transaction_discount_amount + tax_amount` (if exclusive)

---

## 19. transaction_items

One row per product per transaction.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `transaction_id` | BIGINT | NOT NULL · FK → transactions.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NULLABLE · FK → products.id | NULL if product deleted |
| `product_name` | VARCHAR(255) | NOT NULL | Snapshot — never changes |
| `sku` | VARCHAR(50) | NOT NULL | Snapshot |
| `category` | VARCHAR(100) | NULLABLE | Snapshot |
| `unit` | VARCHAR(30) | NOT NULL | |
| `quantity` | INTEGER | NOT NULL · CHECK (> 0) | |
| `unit_price` | NUMERIC(12,2) | NOT NULL | Selling price at time of sale |
| `original_unit_price` | NUMERIC(12,2) | NULLABLE | Set if price was overridden |
| `line_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | |
| `line_discount_type` | VARCHAR(30) | NULLABLE | Discount type id |
| `line_discount_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | |
| `line_total` | NUMERIC(12,2) | NOT NULL | `(unit_price × qty) - line_discount_amount` |
| `tax_code` | VARCHAR(20) | NULLABLE | Product tax code at time of sale |
| `price_overridden` | BOOLEAN | NOT NULL · DEFAULT FALSE | |
| `price_override_approved_by` | BIGINT | NULLABLE · FK → users.id | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `transaction_id` · `product_id`

---

## 20. payments

One row per payment method per transaction. A split payment creates multiple rows.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `payment_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `PMT-00001` |
| `transaction_id` | BIGINT | NOT NULL · FK → transactions.id · ON DELETE CASCADE | |
| `date` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `payment_method` | VARCHAR(30) | NOT NULL · CHECK (see below) | |
| `amount` | NUMERIC(12,2) | NOT NULL · CHECK (> 0) | |
| `reference_number` | VARCHAR(100) | NULLABLE | For bank transfer, card — required |
| `voucher_code` | VARCHAR(20) | NULLABLE | FK to `vouchers.code` if Gift Voucher |
| `is_electronic` | BOOLEAN | NOT NULL · DEFAULT FALSE | Computed from `payment_method` |
| `cashier_id` | BIGINT | NULLABLE · FK → users.id | |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `completed` · CHECK (`completed`, `voided`) | |
| `voided_at` | TIMESTAMPTZ | NULLABLE | |
| `voided_by` | BIGINT | NULLABLE · FK → users.id | |
| `void_reason` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**`payment_method` CHECK values:**  
`Cash` · `GCash` · `Maya` · `QRPH` · `Debit` · `Credit` · `Store Credit` · `Bank Transfer` · `Gift Voucher` · `Account`

**Indexes:** `payment_number` (unique) · `transaction_id` · `payment_method` · `date` · `status`

---

## 21. returns

Return/refund record — linked to the original transaction.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `return_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `RTN-00001` |
| `transaction_id` | BIGINT | NULLABLE · FK → transactions.id | Original sale — NULL if no receipt |
| `date` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `customer_id` | BIGINT | NULLABLE · FK → customers.id | |
| `customer_name` | VARCHAR(200) | NULLABLE | |
| `return_type` | VARCHAR(20) | NOT NULL · CHECK (`refund`, `exchange`) | |
| `reason` | VARCHAR(200) | NOT NULL | |
| `refund_amount` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Total refund value |
| `refund_method` | VARCHAR(30) | NULLABLE | How refund was given |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `pending` · CHECK (`pending`, `approved`, `completed`, `rejected`) | |
| `stock_restored` | BOOLEAN | NOT NULL · DEFAULT FALSE | Whether stock was returned to inventory |
| `created_by` | BIGINT | NOT NULL · FK → users.id | |
| `approved_by` | BIGINT | NULLABLE · FK → users.id | |
| `approved_at` | TIMESTAMPTZ | NULLABLE | |
| `completed_by` | BIGINT | NULLABLE · FK → users.id | |
| `completed_at` | TIMESTAMPTZ | NULLABLE | |
| `rejected_by` | BIGINT | NULLABLE · FK → users.id | |
| `rejected_at` | TIMESTAMPTZ | NULLABLE | |
| `reject_reason` | TEXT | NULLABLE | |
| `notes` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `return_number` (unique) · `transaction_id` · `status` · `date`

---

## 22. return_items

Items being returned in a return record.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `return_id` | BIGINT | NOT NULL · FK → returns.id · ON DELETE CASCADE | |
| `product_id` | BIGINT | NULLABLE · FK → products.id | |
| `product_name` | VARCHAR(255) | NOT NULL | Snapshot |
| `sku` | VARCHAR(50) | NULLABLE | |
| `unit` | VARCHAR(30) | NOT NULL | |
| `original_qty` | INTEGER | NOT NULL | Qty in original transaction |
| `return_qty` | INTEGER | NOT NULL · CHECK (> 0) | Qty being returned |
| `unit_price` | NUMERIC(12,2) | NOT NULL | Price at original sale |
| `line_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | |
| `refund_amount` | NUMERIC(12,2) | NOT NULL | Computed: `unit_price × return_qty × (1 - discount)` |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `return_id` · `product_id`

---

## 23. vouchers

Gift voucher issuance and balance tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `code` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `GV-ABCD1234` |
| `original_amount` | NUMERIC(12,2) | NOT NULL · CHECK (> 0) | Face value |
| `remaining_balance` | NUMERIC(12,2) | NOT NULL | Current balance |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `active` · CHECK (`active`, `partially_used`, `redeemed`, `expired`, `cancelled`) | |
| `issued_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `expires_at` | DATE | NULLABLE | NULL = no expiry |
| `issued_by` | BIGINT | NOT NULL · FK → users.id | |
| `issued_to_name` | VARCHAR(200) | NULLABLE | Optional recipient name |
| `issued_to_customer_id` | BIGINT | NULLABLE · FK → customers.id | |
| `notes` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `code` (unique) · `status` · `expires_at`

---

## 24. voucher_usages

Append-only redemption history per voucher.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `voucher_id` | BIGINT | NOT NULL · FK → vouchers.id | |
| `transaction_id` | BIGINT | NULLABLE · FK → transactions.id | NULL while pending |
| `date` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `amount_used` | NUMERIC(12,2) | NOT NULL · CHECK (> 0) | |
| `balance_after` | NUMERIC(12,2) | NOT NULL | |
| `redeemed_by` | BIGINT | NOT NULL · FK → users.id | Cashier |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `voucher_id` · `transaction_id`

---

## 25. discount_types

Configurable discount types (Senior Citizen, PWD, Promo, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `code` | VARCHAR(30) | NOT NULL · UNIQUE | e.g. `senior`, `pwd`, `promo` |
| `name` | VARCHAR(100) | NOT NULL | Display name |
| `description` | TEXT | NULLABLE | |
| `default_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 0 | Pre-fill percentage |
| `max_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 100 | Maximum allowed % |
| `is_custom_pct` | BOOLEAN | NOT NULL · DEFAULT FALSE | Whether cashier can change the % |
| `requires_id` | BOOLEAN | NOT NULL · DEFAULT FALSE | Requires customer ID number |
| `requires_reason` | BOOLEAN | NOT NULL · DEFAULT FALSE | Requires reason text |
| `requires_override` | BOOLEAN | NOT NULL · DEFAULT FALSE | Always requires manager override |
| `is_active` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `sort_order` | INTEGER | NOT NULL · DEFAULT 0 | |
| `color` | VARCHAR(20) | NULLABLE | UI color hint |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `code` (unique) · `is_active`

---

## 26. discount_audit

Every discount applied — the compliance and exception report source.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `transaction_id` | BIGINT | NULLABLE · FK → transactions.id | |
| `discount_type_id` | BIGINT | NULLABLE · FK → discount_types.id | NULL for custom % |
| `type_name` | VARCHAR(100) | NOT NULL | Snapshot |
| `scope` | VARCHAR(20) | NOT NULL · CHECK (`line`, `transaction`) | Per-item or whole transaction |
| `transaction_item_id` | BIGINT | NULLABLE · FK → transaction_items.id | Set for line-scope |
| `product_name` | VARCHAR(255) | NULLABLE | Snapshot for line-scope |
| `applied_by` | BIGINT | NOT NULL · FK → users.id | Cashier |
| `applied_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `discount_pct` | NUMERIC(5,2) | NOT NULL | |
| `original_amount` | NUMERIC(12,2) | NOT NULL | Before discount |
| `discount_amount` | NUMERIC(12,2) | NOT NULL | Amount saved |
| `final_amount` | NUMERIC(12,2) | NOT NULL | After discount |
| `reason` | TEXT | NULLABLE | |
| `id_number` | VARCHAR(50) | NULLABLE | Customer ID for Senior/PWD |
| `overridden_by` | BIGINT | NULLABLE · FK → users.id | Manager who approved override |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `transaction_id` · `applied_by` · `applied_at` · `discount_type_id`

---

## 27. shifts

Cashier shift management — one row per open/close cycle.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `shift_number` | VARCHAR(20) | NOT NULL · UNIQUE | e.g. `SFT-00001` |
| `cashier_id` | BIGINT | NOT NULL · FK → users.id | |
| `opened_at` | TIMESTAMPTZ | NOT NULL | Shift start time |
| `closed_at` | TIMESTAMPTZ | NULLABLE | NULL if shift still open |
| `closed_by` | BIGINT | NULLABLE · FK → users.id | Could be different user (manager close) |
| `opening_cash` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Starting float |
| `cash_sales` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Computed on close: total cash received |
| `change_given` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Computed: total change returned |
| `cash_in_total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Sum of manual cash-in movements |
| `cash_out_total` | NUMERIC(12,2) | NOT NULL · DEFAULT 0 | Sum of manual cash-out movements |
| `expected_cash` | NUMERIC(12,2) | NULLABLE | Computed on close |
| `counted_cash` | NUMERIC(12,2) | NULLABLE | Actual cash counted by cashier |
| `variance` | NUMERIC(12,2) | NULLABLE | `counted_cash - expected_cash` |
| `notes` | TEXT | NULLABLE | Closing notes |
| `status` | VARCHAR(20) | NOT NULL · DEFAULT `open` · CHECK (`open`, `closed`) | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `shift_number` (unique) · `cashier_id` · `status` · `opened_at`

---

## 28. shift_movements

Manual cash in/out entries within a shift.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `shift_id` | BIGINT | NOT NULL · FK → shifts.id · ON DELETE CASCADE | |
| `type` | VARCHAR(10) | NOT NULL · CHECK (`in`, `out`) | |
| `amount` | NUMERIC(12,2) | NOT NULL · CHECK (> 0) | |
| `reason` | VARCHAR(255) | NOT NULL | e.g. `Add change money`, `Buy supplies` |
| `recorded_by` | BIGINT | NOT NULL · FK → users.id | |
| `recorded_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Indexes:** `shift_id` · `type`

---

## 29. audit_log

**System-wide immutable audit trail.** Never update or delete rows.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `timestamp` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | When the event occurred |
| `user_id` | BIGINT | NULLABLE · FK → users.id | NULL for system events |
| `user_name` | VARCHAR(200) | NOT NULL | Snapshot — user may be deleted |
| `user_role` | VARCHAR(20) | NULLABLE | Snapshot |
| `action` | VARCHAR(50) | NOT NULL | See action codes below |
| `module` | VARCHAR(50) | NOT NULL | e.g. `POS`, `Transactions`, `Auth` |
| `description` | TEXT | NOT NULL | Human-readable summary |
| `reference_type` | VARCHAR(30) | NULLABLE | e.g. `transaction`, `return`, `shift` |
| `reference_id` | BIGINT | NULLABLE | FK value |
| `reference_number` | VARCHAR(30) | NULLABLE | Human-readable ref |
| `severity` | VARCHAR(10) | NOT NULL · DEFAULT `info` · CHECK (`info`, `warning`, `critical`) | |
| `details` | JSONB | NULLABLE | Structured before/after data |
| `ip_address` | INET | NULLABLE | Client IP |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

**Action codes:**  
`LOGIN` · `LOGOUT` · `SHIFT_OPENED` · `SHIFT_CLOSED`  
`TRANSACTION_COMPLETED` · `TRANSACTION_VOIDED`  
`DISCOUNT_APPLIED` · `PRICE_OVERRIDE`  
`RETURN_CREATED` · `RETURN_APPROVED` · `RETURN_COMPLETED`  
`STOCK_TAKE_POSTED` · `INVENTORY_ADJUSTED`  
`USER_CREATED` · `USER_UPDATED` · `USER_DEACTIVATED`  
`SETTINGS_CHANGED` · `CUSTOMER_CREATED` · `CUSTOMER_UPDATED`  
`VOUCHER_ISSUED` · `VOUCHER_REDEEMED` · `VOUCHER_CANCELLED`

**Indexes:** `timestamp` · `user_id` · `action` · `module` · `severity` · `reference_number`  
**Notes:**
- Use **table partitioning by month** once data grows large
- Index `details` JSONB for specific field lookups if needed

---

## 30. settings

Store configuration — one row per store (future: one row per branch).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGSERIAL | PK | |
| `store_name` | VARCHAR(255) | NOT NULL · DEFAULT `PabiliPOS Store` | |
| `store_address` | TEXT | NULLABLE | |
| `store_phone` | VARCHAR(20) | NULLABLE | |
| `store_email` | VARCHAR(255) | NULLABLE | |
| `store_tin` | VARCHAR(20) | NULLABLE | Tax Identification Number |
| `currency` | CHAR(3) | NOT NULL · DEFAULT `PHP` | ISO 4217 |
| `timezone` | VARCHAR(50) | NOT NULL · DEFAULT `Asia/Manila` | |
| `receipt_header` | TEXT | NULLABLE | Custom receipt top text |
| `receipt_footer` | TEXT | NULLABLE · DEFAULT `Thank you for shopping!` | |
| `tax_enabled` | BOOLEAN | NOT NULL · DEFAULT FALSE | |
| `tax_name` | VARCHAR(20) | NOT NULL · DEFAULT `VAT` | |
| `tax_rate` | NUMERIC(5,2) | NOT NULL · DEFAULT 12 | |
| `tax_type` | VARCHAR(20) | NOT NULL · DEFAULT `exclusive` · CHECK (`inclusive`, `exclusive`) | |
| `max_cashier_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 10 | Max % cashier can give without override |
| `max_manager_discount_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 30 | |
| `require_manager_approval_above_pct` | NUMERIC(5,2) | NOT NULL · DEFAULT 5 | |
| `round_to_two_decimals` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `allow_negative_stock` | BOOLEAN | NOT NULL · DEFAULT FALSE | |
| `require_shift_for_sales` | BOOLEAN | NOT NULL · DEFAULT TRUE | Block POS if no open shift |
| `show_cashier_on_receipt` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `show_customer_on_receipt` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `show_tax_breakdown_on_receipt` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `show_discount_breakdown_on_receipt` | BOOLEAN | NOT NULL · DEFAULT TRUE | |
| `loyalty_points_per_peso` | NUMERIC(5,3) | NOT NULL · DEFAULT 0.1 | Points earned per ₱1 spent |
| `loyalty_redeem_rate` | NUMERIC(5,3) | NOT NULL · DEFAULT 1.0 | Pesos per point redeemed |
| `created_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |
| `updated_at` | TIMESTAMPTZ | NOT NULL · DEFAULT NOW() | |

---

## Relationships Summary

```
users           → transactions (cashier_id, voided_by)
users           → shifts (cashier_id)
users           → purchase_orders (created_by, approved_by)
users           → returns (created_by, approved_by, completed_by)
users           → vouchers (issued_by)
users           → audit_log (user_id)
users           → discount_audit (applied_by, overridden_by)

customers       → transactions (customer_id)
customers       → returns (customer_id)
customers       → sales_orders (customer_id)
customers       → quotes (customer_id)
customers       → vouchers (issued_to_customer_id)

suppliers       → purchase_orders (supplier_id)
suppliers       → goods_received_notes (supplier_id)

categories      → products (category_id)

products        → product_barcodes (product_id)
products        → purchase_order_items (product_id)
products        → goods_received_items (product_id)
products        → inventory_ledger (product_id)
products        → stock_take_items (product_id)
products        → transaction_items (product_id)
products        → quote_items (product_id)
products        → sales_order_items (product_id)
products        → return_items (product_id)

purchase_orders → purchase_order_items (po_id)
purchase_orders → goods_received_notes (po_id)

goods_received_notes → goods_received_items (grn_id)

transactions    → transaction_items (transaction_id)
transactions    → payments (transaction_id)
transactions    → discount_audit (transaction_id)
transactions    → returns (transaction_id)
transactions    → quotes.converted_transaction_id
transactions    → sales_orders.converted_transaction_id

shifts          → transactions (shift_id)
shifts          → shift_movements (shift_id)

vouchers        → voucher_usages (voucher_id)
discount_types  → discount_audit (discount_type_id)
returns         → return_items (return_id)
stock_takes     → stock_take_items (stock_take_id)
quotes          → quote_items (quote_id)
sales_orders    → sales_order_items (so_id)
```

---

## Recommended Indexes

```sql
-- High-traffic lookup indexes
CREATE INDEX idx_transactions_date ON transactions(date);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_cashier ON transactions(cashier_id);
CREATE INDEX idx_transaction_items_product ON transaction_items(product_id);
CREATE INDEX idx_payments_date ON payments(date);
CREATE INDEX idx_payments_method ON payments(payment_method);
CREATE INDEX idx_inventory_ledger_product_date ON inventory_ledger(product_id, date DESC);
CREATE INDEX idx_audit_log_timestamp ON audit_log(timestamp DESC);
CREATE INDEX idx_audit_log_severity ON audit_log(severity) WHERE severity = 'critical';

-- Full-text search
CREATE INDEX idx_products_name_fts ON products USING GIN(to_tsvector('english', name));
CREATE INDEX idx_customers_name_fts ON customers USING GIN(to_tsvector('english', first_name || ' ' || last_name));
```

---

## Business Rules to Enforce at Backend

| Rule | Where to Enforce |
|------|-----------------|
| `products.stock` must equal sum of `inventory_ledger` for that product | Trigger or scheduled job |
| `vouchers.remaining_balance` must never go below 0 | CHECK constraint + application |
| `customers.current_balance` must never exceed `credit_limit` (unless allowed) | Application + optional CHECK |
| Voiding a transaction also voids all its `payments` | Application transaction |
| Posting a `stock_take` creates `inventory_ledger` entries and updates `products.stock` | Application transaction |
| `audit_log` rows must never be updated or deleted | Role permissions (`REVOKE UPDATE, DELETE`) |
| `inventory_ledger` rows must never be updated or deleted | Role permissions (`REVOKE UPDATE, DELETE`) |
| Only one `shift` per `cashier_id` can have `status = open` at a time | UNIQUE partial index |

```sql
-- Only one open shift per cashier at a time
CREATE UNIQUE INDEX idx_shifts_one_open_per_cashier
  ON shifts(cashier_id)
  WHERE status = 'open';
```

---

## Future Tables (Multi-Branch / SaaS)

When adding multi-branch support, add `branch_id` to these tables:

- `transactions` · `payments` · `shifts` · `inventory_ledger`
- `stock_takes` · `purchase_orders` · `goods_received_notes`
- `products` (or a separate `product_branch_stock` table)

New tables needed:
- `branches` — branch registry
- `stock_transfers` — inter-branch stock movement
- `stock_transfer_items`

---

*Generated from PabiliPOS frontend data models · June 2026*
