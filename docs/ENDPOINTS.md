# PabiliPOS API Endpoints Reference

**Base URL:** `http://localhost:8000/api`

**API Version:** 1.0

---

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [Products](#products)
3. [Categories](#categories)
4. [Customers](#customers)
5. [Suppliers](#suppliers)
6. [Purchase Orders](#purchase-orders)
7. [Goods Received Notes](#goods-received-notes)
8. [Inventory](#inventory)
9. [Transactions (Sales)](#transactions-sales)
10. [Payments](#payments)
11. [Returns](#returns)
12. [Shifts](#shifts)
13. [Vouchers](#vouchers)
14. [Discounts](#discounts)
15. [Quotes](#quotes)
16. [Sales Orders](#sales-orders)
17. [Stock Takes](#stock-takes)
18. [Settings](#settings)
19. [Reports](#reports)
20. [Status Codes & Error Handling](#status-codes--error-handling)

---

## Authentication Endpoints

### Login
**POST** `/auth/login`

Login and receive authentication token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|1aBcDeFgHiJkLmNoPqRsTuVwXyZ",
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "role": "owner",
      "status": "active"
    }
  }
}
```

---

### Register
**POST** `/auth/register`

Create a new user account.

**Request Body:**
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "cashier"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 2,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "role": "cashier",
    "status": "active",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### Get Current User
**GET** `/auth/me`

Get authenticated user's information.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "user@example.com",
    "role": "owner",
    "status": "active",
    "last_login_at": "2024-01-15T14:20:00Z"
  }
}
```

---

### Logout
**POST** `/auth/logout`

Revoke authentication token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

---

## Products

### List Products
**GET** `/products`

Retrieve all products with pagination.

**Query Parameters:**
- `page` (int, optional, default: 1)
- `per_page` (int, optional, default: 15)
- `status` (string, optional): `active`, `inactive`, `archived`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Coca Cola 1.5L",
        "sku": "COKE001",
        "category_id": 1,
        "description": "Soft drink",
        "cost": 25.00,
        "selling_price": 50.00,
        "quantity": 100,
        "reorder_level": 20,
        "status": "active",
        "has_barcode": true,
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 150,
      "per_page": 15,
      "current_page": 1,
      "last_page": 10
    }
  }
}
```

---

### Create Product
**POST** `/products`

Create a new product.

**Request Body:**
```json
{
  "name": "Sprite 1.5L",
  "sku": "SPRITE001",
  "category_id": 1,
  "description": "Lemon-lime flavored soft drink",
  "cost": 22.00,
  "selling_price": 45.00,
  "quantity": 50,
  "reorder_level": 15,
  "status": "active"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 2,
    "name": "Sprite 1.5L",
    "sku": "SPRITE001",
    "category_id": 1,
    "description": "Lemon-lime flavored soft drink",
    "cost": 22.00,
    "selling_price": 45.00,
    "quantity": 50,
    "reorder_level": 15,
    "status": "active",
    "created_at": "2024-01-15T10:35:00Z"
  }
}
```

---

### Get Product
**GET** `/products/{id}`

Retrieve a specific product by ID.

**Path Parameters:**
- `id` (required): Product ID

**Response (200):**
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "Coca Cola 1.5L",
    "sku": "COKE001",
    "category_id": 1,
    "description": "Soft drink",
    "cost": 25.00,
    "selling_price": 50.00,
    "quantity": 100,
    "reorder_level": 20,
    "status": "active",
    "barcodes": [
      {
        "id": 1,
        "barcode": "8850001000148"
      }
    ]
  }
}
```

---

### Update Product
**PUT** `/products/{id}`

Update product details.

**Request Body:**
```json
{
  "name": "Coca Cola 1.5L",
  "selling_price": 52.00,
  "reorder_level": 25,
  "status": "active"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "Coca Cola 1.5L",
    "selling_price": 52.00,
    "reorder_level": 25,
    "status": "active",
    "updated_at": "2024-01-15T10:40:00Z"
  }
}
```

---

### Delete Product
**DELETE** `/products/{id}`

Soft delete a product.

**Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully",
  "data": null
}
```

---

### Search Products
**GET** `/products/search/{term}`

Search products by name or SKU.

**Path Parameters:**
- `term` (required): Search term

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Coca Cola 1.5L",
      "sku": "COKE001",
      "selling_price": 50.00,
      "quantity": 100
    }
  ]
}
```

---

### Get Product by Barcode
**GET** `/products/barcode/{barcode}`

Lookup product by barcode (useful for POS scanning).

**Path Parameters:**
- `barcode` (required): Product barcode

**Response (200):**
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "Coca Cola 1.5L",
    "sku": "COKE001",
    "selling_price": 50.00,
    "quantity": 100,
    "category": "Beverages"
  }
}
```

---

## Categories

### List Categories
**GET** `/categories`

Retrieve all product categories.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `active`, `inactive`

**Response (200):**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Beverages",
        "description": "All drinks and beverages",
        "status": "active",
        "product_count": 45
      }
    ]
  }
}
```

---

### Create Category
**POST** `/categories`

Create a new product category.

**Request Body:**
```json
{
  "name": "Snacks",
  "description": "Chips, crackers, and other snacks",
  "status": "active"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 2,
    "name": "Snacks",
    "description": "Chips, crackers, and other snacks",
    "status": "active",
    "created_at": "2024-01-15T10:45:00Z"
  }
}
```

---

### Get Category
**GET** `/categories/{id}`

Retrieve a specific category.

**Response (200):**
```json
{
  "success": true,
  "message": "Category retrieved successfully",
  "data": {
    "id": 1,
    "name": "Beverages",
    "description": "All drinks and beverages",
    "status": "active",
    "products": [
      {
        "id": 1,
        "name": "Coca Cola 1.5L",
        "sku": "COKE001",
        "selling_price": 50.00
      }
    ]
  }
}
```

---

### Update Category
**PUT** `/categories/{id}`

Update category details.

**Request Body:**
```json
{
  "name": "Soft Drinks",
  "description": "Non-alcoholic beverages",
  "status": "active"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "id": 1,
    "name": "Soft Drinks",
    "description": "Non-alcoholic beverages",
    "status": "active"
  }
}
```

---

### Delete Category
**DELETE** `/categories/{id}`

Delete a category.

**Response (200):**
```json
{
  "success": true,
  "message": "Category deleted successfully",
  "data": null
}
```

---

## Customers

### List Customers
**GET** `/customers`

Retrieve all customers with pagination.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `active`, `inactive`

**Response (200):**
```json
{
  "success": true,
  "message": "Customers retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "first_name": "Maria",
        "last_name": "Garcia",
        "email": "maria@example.com",
        "phone": "+63912345678",
        "customer_code": "CUST001",
        "customer_type": "regular",
        "credit_limit": 5000.00,
        "credit_balance": 1200.00,
        "loyalty_points": 5500,
        "status": "active"
      }
    ],
    "pagination": {
      "total": 250,
      "per_page": 15,
      "current_page": 1,
      "last_page": 17
    }
  }
}
```

---

### Create Customer
**POST** `/customers`

Create a new customer.

**Request Body:**
```json
{
  "first_name": "Juan",
  "last_name": "Dela Cruz",
  "email": "juan@example.com",
  "phone": "+63912345679",
  "customer_type": "regular",
  "credit_limit": 3000.00,
  "status": "active"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 2,
    "first_name": "Juan",
    "last_name": "Dela Cruz",
    "email": "juan@example.com",
    "phone": "+63912345679",
    "customer_code": "CUST002",
    "customer_type": "regular",
    "credit_limit": 3000.00,
    "credit_balance": 0.00,
    "loyalty_points": 0,
    "status": "active",
    "created_at": "2024-01-15T10:50:00Z"
  }
}
```

---

### Get Customer
**GET** `/customers/{id}`

Retrieve a specific customer.

**Response (200):**
```json
{
  "success": true,
  "message": "Customer retrieved successfully",
  "data": {
    "id": 1,
    "first_name": "Maria",
    "last_name": "Garcia",
    "email": "maria@example.com",
    "phone": "+63912345678",
    "customer_code": "CUST001",
    "customer_type": "regular",
    "credit_limit": 5000.00,
    "credit_balance": 1200.00,
    "loyalty_points": 5500,
    "status": "active",
    "transaction_history": [
      {
        "id": 1,
        "receipt_number": "REC001",
        "total_amount": 2500.00,
        "created_at": "2024-01-14T15:30:00Z"
      }
    ]
  }
}
```

---

### Update Customer
**PUT** `/customers/{id}`

Update customer details.

**Request Body:**
```json
{
  "email": "maria.new@example.com",
  "phone": "+63987654321",
  "credit_limit": 6000.00,
  "status": "active"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Customer updated successfully",
  "data": {
    "id": 1,
    "first_name": "Maria",
    "last_name": "Garcia",
    "email": "maria.new@example.com",
    "phone": "+63987654321",
    "credit_limit": 6000.00,
    "updated_at": "2024-01-15T10:55:00Z"
  }
}
```

---

### Delete Customer
**DELETE** `/customers/{id}`

Soft delete a customer.

**Response (200):**
```json
{
  "success": true,
  "message": "Customer deleted successfully",
  "data": null
}
```

---

### Search Customers
**GET** `/customers/search/{term}`

Search customers by name, phone, or code.

**Path Parameters:**
- `term` (required): Search term

**Response (200):**
```json
{
  "success": true,
  "message": "Customers retrieved successfully",
  "data": [
    {
      "id": 1,
      "first_name": "Maria",
      "last_name": "Garcia",
      "phone": "+63912345678",
      "customer_code": "CUST001",
      "customer_type": "regular"
    }
  ]
}
```

---

### Adjust Customer Credit
**POST** `/customers/{id}/credit`

Add or deduct credit from customer account.

**Request Body:**
```json
{
  "amount": -500.00,
  "reason": "Payment received",
  "reference_number": "TXN12345"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Credit adjusted successfully",
  "data": {
    "customer_id": 1,
    "previous_balance": 1200.00,
    "new_balance": 700.00,
    "amount_adjusted": -500.00,
    "adjusted_at": "2024-01-15T11:00:00Z"
  }
}
```

---

## Suppliers

### List Suppliers
**GET** `/suppliers`

Retrieve all suppliers.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Suppliers retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Coca Cola Philippines",
        "contact_person": "Mr. Santos",
        "email": "order@cocacola.ph",
        "phone": "+63212345678",
        "address": "Manila, Philippines",
        "status": "active"
      }
    ],
    "pagination": {
      "total": 50,
      "per_page": 15,
      "current_page": 1
    }
  }
}
```

---

### Create Supplier
**POST** `/suppliers`

Create a new supplier.

**Request Body:**
```json
{
  "name": "Pepsi Philippines",
  "contact_person": "Ms. Lopez",
  "email": "order@pepsi.ph",
  "phone": "+63212345679",
  "address": "Makati, Manila",
  "status": "active"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Supplier created successfully",
  "data": {
    "id": 2,
    "name": "Pepsi Philippines",
    "contact_person": "Ms. Lopez",
    "email": "order@pepsi.ph",
    "phone": "+63212345679",
    "address": "Makati, Manila",
    "status": "active",
    "created_at": "2024-01-15T11:05:00Z"
  }
}
```

---

### Get Supplier
**GET** `/suppliers/{id}`

Retrieve a specific supplier.

**Response (200):**
```json
{
  "success": true,
  "message": "Supplier retrieved successfully",
  "data": {
    "id": 1,
    "name": "Coca Cola Philippines",
    "contact_person": "Mr. Santos",
    "email": "order@cocacola.ph",
    "phone": "+63212345678",
    "address": "Manila, Philippines",
    "status": "active",
    "purchase_orders": [
      {
        "id": 1,
        "po_number": "PO001",
        "total_amount": 50000.00,
        "status": "approved"
      }
    ]
  }
}
```

---

### Update Supplier
**PUT** `/suppliers/{id}`

Update supplier details.

**Request Body:**
```json
{
  "phone": "+63212345680",
  "address": "BGC, Taguig",
  "status": "active"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Supplier updated successfully",
  "data": {
    "id": 1,
    "name": "Coca Cola Philippines",
    "phone": "+63212345680",
    "address": "BGC, Taguig",
    "updated_at": "2024-01-15T11:10:00Z"
  }
}
```

---

### Delete Supplier
**DELETE** `/suppliers/{id}`

Soft delete a supplier.

**Response (200):**
```json
{
  "success": true,
  "message": "Supplier deleted successfully",
  "data": null
}
```

---

## Purchase Orders

### List Purchase Orders
**GET** `/purchase-orders`

Retrieve all purchase orders.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `draft`, `submitted`, `approved`, `partial`, `received`, `cancelled`
- `supplier_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase orders retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "po_number": "PO001",
        "supplier_id": 1,
        "supplier_name": "Coca Cola Philippines",
        "total_amount": 50000.00,
        "status": "approved",
        "delivery_date": "2024-01-20",
        "created_at": "2024-01-15T11:15:00Z"
      }
    ]
  }
}
```

---

### Create Purchase Order
**POST** `/purchase-orders`

Create a new purchase order.

**Request Body:**
```json
{
  "supplier_id": 1,
  "delivery_date": "2024-01-20",
  "notes": "Urgent delivery required",
  "items": [
    {
      "product_id": 1,
      "quantity": 100,
      "unit_cost": 25.00
    },
    {
      "product_id": 2,
      "quantity": 50,
      "unit_cost": 22.00
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Purchase order created successfully",
  "data": {
    "id": 1,
    "po_number": "PO001",
    "supplier_id": 1,
    "total_amount": 3600.00,
    "status": "draft",
    "delivery_date": "2024-01-20",
    "items": [
      {
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity": 100,
        "unit_cost": 25.00,
        "line_total": 2500.00
      }
    ],
    "created_at": "2024-01-15T11:20:00Z"
  }
}
```

---

### Get Purchase Order
**GET** `/purchase-orders/{id}`

Retrieve a specific purchase order.

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase order retrieved successfully",
  "data": {
    "id": 1,
    "po_number": "PO001",
    "supplier_id": 1,
    "supplier_name": "Coca Cola Philippines",
    "total_amount": 50000.00,
    "status": "approved",
    "delivery_date": "2024-01-20",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity": 100,
        "unit_cost": 25.00,
        "line_total": 2500.00
      }
    ]
  }
}
```

---

### Update Purchase Order
**PUT** `/purchase-orders/{id}`

Update purchase order (only for draft status).

**Request Body:**
```json
{
  "delivery_date": "2024-01-22",
  "notes": "Rescheduled delivery"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase order updated successfully",
  "data": {
    "id": 1,
    "po_number": "PO001",
    "delivery_date": "2024-01-22",
    "updated_at": "2024-01-15T11:25:00Z"
  }
}
```

---

### Approve Purchase Order
**POST** `/purchase-orders/{id}/approve`

Approve a draft purchase order.

**Request Body:**
```json
{
  "notes": "Approved for procurement"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase order approved successfully",
  "data": {
    "id": 1,
    "po_number": "PO001",
    "status": "approved",
    "approved_at": "2024-01-15T11:30:00Z"
  }
}
```

---

### Cancel Purchase Order
**POST** `/purchase-orders/{id}/cancel`

Cancel a purchase order.

**Request Body:**
```json
{
  "reason": "Supplier out of stock"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase order cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled",
    "cancelled_at": "2024-01-15T11:35:00Z"
  }
}
```

---

### Delete Purchase Order
**DELETE** `/purchase-orders/{id}`

Delete a purchase order (only draft).

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase order deleted successfully",
  "data": null
}
```

---

## Goods Received Notes

### List GRNs
**GET** `/grns`

Retrieve all goods received notes.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `draft`, `posted`
- `purchase_order_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "GRNs retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "grn_number": "GRN001",
        "purchase_order_id": 1,
        "po_number": "PO001",
        "status": "posted",
        "total_amount": 50000.00,
        "received_date": "2024-01-20",
        "created_at": "2024-01-20T14:30:00Z"
      }
    ]
  }
}
```

---

### Create GRN
**POST** `/grns`

Create a goods received note.

**Request Body:**
```json
{
  "purchase_order_id": 1,
  "received_date": "2024-01-20",
  "invoice_number": "INV12345",
  "items": [
    {
      "purchase_order_item_id": 1,
      "quantity_received": 100,
      "notes": "All items in good condition"
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "GRN created successfully",
  "data": {
    "id": 1,
    "grn_number": "GRN001",
    "purchase_order_id": 1,
    "status": "draft",
    "received_date": "2024-01-20",
    "items": [
      {
        "id": 1,
        "purchase_order_item_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity_received": 100
      }
    ]
  }
}
```

---

### Get GRN
**GET** `/grns/{id}`

Retrieve a specific GRN.

**Response (200):**
```json
{
  "success": true,
  "message": "GRN retrieved successfully",
  "data": {
    "id": 1,
    "grn_number": "GRN001",
    "purchase_order_id": 1,
    "po_number": "PO001",
    "status": "draft",
    "received_date": "2024-01-20",
    "total_amount": 50000.00,
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity_received": 100
      }
    ]
  }
}
```

---

### Post GRN (Update Inventory)
**POST** `/grns/{id}/post`

Post the GRN and update inventory ledger.

**Request Body:**
```json
{
  "notes": "Stock received and verified"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "GRN posted successfully. Inventory updated.",
  "data": {
    "id": 1,
    "grn_number": "GRN001",
    "status": "posted",
    "posted_at": "2024-01-20T15:00:00Z",
    "inventory_updates": [
      {
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity_added": 100,
        "new_balance": 200
      }
    ]
  }
}
```

---

## Inventory

### List Inventory Ledger
**GET** `/inventory-ledger`

View inventory movement history.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `product_id` (int, optional)
- `type` (string, optional): `purchase`, `sales`, `adjustment`, `return`, `stock_take`
- `date_from` (string, optional): ISO date format
- `date_to` (string, optional): ISO date format

**Response (200):**
```json
{
  "success": true,
  "message": "Inventory ledger retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "type": "purchase",
        "qty_in": 100,
        "qty_out": 0,
        "balance_after": 200,
        "reference": "GRN001",
        "created_at": "2024-01-20T15:00:00Z"
      },
      {
        "id": 2,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "type": "sales",
        "qty_in": 0,
        "qty_out": 5,
        "balance_after": 195,
        "reference": "REC001",
        "created_at": "2024-01-20T16:30:00Z"
      }
    ]
  }
}
```

---

### Get Product Stock Balance
**GET** `/inventory/product/{id}/balance`

Get current stock balance for a product.

**Response (200):**
```json
{
  "success": true,
  "message": "Stock balance retrieved successfully",
  "data": {
    "product_id": 1,
    "product_name": "Coca Cola 1.5L",
    "current_balance": 195,
    "reorder_level": 20,
    "status": "ok",
    "last_updated": "2024-01-20T16:30:00Z"
  }
}
```

---

## Transactions (Sales)

### List Transactions
**GET** `/transactions`

Retrieve all sales transactions.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `completed`, `voided`, `held`
- `date_from` (string, optional)
- `date_to` (string, optional)
- `shift_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "receipt_number": "REC001",
        "shift_id": 1,
        "customer_id": 1,
        "customer_name": "Maria Garcia",
        "cashier_id": 1,
        "cashier_name": "John Doe",
        "subtotal": 2500.00,
        "discount_total": 0.00,
        "tax_total": 0.00,
        "total_amount": 2500.00,
        "amount_paid": 2500.00,
        "change": 0.00,
        "status": "completed",
        "created_at": "2024-01-20T16:30:00Z"
      }
    ]
  }
}
```

---

### Create Transaction (POS Sale)
**POST** `/transactions`

Create a new sales transaction.

**Request Body:**
```json
{
  "shift_id": 1,
  "customer_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 5,
      "unit_price": 50.00,
      "discount_amount": 0.00
    },
    {
      "product_id": 2,
      "quantity": 3,
      "unit_price": 45.00,
      "discount_amount": 0.00
    }
  ],
  "discount_total": 100.00,
  "discount_type_id": 1,
  "tax_amount": 0.00,
  "payments": [
    {
      "payment_method": "cash",
      "amount": 2500.00,
      "reference": null
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "receipt_number": "REC001",
    "shift_id": 1,
    "customer_id": 1,
    "subtotal": 2650.00,
    "discount_total": 100.00,
    "tax_total": 0.00,
    "total_amount": 2550.00,
    "status": "completed",
    "items": [
      {
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity": 5,
        "unit_price": 50.00,
        "line_total": 250.00
      }
    ],
    "created_at": "2024-01-20T16:30:00Z"
  }
}
```

---

### Get Transaction
**GET** `/transactions/{id}`

Retrieve a specific transaction.

**Response (200):**
```json
{
  "success": true,
  "message": "Transaction retrieved successfully",
  "data": {
    "id": 1,
    "receipt_number": "REC001",
    "shift_id": 1,
    "customer_id": 1,
    "customer_name": "Maria Garcia",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity": 5,
        "unit_price": 50.00,
        "discount_amount": 0.00,
        "line_total": 250.00
      }
    ],
    "payments": [
      {
        "id": 1,
        "payment_method": "cash",
        "amount": 2500.00
      }
    ],
    "total_amount": 2550.00,
    "status": "completed"
  }
}
```

---

### Get Transaction by Receipt Number
**GET** `/transactions/receipt/{number}`

Lookup transaction by receipt number.

**Response (200):**
```json
{
  "success": true,
  "message": "Transaction retrieved successfully",
  "data": {
    "id": 1,
    "receipt_number": "REC001",
    "total_amount": 2550.00,
    "status": "completed",
    "items": []
  }
}
```

---

### Void Transaction
**POST** `/transactions/{id}/void`

Void a completed transaction (reverse the sale).

**Request Body:**
```json
{
  "reason": "Customer requested refund"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Transaction voided successfully. Inventory restored.",
  "data": {
    "id": 1,
    "receipt_number": "REC001",
    "status": "voided",
    "voided_at": "2024-01-20T16:40:00Z",
    "inventory_restored": [
      {
        "product_id": 1,
        "quantity": 5
      }
    ]
  }
}
```

---

## Payments

### List Payments
**GET** `/payments`

Retrieve all payments.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `completed`, `voided`
- `payment_method` (string, optional): `cash`, `card`, `e_wallet`, `credit`, `voucher`

**Response (200):**
```json
{
  "success": true,
  "message": "Payments retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "transaction_id": 1,
        "receipt_number": "REC001",
        "payment_method": "cash",
        "amount": 2500.00,
        "reference": null,
        "status": "completed",
        "created_at": "2024-01-20T16:30:00Z"
      }
    ]
  }
}
```

---

### Get Payment
**GET** `/payments/{id}`

Retrieve a specific payment.

**Response (200):**
```json
{
  "success": true,
  "message": "Payment retrieved successfully",
  "data": {
    "id": 1,
    "transaction_id": 1,
    "receipt_number": "REC001",
    "payment_method": "cash",
    "amount": 2500.00,
    "status": "completed"
  }
}
```

---

### Void Payment
**POST** `/payments/{id}/void`

Void a payment.

**Request Body:**
```json
{
  "reason": "Payment error"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Payment voided successfully",
  "data": {
    "id": 1,
    "status": "voided",
    "voided_at": "2024-01-20T16:45:00Z"
  }
}
```

---

## Returns

### List Returns
**GET** `/returns`

Retrieve all returns/refunds.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `pending`, `approved`, `completed`, `rejected`

**Response (200):**
```json
{
  "success": true,
  "message": "Returns retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "return_number": "RET001",
        "transaction_id": 1,
        "receipt_number": "REC001",
        "total_amount": 250.00,
        "status": "pending",
        "created_at": "2024-01-20T17:00:00Z"
      }
    ]
  }
}
```

---

### Create Return
**POST** `/returns`

Create a return/refund request.

**Request Body:**
```json
{
  "transaction_id": 1,
  "reason": "Defective product",
  "items": [
    {
      "transaction_item_id": 1,
      "quantity": 1,
      "reason": "Expired"
    }
  ],
  "refund_method": "cash"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Return created successfully",
  "data": {
    "id": 1,
    "return_number": "RET001",
    "transaction_id": 1,
    "receipt_number": "REC001",
    "total_amount": 250.00,
    "status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity": 1,
        "refund_amount": 50.00
      }
    ]
  }
}
```

---

### Get Return
**GET** `/returns/{id}`

Retrieve a specific return.

**Response (200):**
```json
{
  "success": true,
  "message": "Return retrieved successfully",
  "data": {
    "id": 1,
    "return_number": "RET001",
    "transaction_id": 1,
    "total_amount": 250.00,
    "status": "pending",
    "items": []
  }
}
```

---

### Approve Return
**POST** `/returns/{id}/approve`

Approve a return request.

**Request Body:**
```json
{
  "notes": "Approved by manager"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Return approved successfully. Inventory restored.",
  "data": {
    "id": 1,
    "return_number": "RET001",
    "status": "approved",
    "approved_at": "2024-01-20T17:10:00Z"
  }
}
```

---

### Complete Return
**POST** `/returns/{id}/complete`

Complete a return (issue refund).

**Request Body:**
```json
{
  "refund_date": "2024-01-20"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Return completed. Refund issued.",
  "data": {
    "id": 1,
    "return_number": "RET001",
    "status": "completed",
    "refund_amount": 250.00,
    "completed_at": "2024-01-20T17:15:00Z"
  }
}
```

---

### Reject Return
**POST** `/returns/{id}/reject`

Reject a return request.

**Request Body:**
```json
{
  "reason": "Outside return window"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Return rejected successfully",
  "data": {
    "id": 1,
    "return_number": "RET001",
    "status": "rejected",
    "rejected_at": "2024-01-20T17:20:00Z"
  }
}
```

---

### Delete Return
**DELETE** `/returns/{id}`

Delete a return (only pending).

**Response (200):**
```json
{
  "success": true,
  "message": "Return deleted successfully",
  "data": null
}
```

---

## Shifts

### List Shifts
**GET** `/shifts`

Retrieve all shifts.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `open`, `closed`
- `cashier_id` (int, optional)
- `date` (string, optional): Filter by date

**Response (200):**
```json
{
  "success": true,
  "message": "Shifts retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "shift_number": "SHIFT001",
        "cashier_id": 1,
        "cashier_name": "John Doe",
        "float_amount": 1000.00,
        "status": "open",
        "opened_at": "2024-01-20T08:00:00Z",
        "closed_at": null
      }
    ]
  }
}
```

---

### Create Shift (Open Shift)
**POST** `/shifts`

Open a new cash shift.

**Request Body:**
```json
{
  "cashier_id": 1,
  "float_amount": 1000.00,
  "notes": "Starting shift"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Shift opened successfully",
  "data": {
    "id": 1,
    "shift_number": "SHIFT001",
    "cashier_id": 1,
    "cashier_name": "John Doe",
    "float_amount": 1000.00,
    "status": "open",
    "opened_at": "2024-01-20T08:00:00Z"
  }
}
```

---

### Get Shift
**GET** `/shifts/{id}`

Retrieve a specific shift.

**Response (200):**
```json
{
  "success": true,
  "message": "Shift retrieved successfully",
  "data": {
    "id": 1,
    "shift_number": "SHIFT001",
    "cashier_id": 1,
    "cashier_name": "John Doe",
    "float_amount": 1000.00,
    "status": "open",
    "opened_at": "2024-01-20T08:00:00Z",
    "transactions": [
      {
        "id": 1,
        "receipt_number": "REC001",
        "total_amount": 2550.00
      }
    ],
    "movements": [
      {
        "id": 1,
        "type": "in",
        "amount": 500.00,
        "reason": "Received payment"
      }
    ]
  }
}
```

---

### Get Active Shift
**GET** `/shifts/active/current`

Get current active shift for logged-in cashier.

**Response (200):**
```json
{
  "success": true,
  "message": "Active shift retrieved successfully",
  "data": {
    "id": 1,
    "shift_number": "SHIFT001",
    "status": "open",
    "float_amount": 1000.00,
    "sales_total": 15000.00,
    "cash_in_total": 1000.00,
    "cash_out_total": 500.00
  }
}
```

---

### Record Shift Movement
**POST** `/shifts/{id}/movements`

Record cash in/out movement during shift.

**Request Body:**
```json
{
  "type": "in",
  "amount": 500.00,
  "reason": "Customer paid previous balance",
  "reference": "CUST001"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Shift movement recorded successfully",
  "data": {
    "id": 1,
    "shift_id": 1,
    "type": "in",
    "amount": 500.00,
    "reason": "Customer paid previous balance",
    "recorded_at": "2024-01-20T14:30:00Z"
  }
}
```

---

### Close Shift
**POST** `/shifts/{id}/close`

Close a cash shift with reconciliation.

**Request Body:**
```json
{
  "final_count": 18500.00,
  "notes": "Day-end reconciliation completed"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Shift closed successfully",
  "data": {
    "id": 1,
    "shift_number": "SHIFT001",
    "status": "closed",
    "float_amount": 1000.00,
    "sales_total": 15000.00,
    "cash_movements_total": 1500.00,
    "expected_balance": 17500.00,
    "final_count": 18500.00,
    "variance": 1000.00,
    "closed_at": "2024-01-20T17:00:00Z"
  }
}
```

---

## Vouchers

### List Vouchers
**GET** `/vouchers`

Retrieve all vouchers.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `active`, `redeemed`, `expired`, `cancelled`

**Response (200):**
```json
{
  "success": true,
  "message": "Vouchers retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "voucher_code": "GIFT001",
        "amount": 1000.00,
        "balance": 750.00,
        "status": "active",
        "issued_by": "admin",
        "issued_date": "2024-01-15",
        "expiry_date": "2024-12-31"
      }
    ]
  }
}
```

---

### Create Voucher
**POST** `/vouchers`

Issue a new gift voucher.

**Request Body:**
```json
{
  "amount": 1000.00,
  "issued_by": "admin",
  "issued_date": "2024-01-20",
  "expiry_date": "2024-12-31",
  "notes": "Birthday gift"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Voucher created successfully",
  "data": {
    "id": 1,
    "voucher_code": "GIFT001",
    "amount": 1000.00,
    "balance": 1000.00,
    "status": "active",
    "issued_date": "2024-01-20",
    "expiry_date": "2024-12-31",
    "created_at": "2024-01-20T10:00:00Z"
  }
}
```

---

### Get Voucher
**GET** `/vouchers/{id}`

Retrieve a specific voucher.

**Response (200):**
```json
{
  "success": true,
  "message": "Voucher retrieved successfully",
  "data": {
    "id": 1,
    "voucher_code": "GIFT001",
    "amount": 1000.00,
    "balance": 750.00,
    "status": "active",
    "usages": [
      {
        "id": 1,
        "transaction_id": 1,
        "amount_used": 250.00,
        "used_at": "2024-01-20T15:30:00Z"
      }
    ]
  }
}
```

---

### Redeem Voucher
**POST** `/vouchers/{id}/redeem`

Use voucher balance for payment.

**Request Body:**
```json
{
  "transaction_id": 1,
  "amount": 250.00
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Voucher redeemed successfully",
  "data": {
    "id": 1,
    "voucher_code": "GIFT001",
    "amount_used": 250.00,
    "balance_remaining": 750.00,
    "redeemed_at": "2024-01-20T15:30:00Z"
  }
}
```

---

### Cancel Voucher
**POST** `/vouchers/{id}/cancel`

Cancel an unused voucher.

**Request Body:**
```json
{
  "reason": "Customer request"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Voucher cancelled successfully",
  "data": {
    "id": 1,
    "voucher_code": "GIFT001",
    "status": "cancelled",
    "cancelled_at": "2024-01-20T16:00:00Z"
  }
}
```

---

## Discounts

### List Discount Types
**GET** `/discount-types`

Retrieve all discount types.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `active`, `inactive`

**Response (200):**
```json
{
  "success": true,
  "message": "Discount types retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Senior Citizen",
        "percentage": 20.00,
        "max_discount": null,
        "status": "active",
        "requires_approval": false
      },
      {
        "id": 2,
        "name": "PWD",
        "percentage": 12.00,
        "max_discount": null,
        "status": "active",
        "requires_approval": false
      }
    ]
  }
}
```

---

### Create Discount Type
**POST** `/discount-types`

Create a new discount type.

**Request Body:**
```json
{
  "name": "Employee",
  "percentage": 15.00,
  "max_discount": 5000.00,
  "status": "active",
  "requires_approval": true,
  "approval_level": "manager"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Discount type created successfully",
  "data": {
    "id": 3,
    "name": "Employee",
    "percentage": 15.00,
    "max_discount": 5000.00,
    "status": "active",
    "created_at": "2024-01-20T10:05:00Z"
  }
}
```

---

### Get Discount Type
**GET** `/discount-types/{id}`

Retrieve a specific discount type.

**Response (200):**
```json
{
  "success": true,
  "message": "Discount type retrieved successfully",
  "data": {
    "id": 1,
    "name": "Senior Citizen",
    "percentage": 20.00,
    "max_discount": null,
    "status": "active"
  }
}
```

---

### Update Discount Type
**PUT** `/discount-types/{id}`

Update discount type details.

**Request Body:**
```json
{
  "name": "Senior/PWD Discount",
  "percentage": 20.00,
  "status": "active"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Discount type updated successfully",
  "data": {
    "id": 1,
    "name": "Senior/PWD Discount",
    "percentage": 20.00,
    "updated_at": "2024-01-20T10:10:00Z"
  }
}
```

---

### Delete Discount Type
**DELETE** `/discount-types/{id}`

Delete a discount type.

**Response (200):**
```json
{
  "success": true,
  "message": "Discount type deleted successfully",
  "data": null
}
```

---

## Quotes

### List Quotes
**GET** `/quotes`

Retrieve all quotations.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `draft`, `sent`, `accepted`, `rejected`, `expired`, `converted`
- `customer_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Quotes retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "quote_number": "QT001",
        "customer_id": 1,
        "customer_name": "Maria Garcia",
        "total_amount": 5000.00,
        "status": "draft",
        "expiry_date": "2024-02-20",
        "created_at": "2024-01-20T10:15:00Z"
      }
    ]
  }
}
```

---

### Create Quote
**POST** `/quotes`

Create a new quotation.

**Request Body:**
```json
{
  "customer_id": 1,
  "expiry_date": "2024-02-20",
  "items": [
    {
      "product_id": 1,
      "quantity": 10,
      "unit_price": 50.00
    }
  ],
  "notes": "Quote for bulk order"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Quote created successfully",
  "data": {
    "id": 1,
    "quote_number": "QT001",
    "customer_id": 1,
    "total_amount": 500.00,
    "status": "draft",
    "items": []
  }
}
```

---

### Get Quote
**GET** `/quotes/{id}`

Retrieve a specific quote.

**Response (200):**
```json
{
  "success": true,
  "message": "Quote retrieved successfully",
  "data": {
    "id": 1,
    "quote_number": "QT001",
    "customer_id": 1,
    "total_amount": 500.00,
    "status": "draft",
    "items": []
  }
}
```

---

### Update Quote
**PUT** `/quotes/{id}`

Update a quote (only draft).

**Request Body:**
```json
{
  "expiry_date": "2024-02-25",
  "notes": "Updated quote"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Quote updated successfully",
  "data": {
    "id": 1,
    "quote_number": "QT001",
    "expiry_date": "2024-02-25"
  }
}
```

---

### Convert Quote to Transaction
**POST** `/quotes/{id}/convert`

Convert accepted quote to sales transaction.

**Request Body:**
```json
{
  "shift_id": 1,
  "payment_method": "credit"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Quote converted to transaction successfully",
  "data": {
    "quote_id": 1,
    "transaction_id": 1,
    "receipt_number": "REC001",
    "status": "converted",
    "converted_at": "2024-01-20T10:20:00Z"
  }
}
```

---

### Delete Quote
**DELETE** `/quotes/{id}`

Delete a quote (only draft).

**Response (200):**
```json
{
  "success": true,
  "message": "Quote deleted successfully",
  "data": null
}
```

---

## Sales Orders

### List Sales Orders
**GET** `/sales-orders`

Retrieve all sales orders.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `draft`, `submitted`, `approved`, `partial`, `fulfilled`, `cancelled`
- `customer_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Sales orders retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "so_number": "SO001",
        "customer_id": 1,
        "customer_name": "Maria Garcia",
        "total_amount": 10000.00,
        "status": "draft",
        "delivery_date": "2024-01-25",
        "created_at": "2024-01-20T10:25:00Z"
      }
    ]
  }
}
```

---

### Create Sales Order
**POST** `/sales-orders`

Create a new sales order.

**Request Body:**
```json
{
  "customer_id": 1,
  "delivery_date": "2024-01-25",
  "items": [
    {
      "product_id": 1,
      "quantity": 20,
      "unit_price": 50.00
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Sales order created successfully",
  "data": {
    "id": 1,
    "so_number": "SO001",
    "customer_id": 1,
    "total_amount": 1000.00,
    "status": "draft"
  }
}
```

---

### Get Sales Order
**GET** `/sales-orders/{id}`

Retrieve a specific sales order.

**Response (200):**
```json
{
  "success": true,
  "message": "Sales order retrieved successfully",
  "data": {
    "id": 1,
    "so_number": "SO001",
    "customer_id": 1,
    "total_amount": 1000.00,
    "status": "draft",
    "items": []
  }
}
```

---

### Update Sales Order
**PUT** `/sales-orders/{id}`

Update a sales order (only draft).

**Request Body:**
```json
{
  "delivery_date": "2024-01-26"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Sales order updated successfully",
  "data": {
    "id": 1,
    "so_number": "SO001",
    "delivery_date": "2024-01-26"
  }
}
```

---

### Convert Sales Order to Transaction
**POST** `/sales-orders/{id}/convert`

Convert sales order to transaction.

**Request Body:**
```json
{
  "shift_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Sales order converted to transaction successfully",
  "data": {
    "sales_order_id": 1,
    "transaction_id": 1,
    "receipt_number": "REC001"
  }
}
```

---

### Delete Sales Order
**DELETE** `/sales-orders/{id}`

Delete a sales order (only draft).

**Response (200):**
```json
{
  "success": true,
  "message": "Sales order deleted successfully",
  "data": null
}
```

---

## Stock Takes

### List Stock Takes
**GET** `/stock-takes`

Retrieve all physical stock counts.

**Query Parameters:**
- `page` (int, optional)
- `per_page` (int, optional)
- `status` (string, optional): `draft`, `in_progress`, `completed`, `posted`

**Response (200):**
```json
{
  "success": true,
  "message": "Stock takes retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "st_number": "ST001",
        "count_date": "2024-01-20",
        "total_items": 15,
        "status": "completed",
        "variance_amount": 500.00,
        "created_at": "2024-01-20T10:30:00Z"
      }
    ]
  }
}
```

---

### Create Stock Take
**POST** `/stock-takes`

Create a new stock take.

**Request Body:**
```json
{
  "count_date": "2024-01-20",
  "notes": "Monthly physical count"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Stock take created successfully",
  "data": {
    "id": 1,
    "st_number": "ST001",
    "count_date": "2024-01-20",
    "status": "draft"
  }
}
```

---

### Get Stock Take
**GET** `/stock-takes/{id}`

Retrieve a specific stock take.

**Response (200):**
```json
{
  "success": true,
  "message": "Stock take retrieved successfully",
  "data": {
    "id": 1,
    "st_number": "ST001",
    "count_date": "2024-01-20",
    "status": "draft",
    "items": []
  }
}
```

---

### Complete Stock Take
**POST** `/stock-takes/{id}/complete`

Mark stock take as complete.

**Request Body:**
```json
{
  "notes": "Count completed"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Stock take completed successfully",
  "data": {
    "id": 1,
    "st_number": "ST001",
    "status": "completed",
    "total_variance": 500.00
  }
}
```

---

### Post Stock Take
**POST** `/stock-takes/{id}/post`

Post stock take and update inventory.

**Request Body:**
```json
{
  "notes": "Variances recorded"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Stock take posted successfully. Inventory updated.",
  "data": {
    "id": 1,
    "st_number": "ST001",
    "status": "posted",
    "posted_at": "2024-01-20T11:00:00Z"
  }
}
```

---

### Delete Stock Take
**DELETE** `/stock-takes/{id}`

Delete a stock take (only draft).

**Response (200):**
```json
{
  "success": true,
  "message": "Stock take deleted successfully",
  "data": null
}
```

---

## Settings

### Get Settings
**GET** `/settings`

Retrieve store settings.

**Response (200):**
```json
{
  "success": true,
  "message": "Settings retrieved successfully",
  "data": {
    "store_name": "Pabili POS Store",
    "store_address": "Manila, Philippines",
    "store_phone": "+63212345678",
    "store_email": "store@pabili.ph",
    "currency": "PHP",
    "tax_rate": 12.00,
    "receipt_footer": "Thank you for your purchase!",
    "enable_credit": true,
    "enable_loyalty_points": true,
    "loyalty_points_per_peso": 0.1,
    "enable_price_override": true,
    "price_override_max_discount": 20.00
  }
}
```

---

### Update Settings
**PUT** `/settings`

Update store settings.

**Request Body:**
```json
{
  "store_name": "Pabili POS Store",
  "tax_rate": 12.00,
  "receipt_footer": "Thank you for your purchase!",
  "enable_loyalty_points": true,
  "loyalty_points_per_peso": 0.1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    "store_name": "Pabili POS Store",
    "tax_rate": 12.00,
    "updated_at": "2024-01-20T11:05:00Z"
  }
}
```

---

## Reports

### Sales Report
**GET** `/reports/sales`

Generate sales report.

**Query Parameters:**
- `date_from` (string, required): Start date (YYYY-MM-DD)
- `date_to` (string, required): End date (YYYY-MM-DD)
- `shift_id` (int, optional)
- `cashier_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Sales report retrieved successfully",
  "data": {
    "period": "2024-01-20 to 2024-01-20",
    "total_transactions": 15,
    "total_sales": 37500.00,
    "total_discounts": 500.00,
    "total_returns": 0.00,
    "total_tax": 0.00,
    "net_sales": 37000.00,
    "payment_breakdown": {
      "cash": 25000.00,
      "card": 10000.00,
      "credit": 2000.00,
      "voucher": 0.00
    },
    "top_products": [
      {
        "product_id": 1,
        "product_name": "Coca Cola 1.5L",
        "quantity_sold": 50,
        "sales_amount": 2500.00
      }
    ]
  }
}
```

---

### Inventory Report
**GET** `/reports/inventory`

Generate inventory movement report.

**Query Parameters:**
- `date_from` (string, optional)
- `date_to` (string, optional)
- `product_id` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Inventory report retrieved successfully",
  "data": {
    "period": "2024-01-01 to 2024-01-20",
    "total_products": 150,
    "low_stock_items": [
      {
        "product_id": 5,
        "product_name": "Mountain Dew 1.5L",
        "current_stock": 8,
        "reorder_level": 20
      }
    ],
    "movements_summary": {
      "purchases": 500,
      "sales": 350,
      "returns": 10,
      "adjustments": 5
    }
  }
}
```

---

### Audit Log Report
**GET** `/reports/audit-log`

Generate audit log report.

**Query Parameters:**
- `date_from` (string, optional)
- `date_to` (string, optional)
- `user_id` (int, optional)
- `module` (string, optional)
- `severity` (string, optional): `info`, `warning`, `error`
- `page` (int, optional)
- `per_page` (int, optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Audit log retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "user_name": "John Doe",
        "module": "transactions",
        "action": "create",
        "description": "Transaction REC001 created",
        "ip_address": "192.168.1.100",
        "severity": "info",
        "created_at": "2024-01-20T16:30:00Z"
      }
    ],
    "pagination": {
      "total": 1000,
      "per_page": 15,
      "current_page": 1
    }
  }
}
```

---

## Status Codes & Error Handling

### Success Responses

| Code | Status | Message |
|------|--------|---------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request successful, no content to return |

### Error Responses

| Code | Status | Message |
|------|--------|---------|
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Missing or invalid authentication token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server error |

### Error Response Format

```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Common Errors

#### Missing Authentication
```json
{
  "success": false,
  "message": "Unauthenticated",
  "data": null
}
```

#### Invalid Token
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "data": null
}
```

#### Not Found
```json
{
  "success": false,
  "message": "Product not found",
  "data": null
}
```

#### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "name": ["The name field is required."],
    "selling_price": ["The selling price must be greater than cost."]
  }
}
```

---

## Integration Notes

### Headers Required
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  // For protected routes
```

### Pagination
Most list endpoints support pagination:
- `page`: Current page (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

### Date Format
All dates should be in ISO 8601 format: `YYYY-MM-DD` or `YYYY-MM-DDTHH:MM:SS`

### Currency
All monetary values are in PHP (₱) with 2 decimal places.

### Status Values
- Products: `active`, `inactive`, `archived`
- Orders: `draft`, `submitted`, `approved`, `partial`, `received`, `cancelled`
- Transactions: `completed`, `voided`, `held`
- Shifts: `open`, `closed`
- Returns: `pending`, `approved`, `completed`, `rejected`

---

**API Version:** 1.0  
**Last Updated:** 2024-01-20  
**Base URL:** `http://localhost:8000/api`
