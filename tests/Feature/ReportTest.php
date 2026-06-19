<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function createTransaction(): void
    {
        $category = Category::create(['name' => 'Test', 'status' => 'active']);
        $product  = Product::create([
            'sku' => 'RPT-' . uniqid(), 'name' => 'Report Product',
            'category_id' => $category->id, 'selling_price' => 100,
            'unit' => 'piece', 'stock' => 50, 'status' => 'active',
        ]);

        $this->auth()->postJson('/api/transactions', [
            'subtotal' => 100, 'total' => 100, 'amount_paid' => 100,
            'items' => [[
                'product_id' => $product->id, 'product_name' => $product->name,
                'sku' => $product->sku, 'unit' => 'piece', 'quantity' => 1,
                'unit_price' => 100, 'line_total' => 100,
            ]],
            'payments' => [['payment_method' => 'cash', 'amount' => 100]],
        ]);
    }

    // ── GET /api/reports/sales ─────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_sales_report(): void
    {
        $this->getJson('/api/reports/sales')->assertStatus(401);
    }

    public function test_can_get_sales_report(): void
    {
        $this->createTransaction();

        $response = $this->auth()->getJson('/api/reports/sales');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => [
                     'total_transactions', 'total_sales', 'transactions',
                 ]]);
    }

    public function test_sales_report_filters_by_date(): void
    {
        $this->createTransaction();

        $this->auth()->getJson('/api/reports/sales?start_date=' . now()->toDateString())
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_sales_report_transactions_are_paginated(): void
    {
        $this->createTransaction();

        $response = $this->auth()->getJson('/api/reports/sales?per_page=5');

        $response->assertStatus(200);
        $transactions = $response->json('data.transactions');
        $this->assertArrayHasKey('current_page', $transactions);
        $this->assertArrayHasKey('per_page', $transactions);
    }

    // ── GET /api/reports/inventory ─────────────────────────────────────────────

    public function test_can_get_inventory_report(): void
    {
        $this->auth()->getJson('/api/reports/inventory')
             ->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['total_products', 'total_movements']]);
    }

    // ── GET /api/reports/audit-log ─────────────────────────────────────────────

    public function test_can_get_audit_log(): void
    {
        $this->auth()->getJson('/api/reports/audit-log')
             ->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['data']]);
    }
}
