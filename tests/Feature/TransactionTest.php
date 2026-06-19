<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $category = Category::create([
            'name'   => 'Beverages',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'sku'             => 'COKE-001',
            'name'            => 'Coca-Cola 1.5L',
            'category_id'     => $category->id,
            'selling_price'   => 50.00,
            'cost_price'      => 30.00,
            'unit'            => 'bottle',
            'stock'           => 100,
            'track_inventory' => true,
            'status'          => 'active',
        ]);
    }

    private function auth()
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    private function transactionPayload(array $overrides = []): array
    {
        return array_merge([
            'subtotal'    => 50.00,
            'total'       => 50.00,
            'amount_paid' => 50.00,
            'change_given'=> 0,
            'items'       => [
                [
                    'product_id'   => $this->product->id,
                    'product_name' => $this->product->name,
                    'sku'          => $this->product->sku,
                    'unit'         => $this->product->unit,
                    'quantity'     => 1,
                    'unit_price'   => 50.00,
                    'line_total'   => 50.00,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount'         => 50.00,
                ],
            ],
        ], $overrides);
    }

    // ── GET /api/transactions ──────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_transactions(): void
    {
        $this->getJson('/api/transactions')->assertStatus(401);
    }

    public function test_can_list_transactions(): void
    {
        $response = $this->auth()->getJson('/api/transactions');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    // ── POST /api/transactions ─────────────────────────────────────────────────

    public function test_can_create_transaction(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'completed')
                 ->assertJsonPath('data.total', 50);

        $this->assertDatabaseHas('transactions', [
            'cashier_id' => $this->user->id,
            'total'      => 50.00,
            'status'     => 'completed',
        ]);
    }

    public function test_change_given_is_calculated_from_amount_paid_minus_total(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload([
            'subtotal'    => 328.00,
            'total'       => 328.00,
            'amount_paid' => 400.00,
            'items'       => [[
                'product_id'   => $this->product->id,
                'product_name' => $this->product->name,
                'sku'          => $this->product->sku,
                'unit'         => $this->product->unit,
                'quantity'     => 1,
                'unit_price'   => 328.00,
                'line_total'   => 328.00,
            ]],
            'payments' => [['payment_method' => 'cash', 'amount' => 400.00]],
        ]));

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'amount_paid'  => 400.00,
            'total'        => 328.00,
            'change_given' => 72.00,
        ]);
    }

    public function test_change_given_is_zero_when_exact_payment(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());

        $this->assertDatabaseHas('transactions', [
            'amount_paid'  => 50.00,
            'total'        => 50.00,
            'change_given' => 0.00,
        ]);
    }

    public function test_transaction_deducts_stock(): void
    {
        $initialStock = $this->product->stock;

        $this->auth()->postJson('/api/transactions', $this->transactionPayload([
            'items' => [
                [
                    'product_id'   => $this->product->id,
                    'product_name' => $this->product->name,
                    'sku'          => $this->product->sku,
                    'unit'         => $this->product->unit,
                    'quantity'     => 3,
                    'unit_price'   => 50.00,
                    'line_total'   => 150.00,
                ],
            ],
            'subtotal'    => 150.00,
            'total'       => 150.00,
            'amount_paid' => 150.00,
            'payments'    => [['payment_method' => 'cash', 'amount' => 150.00]],
        ]));

        $this->assertDatabaseHas('products', [
            'id'    => $this->product->id,
            'stock' => $initialStock - 3,
        ]);
    }

    public function test_transaction_assigns_receipt_number(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());

        $receiptNumber = $response->json('data.receipt_number');
        $this->assertStringStartsWith('RCP-', $receiptNumber);
    }

    public function test_create_transaction_with_customer(): void
    {
        $customer = Customer::create([
            'code'       => 'CUST-T001',
            'first_name' => 'Test',
            'last_name'  => 'Customer',
            'phone'      => '+639170000099',
            'status'     => 'active',
        ]);

        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload([
            'customer_id' => $customer->id,
        ]));

        $response->assertStatus(201)
                 ->assertJsonPath('data.customer_id', $customer->id);
    }

    public function test_create_transaction_requires_items(): void
    {
        $payload = $this->transactionPayload();
        unset($payload['items']);

        $this->auth()->postJson('/api/transactions', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['items']);
    }

    public function test_create_transaction_requires_payments(): void
    {
        $payload = $this->transactionPayload();
        unset($payload['payments']);

        $this->auth()->postJson('/api/transactions', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['payments']);
    }

    public function test_create_transaction_requires_subtotal_and_total(): void
    {
        $payload = $this->transactionPayload();
        unset($payload['subtotal'], $payload['total']);

        $this->auth()->postJson('/api/transactions', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['subtotal', 'total']);
    }

    // ── GET /api/transactions/{id} ─────────────────────────────────────────────

    public function test_can_show_transaction(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());
        $id       = $response->json('data.id');

        $this->auth()->getJson("/api/transactions/{$id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $id);
    }

    public function test_show_returns_404_for_missing_transaction(): void
    {
        $this->auth()->getJson('/api/transactions/9999')
             ->assertStatus(404);
    }

    // ── POST /api/transactions/{id}/void ──────────────────────────────────────

    public function test_can_void_transaction(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());
        $id       = $response->json('data.id');

        $this->auth()->postJson("/api/transactions/{$id}/void", [
            'void_reason' => 'Customer changed mind',
        ])->assertStatus(200)
          ->assertJsonPath('data.status', 'voided');

        $this->assertDatabaseHas('transactions', [
            'id'          => $id,
            'status'      => 'voided',
            'void_reason' => 'Customer changed mind',
        ]);
    }

    public function test_void_requires_void_reason(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());
        $id       = $response->json('data.id');

        $this->auth()->postJson("/api/transactions/{$id}/void", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['void_reason']);
    }

    // ── DELETE /api/transactions/{id} ─────────────────────────────────────────

    public function test_cannot_delete_transaction(): void
    {
        $response = $this->auth()->postJson('/api/transactions', $this->transactionPayload());
        $id       = $response->json('data.id');

        $this->auth()->deleteJson("/api/transactions/{$id}")
             ->assertJsonPath('success', false);
    }

    // ── GET /api/transactions/receipt/{receipt_number} ────────────────────────

    public function test_can_get_transaction_by_receipt_number(): void
    {
        $response      = $this->auth()->postJson('/api/transactions', $this->transactionPayload());
        $receiptNumber = $response->json('data.receipt_number');

        $this->auth()->getJson("/api/transactions/receipt/{$receiptNumber}")
             ->assertStatus(200)
             ->assertJsonPath('data.receipt_number', $receiptNumber);
    }

    public function test_get_by_receipt_number_returns_404_for_missing(): void
    {
        $this->auth()->getJson('/api/transactions/receipt/RCP-NOTREAL')
             ->assertStatus(404);
    }
}
