<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner   = User::factory()->owner()->create();
        $this->cashier = User::factory()->create(['role' => 'cashier']);
    }

    private function makeStore(array $attrs = []): Store
    {
        return Store::create(array_merge([
            'owner_id'    => $this->owner->id,
            'name'        => 'Test Store',
            'code'        => 'TST-' . uniqid(),
            'status'      => 'active',
            'opened_date' => now()->subMonth(),
        ], $attrs));
    }

    // ── GET /api/stores ────────────────────────────────────────────────────────

    public function test_owner_can_list_own_stores(): void
    {
        $this->makeStore(['name' => 'Manila Store']);
        $this->makeStore(['name' => 'QC Store']);

        $this->actingAs($this->owner, 'sanctum')
             ->getJson('/api/stores')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    // ── POST /api/stores ───────────────────────────────────────────────────────

    public function test_owner_can_create_store(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')
             ->postJson('/api/stores', [
                 'name'        => 'New Branch',
                 'code'        => 'NB-001',
                 'status'      => 'active',
                 'opened_date' => '2026-01-01',
             ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Branch')
                 ->assertJsonPath('data.owner_id', $this->owner->id);
    }

    public function test_cashier_cannot_create_store(): void
    {
        $this->actingAs($this->cashier, 'sanctum')
             ->postJson('/api/stores', [
                 'name' => 'Fail', 'code' => 'F-001', 'status' => 'active', 'opened_date' => '2026-01-01',
             ])
             ->assertStatus(403);
    }

    public function test_create_store_requires_name_and_code(): void
    {
        $this->actingAs($this->owner, 'sanctum')
             ->postJson('/api/stores', ['status' => 'active', 'opened_date' => '2026-01-01'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'code']);
    }

    // ── GET /api/stores/{id} ───────────────────────────────────────────────────

    public function test_owner_can_show_own_store(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->owner, 'sanctum')
             ->getJson("/api/stores/{$store->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $store->id);
    }

    // ── PUT /api/stores/{id} ───────────────────────────────────────────────────

    public function test_owner_can_update_store(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->owner, 'sanctum')
             ->putJson("/api/stores/{$store->id}", ['name' => 'Renamed Store'])
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'Renamed Store');
    }

    public function test_cashier_cannot_update_store(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->cashier, 'sanctum')
             ->putJson("/api/stores/{$store->id}", ['name' => 'Nope'])
             ->assertStatus(403);
    }

    // ── DELETE /api/stores/{id} ────────────────────────────────────────────────

    public function test_owner_can_delete_store(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->owner, 'sanctum')
             ->deleteJson("/api/stores/{$store->id}")
             ->assertStatus(200);
        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }

    public function test_cashier_cannot_delete_store(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->cashier, 'sanctum')
             ->deleteJson("/api/stores/{$store->id}")
             ->assertStatus(403);
    }
}
