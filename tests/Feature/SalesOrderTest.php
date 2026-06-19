<?php

namespace Tests\Feature;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makeSO(array $attrs = []): SalesOrder
    {
        return SalesOrder::create(array_merge([
            'so_number'     => 'SO-' . uniqid(),
            'customer_name' => 'Walk-in',
            'order_date'    => now(),
            'created_by'    => $this->user->id,
            'status'        => 'draft',
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_sales_orders(): void
    {
        $this->getJson('/api/sales-orders')->assertStatus(401);
    }

    public function test_can_list_sales_orders(): void
    {
        $this->makeSO();
        $this->auth()->getJson('/api/sales-orders')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_can_create_sales_order(): void
    {
        $this->auth()->postJson('/api/sales-orders', [
            'customer_name' => 'Maria Garcia',
            'order_date'    => '2026-06-20',
            'items'         => [['name' => 'Sample Item']],
        ])->assertStatus(201)
          ->assertJsonPath('data.status', 'draft');
    }

    public function test_create_requires_order_date_and_items(): void
    {
        $this->auth()->postJson('/api/sales-orders', ['customer_name' => 'Test'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['order_date', 'items']);
    }

    public function test_can_show_sales_order(): void
    {
        $so = $this->makeSO();
        $this->auth()->getJson("/api/sales-orders/{$so->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $so->id);
    }

    public function test_can_delete_sales_order(): void
    {
        $so = $this->makeSO();
        $this->auth()->deleteJson("/api/sales-orders/{$so->id}")
             ->assertStatus(200);
        $this->assertDatabaseMissing('sales_orders', ['id' => $so->id]);
    }
}
