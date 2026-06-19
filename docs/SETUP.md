# PabiliPOS Backend Setup Guide

## System Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer 2.0+
- Node.js 16+ (optional, for build tools)
- 2GB RAM minimum
- 500MB disk space

## Installation Steps

### 1. Clone the Repository
```bash
git clone <repository-url>
cd pabili-pos-backend
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` file with your configuration:
```env
APP_NAME=PabiliPOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pabilipos
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Create Database
```bash
mysql -u root -p
CREATE DATABASE pabilipos;
exit;
```

### 6. Run Migrations
```bash
php artisan migrate
```

This will create all 30 tables in the database.

### 7. Create Admin User (Optional)
```bash
php artisan tinker
```

In the tinker shell:
```php
use App\Models\User;
User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@example.com',
    'password_hash' => bcrypt('password'),
    'role' => 'owner',
    'status' => 'active',
]);
```

### 8. Start Development Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Database Structure

The backend includes 30 interconnected tables organized into logical groups:

### User Management (1 table)
- `users` - Staff accounts

### Product Management (3 tables)
- `products` - Product catalog
- `categories` - Product categories  
- `product_barcodes` - Multiple barcodes per product

### Supplier Management (1 table)
- `suppliers` - Vendor information

### Customer Management (1 table)
- `customers` - Customer profiles

### Inventory Management (4 tables)
- `inventory_ledger` - Stock movements (append-only)
- `stock_takes` - Physical inventory counts
- `stock_take_items` - Items in stock takes

### Purchase Management (4 tables)
- `purchase_orders` - PO headers
- `purchase_order_items` - PO line items
- `goods_received_notes` - GRN headers
- `goods_received_items` - GRN line items

### Sales Management (7 tables)
- `transactions` - POS transactions
- `transaction_items` - Items sold
- `payments` - Payment records
- `quotes` - Price quotations
- `quote_items` - Items in quotes
- `sales_orders` - Customer reservations
- `sales_order_items` - Items in sales orders

### Return Management (2 tables)
- `returns` - Return records
- `return_items` - Items being returned

### Promotion Management (4 tables)
- `vouchers` - Gift vouchers
- `voucher_usages` - Redemption history
- `discount_types` - Discount categories
- `discount_audit` - Discount audit trail

### Shift Management (2 tables)
- `shifts` - Cashier shifts
- `shift_movements` - Cash in/out

### System (2 tables)
- `audit_log` - Complete audit trail
- `settings` - Store configuration

## Key Database Features

### Append-Only Design
The `inventory_ledger` table uses append-only pattern:
- Never update or delete existing entries
- Always append new entries for stock movements
- Provides complete audit trail of inventory changes
- Ensures data integrity

### Soft Deletes
Tables like `products`, `customers`, `suppliers` support soft deletes:
- Deleted records remain in database with `deleted_at` timestamp
- Prevents referential integrity issues
- Allows data recovery if needed

### Immutable Records
- `audit_log` - System-wide action tracking
- `inventory_ledger` - Stock movement history
- `discount_audit` - Discount compliance
- `transaction_items` - Sale item records

### Status Tracking
Most entities use `status` column to track states:
- Products: active, inactive, archived
- Orders: draft, submitted, approved, partial, received, cancelled
- Transactions: completed, voided, held
- Shifts: open, closed

## Initial Data Setup

### Create Categories
```bash
php artisan tinker
```

```php
use App\Models\Category;

Category::create([
    'name' => 'Beverages',
    'sort_order' => 1,
]);

Category::create([
    'name' => 'Snacks',
    'sort_order' => 2,
]);

Category::create([
    'name' => 'Groceries',
    'sort_order' => 3,
]);
```

### Create Discount Types
```php
use App\Models\DiscountType;

DiscountType::create([
    'code' => 'senior',
    'name' => 'Senior Citizen',
    'default_pct' => 20,
    'max_pct' => 20,
    'requires_id' => true,
]);

DiscountType::create([
    'code' => 'pwd',
    'name' => 'PWD',
    'default_pct' => 20,
    'max_pct' => 20,
    'requires_id' => true,
]);
```

### Create Store Settings
```php
use App\Models\Setting;

Setting::create([
    'store_name' => 'My Store',
    'store_address' => '123 Main Street',
    'store_phone' => '+63-2-1234-5678',
    'store_email' => 'store@example.com',
    'store_tin' => '000-000-000-000',
    'currency' => 'PHP',
    'timezone' => 'Asia/Manila',
    'tax_enabled' => true,
    'tax_rate' => 12,
    'tax_type' => 'exclusive',
]);
```

## API Testing

### Using cURL
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Get Products
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Postman
1. Import the API collection from `docs/postman_collection.json`
2. Set environment variables (base_url, token)
3. Start making requests

## Troubleshooting

### Migration Failed
```bash
# Reset migrations
php artisan migrate:reset

# Re-run migrations
php artisan migrate
```

### Database Connection Error
- Check MySQL is running
- Verify `.env` database credentials
- Check database exists

### Token Invalid
- Ensure you're using Bearer token in Authorization header
- Check token hasn't expired (24 hours)
- Re-login to get a new token

### Permission Errors
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

## Performance Optimization

### Indexing
All critical columns are indexed:
- Foreign keys
- Status columns
- Date columns
- Search columns (email, phone, code)

### Query Optimization
- Use `with()` for eager loading relationships
- Paginate large result sets
- Add database indices for custom searches

### Caching (Optional)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Backup & Recovery

### Backup Database
```bash
mysqldump -u root -p pabilipos > backup.sql
```

### Restore Database
```bash
mysql -u root -p pabilipos < backup.sql
```

## Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure `.env` for production database
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Set file permissions (storage, bootstrap)
- [ ] Configure reverse proxy (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure firewall rules
- [ ] Set up monitoring/logging
- [ ] Configure backups

## Support & Documentation

- API Documentation: See [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- Database Schema: See [docs/BACKEND_DATABASE.md](docs/BACKEND_DATABASE.md)
- Laravel Documentation: https://laravel.com/docs
