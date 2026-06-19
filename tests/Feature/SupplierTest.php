<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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

    private function makeSupplier(array $attrs = []): Supplier
    {
        return Supplier::create(array_merge([
            'code'   => 'SUP-' . uniqid(),
            'name'   => 'Test Supplier',
            'status' => 'active',
        ], $attrs));
    }

    // ── GET /api/suppliers ─────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_suppliers(): void
    {
        $this->getJson('/api/suppliers')->assertStatus(401);
    }

    public function test_can_list_suppliers(): void
    {
        $this->makeSupplier(['name' => 'San Miguel Corp']);
        $this->makeSupplier(['name' => 'Nestlé Philippines']);

        $response = $this->auth()->getJson('/api/suppliers');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    // ── POST /api/suppliers ────────────────────────────────────────────────────

    public function test_can_create_supplier(): void
    {
        $response = $this->auth()->postJson('/api/suppliers', [
            'code'           => 'SUP-001',
            'name'           => 'New Supplier Inc.',
            'contact_person' => 'Juan Santos',
            'phone'          => '+63-2-1234-5678',
            'email'          => 'supplier@example.com',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Supplier Inc.')
                 ->assertJsonPath('data.code', 'SUP-001');

        $this->assertDatabaseHas('suppliers', ['code' => 'SUP-001']);
    }

    public function test_create_supplier_requires_name(): void
    {
        $this->auth()->postJson('/api/suppliers', ['code' => 'SUP-002'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_create_supplier_requires_code(): void
    {
        $this->auth()->postJson('/api/suppliers', ['name' => 'No Code Supplier'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['code']);
    }

    public function test_create_supplier_code_must_be_unique(): void
    {
        $this->makeSupplier(['code' => 'DUPE-SUP']);

        $this->auth()->postJson('/api/suppliers', [
            'code' => 'DUPE-SUP',
            'name' => 'Another Supplier',
        ])->assertStatus(422)->assertJsonValidationErrors(['code']);
    }

    public function test_create_supplier_email_must_be_valid(): void
    {
        $this->auth()->postJson('/api/suppliers', [
            'code'  => 'SUP-003',
            'name'  => 'Bad Email Supplier',
            'email' => 'not-an-email',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // ── GET /api/suppliers/{id} ────────────────────────────────────────────────

    public function test_can_show_supplier(): void
    {
        $supplier = $this->makeSupplier(['name' => 'Visible Supplier']);

        $this->auth()->getJson("/api/suppliers/{$supplier->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'Visible Supplier');
    }

    public function test_show_returns_404_for_missing_supplier(): void
    {
        $this->auth()->getJson('/api/suppliers/9999')
             ->assertStatus(404);
    }

    // ── PUT /api/suppliers/{id} ────────────────────────────────────────────────

    public function test_can_update_supplier(): void
    {
        $supplier = $this->makeSupplier(['name' => 'Old Name']);

        $this->auth()->putJson("/api/suppliers/{$supplier->id}", [
            'name'           => 'Updated Name',
            'contact_person' => 'Maria Reyes',
        ])->assertStatus(200)
          ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('suppliers', [
            'id'   => $supplier->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_deactivate_supplier(): void
    {
        $supplier = $this->makeSupplier(['status' => 'active']);

        $this->auth()->putJson("/api/suppliers/{$supplier->id}", [
            'status' => 'inactive',
        ])->assertStatus(200);

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'status' => 'inactive']);
    }

    // ── DELETE /api/suppliers/{id} ─────────────────────────────────────────────

    public function test_can_delete_supplier(): void
    {
        $supplier = $this->makeSupplier();

        $this->auth()->deleteJson("/api/suppliers/{$supplier->id}")
             ->assertStatus(200)
             ->assertJsonPath('success', true);

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
