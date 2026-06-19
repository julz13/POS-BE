<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use Database\Seeders\BulkDataSeeder;
use App\Models\DiscountType;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Owner
        $owner = User::create([
            'first_name'    => 'Owner',
            'last_name'     => 'Account',
            'email'         => 'owner@pabili.test',
            'password_hash' => Hash::make('password123'),
            'role'          => 'owner',
            'status'        => 'active',
            'pin'           => Hash::make('1234'),
        ]);

        // Create Stores
        $storeManila = Store::create([
            'owner_id'    => $owner->id,
            'name'        => 'Manila Store',
            'code'        => 'STR001',
            'address'     => '123 Makati Avenue, Makati City',
            'phone'       => '+63212345678',
            'email'       => 'manila@pabili.ph',
            'city'        => 'Manila',
            'province'    => 'Metro Manila',
            'postal_code' => '1226',
            'latitude'    => 14.5564,
            'longitude'   => 121.0177,
            'status'      => 'active',
            'opened_date' => now()->subMonths(6),
        ]);

        $storeQC = Store::create([
            'owner_id'    => $owner->id,
            'name'        => 'Quezon City Store',
            'code'        => 'STR002',
            'address'     => '456 Quezon Avenue, Quezon City',
            'phone'       => '+63287654321',
            'email'       => 'qc@pabili.ph',
            'city'        => 'Quezon City',
            'province'    => 'Metro Manila',
            'postal_code' => '1102',
            'latitude'    => 14.6349,
            'longitude'   => 121.0388,
            'status'      => 'active',
            'opened_date' => now()->subMonths(3),
        ]);

        // Create Staff
        $managerManila = User::create([
            'first_name'    => 'Maria',
            'last_name'     => 'Santos',
            'email'         => 'manager.manila@pabili.test',
            'password_hash' => Hash::make('password123'),
            'role'          => 'manager',
            'status'        => 'active',
            'pin'           => Hash::make('2345'),
        ]);

        $cashierManila = User::create([
            'first_name'    => 'Juan',
            'last_name'     => 'Dela Cruz',
            'email'         => 'cashier.manila@pabili.test',
            'password_hash' => Hash::make('password123'),
            'role'          => 'cashier',
            'status'        => 'active',
        ]);

        $managerQC = User::create([
            'first_name'    => 'Pedro',
            'last_name'     => 'Garcia',
            'email'         => 'manager.qc@pabili.test',
            'password_hash' => Hash::make('password123'),
            'role'          => 'manager',
            'status'        => 'active',
            'pin'           => Hash::make('3456'),
        ]);

        $cashierQC = User::create([
            'first_name'    => 'Rosa',
            'last_name'     => 'Reyes',
            'email'         => 'cashier.qc@pabili.test',
            'password_hash' => Hash::make('password123'),
            'role'          => 'cashier',
            'status'        => 'active',
        ]);

        // Assign staff to stores
        $managerManila->assignedStore()->attach($storeManila->id);
        $cashierManila->assignedStore()->attach($storeManila->id);
        $managerQC->assignedStore()->attach($storeQC->id);
        $cashierQC->assignedStore()->attach($storeQC->id);

        $this->call(BulkDataSeeder::class);

        // ── Manila Store ──────────────────────────────────────────────────────

        $beverages = Category::create([
            'store_id'    => $storeManila->id,
            'name'        => 'Beverages',
            'description' => 'Soft drinks, juices, and beverages',
            'status'      => 'active',
        ]);

        $snacks = Category::create([
            'store_id'    => $storeManila->id,
            'name'        => 'Snacks',
            'description' => 'Chips, crackers, and other snacks',
            'status'      => 'active',
        ]);

        $groceries = Category::create([
            'store_id'    => $storeManila->id,
            'name'        => 'Groceries',
            'description' => 'Household items and groceries',
            'status'      => 'active',
        ]);

        $manilaProducts = [
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $beverages->id,
                'name'         => 'Coca Cola 1.5L',
                'sku'          => 'COKE001',
                'description'  => 'Coca Cola bottled soft drink',
                'cost_price'   => 25.00,
                'selling_price'=> 50.00,
                'stock'        => 100,
                'reorder_level'=> 20,
                'status'       => 'active',
            ],
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $beverages->id,
                'name'         => 'Sprite 1.5L',
                'sku'          => 'SPRITE001',
                'description'  => 'Sprite lemon-lime soft drink',
                'cost_price'   => 22.00,
                'selling_price'=> 45.00,
                'stock'        => 75,
                'reorder_level'=> 15,
                'status'       => 'active',
            ],
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $snacks->id,
                'name'         => "Lay's Potato Chips",
                'sku'          => 'LAYS001',
                'description'  => 'Crispy potato chips',
                'cost_price'   => 8.00,
                'selling_price'=> 15.00,
                'stock'        => 200,
                'reorder_level'=> 50,
                'status'       => 'active',
            ],
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $snacks->id,
                'name'         => 'Cheetos',
                'sku'          => 'CHEETOS001',
                'description'  => 'Cheesy puffs snack',
                'cost_price'   => 7.50,
                'selling_price'=> 14.00,
                'stock'        => 150,
                'reorder_level'=> 40,
                'status'       => 'active',
            ],
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $groceries->id,
                'name'         => 'Rice 5kg',
                'sku'          => 'RICE001',
                'description'  => 'Premium white rice',
                'cost_price'   => 150.00,
                'selling_price'=> 250.00,
                'stock'        => 30,
                'reorder_level'=> 10,
                'status'       => 'active',
            ],
            [
                'store_id'     => $storeManila->id,
                'category_id'  => $groceries->id,
                'name'         => 'Sugar 1kg',
                'sku'          => 'SUGAR001',
                'description'  => 'Refined white sugar',
                'cost_price'   => 35.00,
                'selling_price'=> 55.00,
                'stock'        => 50,
                'reorder_level'=> 20,
                'status'       => 'active',
            ],
        ];

        foreach ($manilaProducts as $product) {
            Product::create($product);
        }

        DiscountType::create([
            'store_id'         => $storeManila->id,
            'code'             => 'senior-citizen',
            'name'             => 'Senior Citizen',
            'default_pct'      => 20.00,
            'requires_override'=> false,
            'is_active'        => true,
        ]);

        DiscountType::create([
            'store_id'         => $storeManila->id,
            'code'             => 'pwd',
            'name'             => 'PWD',
            'default_pct'      => 12.00,
            'requires_override'=> false,
            'is_active'        => true,
        ]);

        DiscountType::create([
            'store_id'         => $storeManila->id,
            'code'             => 'staff-discount',
            'name'             => 'Staff Discount',
            'default_pct'      => 10.00,
            'max_pct'          => 10.00,
            'requires_override'=> true,
            'is_active'        => true,
        ]);

        Customer::create([
            'code'          => 'CUST001',
            'first_name'    => 'Juan',
            'last_name'     => 'Dela Cruz',
            'email'         => 'juan@email.com',
            'phone'         => '+639123456789',
            'customer_type' => 'regular',
            'credit_limit'  => 5000.00,
            'loyalty_points'=> 100,
            'status'        => 'active',
        ]);

        Customer::create([
            'code'          => 'CUST002',
            'first_name'    => 'Maria',
            'last_name'     => 'Garcia',
            'email'         => 'maria@email.com',
            'phone'         => '+639987654321',
            'customer_type' => 'vip',
            'credit_limit'  => 10000.00,
            'loyalty_points'=> 500,
            'status'        => 'active',
        ]);

        Setting::create([
            'store_name'           => $storeManila->name,
            'store_address'        => $storeManila->address,
            'store_phone'          => $storeManila->phone,
            'store_email'          => $storeManila->email,
            'currency'             => 'PHP',
            'tax_rate'             => 12.00,
            'receipt_footer'       => 'Thank you for your purchase!',
            'loyalty_points_per_peso' => 0.1,
        ]);

        // ── QC Store ──────────────────────────────────────────────────────────

        $beveragesQC = Category::create([
            'store_id'    => $storeQC->id,
            'name'        => 'Beverages',
            'description' => 'Soft drinks, juices, and beverages',
            'status'      => 'active',
        ]);

        $snacksQC = Category::create([
            'store_id'    => $storeQC->id,
            'name'        => 'Snacks',
            'description' => 'Chips, crackers, and other snacks',
            'status'      => 'active',
        ]);

        $groceriesQC = Category::create([
            'store_id'    => $storeQC->id,
            'name'        => 'Groceries',
            'description' => 'Household items and groceries',
            'status'      => 'active',
        ]);

        $categoryMap = [
            $beverages->id => $beveragesQC->id,
            $snacks->id    => $snacksQC->id,
            $groceries->id => $groceriesQC->id,
        ];

        foreach ($manilaProducts as $product) {
            $product['store_id']    = $storeQC->id;
            $product['category_id'] = $categoryMap[$product['category_id']];
            $product['sku']         = $product['sku'] . '-QC';
            Product::create($product);
        }

        DiscountType::create([
            'store_id'         => $storeQC->id,
            'code'             => 'senior-citizen-qc',
            'name'             => 'Senior Citizen',
            'default_pct'      => 20.00,
            'requires_override'=> false,
            'is_active'        => true,
        ]);

        DiscountType::create([
            'store_id'         => $storeQC->id,
            'code'             => 'pwd-qc',
            'name'             => 'PWD',
            'default_pct'      => 12.00,
            'requires_override'=> false,
            'is_active'        => true,
        ]);

        Customer::create([
            'code'          => 'CUST003',
            'first_name'    => 'Pedro',
            'last_name'     => 'Reyes',
            'email'         => 'pedro@email.com',
            'phone'         => '+639111111111',
            'customer_type' => 'regular',
            'credit_limit'  => 3000.00,
            'loyalty_points'=> 50,
            'status'        => 'active',
        ]);
    }
}
