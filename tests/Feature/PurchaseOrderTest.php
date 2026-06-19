<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = User::factory()->create();
        $this->supplier = Supplier::create(['code' => 'SUP-001', 'name' => 'Test Supplier', 'status' => 'active']);
        $category       = Category::create(['name' => 'Test', 'status' => 'active']);
        $this->product  = Product::create([
            'sku' => 'P-001', 'name' => 'Test Product', 'category_id' => $category->id,
            'selling_price' => 50, 'cost_price' => 30, 'unit' => 'piece', 'stock' => 100, 'status' => 'active',
        ]);
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makePO(array $attrs = []): PurchaseOrder
    {
        return PurchaseOrder::create(array_merge([
            'po_number'   => 'PO-' . uniqid(),
            'supplier_id' => $this->supplier->id,
            'order_date'  => now()->toDateString(),
            'created_by'  => $this->user->id,
            'status'      => 'draft',
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_purchase_orders(): void
    {
        $this->getJson('/api/purchase-orders')->assertStatus(401);
    }

    public function test_can_list_purchase_orders(): void
    {
        $this->makePO();
        $this->auth()->getJson('/api/purchase-orders')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_can_create_purchase_order(): void
    {
        $response = $this->auth()->postJson('/api/purchase-orders', [
            'supplier_id'   => $this->supplier->id,
            'order_date'    => '2026-06-01',
            'expected_date' => '2026-06-15',
            'items'         => [[
                'product_id' => $this->product->id,
                'quantity'   => 10,
                'unit_cost'  => 30.00,
            ]],
        ]);

        $response->assertStatus(201)->assertJsonPath('data.status', 'draft');
        $this->assertDatabaseHas('purchase_orders', ['supplier_id' => $this->supplier->id]);
    }

    public function test_create_po_requires_supplier_and_items(): void
    {
        $this->auth()->postJson('/api/purchase-orders', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['supplier_id', 'order_date', 'items']);
    }

    public function test_can_show_purchase_order(): void
    {
        $po = $this->makePO();
        $this->auth()->getJson("/api/purchase-orders/{$po->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $po->id);
    }

    public function test_can_update_draft_po(): void
    {
        $po = $this->makePO();
        $this->auth()->putJson("/api/purchase-orders/{$po->id}", ['notes' => 'Urgent order'])
             ->assertStatus(200);
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'notes' => 'Urgent order']);
    }

    public function test_cannot_update_approved_po(): void
    {
        $po = $this->makePO(['status' => 'approved']);
        $this->auth()->putJson("/api/purchase-orders/{$po->id}", ['notes' => 'Too late'])
             ->assertJsonPath('success', false);
    }

    public function test_can_approve_po(): void
    {
        $po = $this->makePO();
        $this->auth()->postJson("/api/purchase-orders/{$po->id}/approve")
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'approved');
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'approved']);
    }

    public function test_can_cancel_po(): void
    {
        $po = $this->makePO();
        $this->auth()->postJson("/api/purchase-orders/{$po->id}/cancel")
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_can_delete_draft_po(): void
    {
        $po = $this->makePO();
        $this->auth()->deleteJson("/api/purchase-orders/{$po->id}")
             ->assertStatus(200);
        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }

    public function test_cannot_delete_approved_po(): void
    {
        $po = $this->makePO(['status' => 'approved']);
        $this->auth()->deleteJson("/api/purchase-orders/{$po->id}")
             ->assertJsonPath('success', false);
    }
}
