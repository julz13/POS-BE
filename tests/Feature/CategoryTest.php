<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth()
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    private function makeCategory(array $attrs = []): Category
    {
        return Category::create(array_merge([
            'name'   => 'Test Category ' . uniqid(),
            'status' => 'active',
        ], $attrs));
    }

    // ── GET /api/categories ────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_categories(): void
    {
        $this->getJson('/api/categories')->assertStatus(401);
    }

    public function test_can_list_categories(): void
    {
        $this->makeCategory(['name' => 'Beverages']);
        $this->makeCategory(['name' => 'Snacks']);

        $response = $this->auth()->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_inactive_categories_excluded_from_list(): void
    {
        $this->makeCategory(['name' => 'Active Cat', 'status' => 'active']);
        $this->makeCategory(['name' => 'Inactive Cat', 'status' => 'inactive']);

        $response = $this->auth()->getJson('/api/categories');

        $names = collect($response->json('data.data'))->pluck('name');
        $this->assertContains('Active Cat', $names);
        $this->assertNotContains('Inactive Cat', $names);
    }

    // ── POST /api/categories ───────────────────────────────────────────────────

    public function test_can_create_category(): void
    {
        $response = $this->auth()->postJson('/api/categories', [
            'name'        => 'New Category',
            'description' => 'A test category',
            'sort_order'  => 1,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Category');

        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    public function test_create_category_requires_name(): void
    {
        $this->auth()->postJson('/api/categories', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_create_category_name_must_be_unique(): void
    {
        $this->makeCategory(['name' => 'Duplicate']);

        $this->auth()->postJson('/api/categories', ['name' => 'Duplicate'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    // ── GET /api/categories/{id} ───────────────────────────────────────────────

    public function test_can_show_category(): void
    {
        $category = $this->makeCategory(['name' => 'Groceries']);

        $this->auth()->getJson("/api/categories/{$category->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'Groceries');
    }

    public function test_show_returns_404_for_missing_category(): void
    {
        $this->auth()->getJson('/api/categories/9999')
             ->assertStatus(404);
    }

    // ── PUT /api/categories/{id} ───────────────────────────────────────────────

    public function test_can_update_category(): void
    {
        $category = $this->makeCategory(['name' => 'Old Name']);

        $this->auth()->putJson("/api/categories/{$category->id}", [
            'name' => 'New Name',
        ])->assertStatus(200)
          ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_can_deactivate_category_via_update(): void
    {
        $category = $this->makeCategory(['status' => 'active']);

        $this->auth()->putJson("/api/categories/{$category->id}", [
            'status' => 'inactive',
        ])->assertStatus(200);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'status' => 'inactive']);
    }

    // ── DELETE /api/categories/{id} ────────────────────────────────────────────

    public function test_can_delete_category(): void
    {
        $category = $this->makeCategory();

        $this->auth()->deleteJson("/api/categories/{$category->id}")
             ->assertStatus(200)
             ->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'status' => 'inactive']);
    }
}
