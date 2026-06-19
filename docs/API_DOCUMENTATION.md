# PabiliPOS Backend API

Complete Point of Sale (POS) system backend built with Laravel, featuring inventory management, customer relations, purchase orders, transactions, and comprehensive auditing.

## Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js (for frontend)

### Installation

1. **Clone and install dependencies:**
```bash
composer install
npm install
```

2. **Configure environment:**
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pabilipos
DB_USERNAME=root
DB_PASSWORD=
```

3. **Run migrations:**
```bash
php artisan migrate
```

4. **Start the development server:**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Database Schema

The backend uses 30 interconnected tables:

### Core Tables
- **users** - Staff accounts (owners, managers, cashiers)
- **customers** - Customer profiles with credit tracking
- **suppliers** - Vendor information
- **products** - Product catalog with SKU and barcodes
- **categories** - Product categorization
- **settings** - Store configuration

### Inventory Management
- **inventory_ledger** - Immutable stock movement history
- **stock_takes** - Physical inventory counts
- **stock_take_items** - Individual items in stock takes
- **product_barcodes** - Multiple barcodes per product

### Purchase Management
- **purchase_orders** - Orders placed to suppliers
- **purchase_order_items** - Line items in POs
- **goods_received_notes** - Stock received from suppliers
- **goods_received_items** - Items received in GRNs

### Sales & Transactions
- **transactions** - Completed POS sales (receipts)
- **transaction_items** - Products sold in transactions
- **payments** - Payment methods per transaction
- **quotes** - Price quotations for customers
- **quote_items** - Items in quotations
- **sales_orders** - Customer reservations
- **sales_order_items** - Items in sales orders

### Returns & Refunds
- **returns** - Return/refund records
- **return_items** - Products being returned

### Promotions & Discounts
- **vouchers** - Gift vouchers
- **voucher_usages** - Redemption history
- **discount_types** - Configurable discount categories
- **discount_audit** - All discounts applied (compliance)

### Shift Management
- **shifts** - Cashier shift sessions
- **shift_movements** - Manual cash in/out within shifts

### Auditing
- **audit_log** - System-wide immutable audit trail

## API Authentication

The API uses **Sanctum token-based authentication**:

### Login
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "role": "owner"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

**Use token in subsequent requests:**
```bash
Authorization: Bearer {token}
```

### Register User
```bash
POST /api/auth/register
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "password": "password",
  "password_confirmation": "password",
  "role": "cashier"
}
```

### Logout
```bash
POST /api/auth/logout
Authorization: Bearer {token}
```

## API Endpoints

### Products
```
GET    /api/products                          # List all products
POST   /api/products                          # Create product
GET    /api/products/{id}                     # Get product details
PUT    /api/products/{id}                     # Update product
DELETE /api/products/{id}                     # Delete product
GET    /api/products/search/{term}            # Search products
GET    /api/products/barcode/{barcode}        # Search by barcode
```

### Customers
```
GET    /api/customers                         # List customers
POST   /api/customers                         # Create customer
GET    /api/customers/{id}                    # Get customer
PUT    /api/customers/{id}                    # Update customer
DELETE /api/customers/{id}                    # Delete customer
GET    /api/customers/search/{term}           # Search customers
POST   /api/customers/{id}/credit             # Update customer credit
```

### Transactions (POS Sales)
```
GET    /api/transactions                      # List transactions
POST   /api/transactions                      # Create transaction (new sale)
GET    /api/transactions/{id}                 # Get transaction details
POST   /api/transactions/{id}/void            # Void transaction
GET    /api/transactions/receipt/{number}     # Get by receipt number
```

### Purchase Orders
```
GET    /api/purchase-orders                   # List POs
POST   /api/purchase-orders                   # Create PO
GET    /api/purchase-orders/{id}              # Get PO details
PUT    /api/purchase-orders/{id}              # Update PO
POST   /api/purchase-orders/{id}/approve      # Approve PO
POST   /api/purchase-orders/{id}/cancel       # Cancel PO
DELETE /api/purchase-orders/{id}              # Delete PO
```

### Goods Received Notes
```
GET    /api/goods-received-notes              # List GRNs
POST   /api/goods-received-notes              # Create GRN
GET    /api/goods-received-notes/{id}         # Get GRN details
POST   /api/goods-received-notes/{id}/post    # Post GRN (update inventory)
```

### Returns
```
GET    /api/returns                           # List returns
POST   /api/returns                           # Create return
GET    /api/returns/{id}                      # Get return details
POST   /api/returns/{id}/approve              # Approve return
POST   /api/returns/{id}/complete             # Complete return
POST   /api/returns/{id}/reject               # Reject return
```

### Shifts
```
GET    /api/shifts                            # List shifts
POST   /api/shifts                            # Open shift
GET    /api/shifts/{id}                       # Get shift details
POST   /api/shifts/{id}/close                 # Close shift
GET    /api/shifts/active/current             # Get current active shift
POST   /api/shifts/{id}/movements             # Record cash in/out
```

### Reports
```
GET    /api/reports/sales?start_date=...&end_date=...
GET    /api/reports/inventory
GET    /api/reports/audit-log
```

### Settings
```
GET    /api/settings                          # Get store settings
PUT    /api/settings                          # Update settings
```

## Example Workflow: Complete a Sale

### 1. Search for product by barcode
```bash
GET /api/products/barcode/1234567890
```

### 2. Open a shift (start of day)
```bash
POST /api/shifts
{
  "cashier_id": 1,
  "opening_cash": 5000
}
```

### 3. Create a transaction
```bash
POST /api/transactions
{
  "cashier_id": 1,
  "customer_id": null,
  "shift_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": 99.99
    }
  ],
  "payments": [
    {
      "payment_method": "Cash",
      "amount": 200
    }
  ]
}
```

### 4. Close the shift (end of day)
```bash
POST /api/shifts/1/close
{
  "counted_cash": 5250
}
```

## Key Features

### Inventory Management
- Real-time stock tracking via append-only ledger
- Multiple barcodes per product support
- Automatic stock adjustments from purchases and sales
- Physical stock count reconciliation

### Transaction Handling
- Complete transaction history with immutable records
- Multi-payment support (cash, credit card, e-wallet, etc.)
- Discount tracking and audit trail
- Tax calculation (inclusive/exclusive)
- Price override logging

### Customer Management
- Customer credit limits and tracking
- Loyalty points system
- Store credit balance management
- Customer purchase history

### Compliance & Auditing
- Complete audit log of all actions
- Discount audit trail for compliance
- Immutable transaction records
- User access logging

### Shift Management
- Cashier shift opening/closing
- Float management
- Cash reconciliation
- Manual cash in/out tracking

### Promotions
- Configurable discount types
- Gift voucher management
- Partial voucher redemption tracking
- Voucher expiry management

## Error Handling

All API responses follow a consistent format:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

HTTP Status Codes:
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Database Relationships

Key relationships are:
- **Users** → Transactions (cashier)
- **Products** → Categories
- **Products** → Inventory Ledger (stock tracking)
- **Customers** → Transactions (purchases)
- **Customers** → Returns (refunds)
- **Suppliers** → Purchase Orders
- **Purchase Orders** → Goods Received Notes

## Conventions

- Table names: `snake_case`
- Column names: `snake_case`
- Primary keys: BIGSERIAL (auto-increment)
- Soft deletes: `deleted_at` column
- Timestamps: `created_at`, `updated_at`
- Currency: PHP (₱) in decimals (12,2)
- Status fields: lowercase values

## Running Tests

```bash
php artisan test
```

## Deployment

### Production Setup

1. **Update environment:**
```bash
APP_ENV=production
APP_DEBUG=false
```

2. **Optimize for production:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Set file permissions:**
```bash
chmod -R 775 storage bootstrap/cache
```

## Common Issues

### "SQLSTATE[HY000]: General error: 1030 Got error..."
Increase MySQL max_allowed_packet:
```bash
# Edit /etc/mysql/my.cnf
max_allowed_packet=256M
```

### Token expiration
Tokens are valid for 24 hours. Request a new token when expired.

## License

This project is proprietary software. All rights reserved.

## Support

For issues or questions, contact the development team.
