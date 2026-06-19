<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = User::factory()->create();
        $this->category = Category::create([
            'name'   => 'Test Category',
            'status' => 'active',
        ]);
    }

    private function auth()
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'sku'           => 'SKU-' . uniqid(),
            'name'          => 'Test Product',
            'category_id'   => $this->category->id,
            'selling_price' => 100.00,
            'cost_price'    => 60.00,
            'unit'          => 'piece',
            'stock'         => 50,
            'status'        => 'active',
        ], $attrs));
    }

    // ── GET /api/products ──────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_products(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_can_list_products(): void
    {
        $this->makeProduct(['name' => 'Coke 1.5L']);
        $this->makeProduct(['name' => 'Sprite 1.5L']);

        $response = $this->auth()->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $other = Category::create(['name' => 'Other', 'status' => 'active']);
        $this->makeProduct(['name' => 'In Category', 'category_id' => $this->category->id]);
        $this->makeProduct(['name' => 'Other Product', 'category_id' => $other->id]);

        $response = $this->auth()
                         ->getJson("/api/products?category_id={$this->category->id}");

        $names = collect($response->json('data.data'))->pluck('name');
        $this->assertContains('In Category', $names);
        $this->assertNotContains('Other Product', $names);
    }

    // ── POST /api/products ─────────────────────────────────────────────────────

    public function test_can_create_product(): void
    {
        $response = $this->auth()->postJson('/api/products', [
            'sku'           => 'TEST-001',
            'name'          => 'New Product',
            'category_id'   => $this->category->id,
            'selling_price' => 50.00,
            'cost_price'    => 30.00,
            'unit'          => 'bottle',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Product')
                 ->assertJsonPath('data.sku', 'TEST-001');

        $this->assertDatabaseHas('products', ['sku' => 'TEST-001']);
    }

    public function test_create_product_requires_sku(): void
    {
        $this->auth()->postJson('/api/products', [
            'name'          => 'No SKU',
            'category_id'   => $this->category->id,
            'selling_price' => 10,
            'unit'          => 'piece',
        ])->assertStatus(422)->assertJsonValidationErrors(['sku']);
    }

    public function test_create_product_requires_name(): void
    {
        $this->auth()->postJson('/api/products', [
            'sku'           => 'SKU-001',
            'category_id'   => $this->category->id,
            'selling_price' => 10,
            'unit'          => 'piece',
        ])->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_create_product_requires_category(): void
    {
        $this->auth()->postJson('/api/products', [
            'sku'           => 'SKU-001',
            'name'          => 'No Cat',
            'selling_price' => 10,
            'unit'          => 'piece',
        ])->assertStatus(422)->assertJsonValidationErrors(['category_id']);
    }

    public function test_create_product_sku_must_be_unique(): void
    {
        $this->makeProduct(['sku' => 'DUPE-001']);

        $this->auth()->postJson('/api/products', [
            'sku'           => 'DUPE-001',
            'name'          => 'Duplicate SKU',
            'category_id'   => $this->category->id,
            'selling_price' => 10,
            'unit'          => 'piece',
        ])->assertStatus(422)->assertJsonValidationErrors(['sku']);
    }

    // ── GET /api/products/{id} ─────────────────────────────────────────────────

    public function test_can_show_product(): void
    {
        $product = $this->makeProduct(['name' => 'Show Me']);

        $this->auth()->getJson("/api/products/{$product->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'Show Me');
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->auth()->getJson('/api/products/9999')
             ->assertStatus(404);
    }

    // ── PUT /api/products/{id} ─────────────────────────────────────────────────

    public function test_can_update_product(): void
    {
        $product = $this->makeProduct(['name' => 'Old Name', 'selling_price' => 50.00]);

        $this->auth()->putJson("/api/products/{$product->id}", [
            'name'          => 'Updated Name',
            'selling_price' => 75.00,
        ])->assertStatus(200)
          ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('products', [
            'id'            => $product->id,
            'name'          => 'Updated Name',
            'selling_price' => 75.00,
        ]);
    }

    // ── DELETE /api/products/{id} ──────────────────────────────────────────────

    public function test_can_delete_product(): void
    {
        $product = $this->makeProduct();

        $this->auth()->deleteJson("/api/products/{$product->id}")
             ->assertStatus(200)
             ->assertJsonPath('success', true);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    // ── GET /api/products/search/{term} ───────────────────────────────────────

    public function test_can_search_products_by_name(): void
    {
        $this->makeProduct(['name' => 'Coca Cola 1.5L', 'sku' => 'COKE-001']);
        $this->makeProduct(['name' => 'Sprite 500ml', 'sku' => 'SPRITE-001']);

        $response = $this->auth()->getJson('/api/products/search/Coca');

        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('Coca Cola 1.5L', $names);
        $this->assertNotContains('Sprite 500ml', $names);
    }

    public function test_can_search_products_by_sku(): void
    {
        $this->makeProduct(['name' => 'Some Product', 'sku' => 'FIND-ME-001']);

        $response = $this->auth()->getJson('/api/products/search/FIND-ME');

        $skus = collect($response->json('data'))->pluck('sku');
        $this->assertContains('FIND-ME-001', $skus);
    }
}
