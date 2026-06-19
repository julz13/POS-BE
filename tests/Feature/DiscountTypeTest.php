<?php

namespace Tests\Feature;

use App\Models\DiscountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makeDiscount(array $attrs = []): DiscountType
    {
        return DiscountType::create(array_merge([
            'code'        => 'DISC-' . strtoupper(uniqid()),
            'name'        => 'Test Discount',
            'default_pct' => 10,
            'max_pct'     => 50,
            'is_active'   => true,
            'sort_order'  => 0,
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_discount_types(): void
    {
        $this->getJson('/api/discount-types')->assertStatus(401);
    }

    public function test_can_list_active_discount_types(): void
    {
        $this->makeDiscount(['name' => 'Senior', 'is_active' => true]);
        $this->makeDiscount(['name' => 'Inactive', 'is_active' => false]);

        $response = $this->auth()->getJson('/api/discount-types');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $names = collect($response->json('data.data'))->pluck('name');
        $this->assertContains('Senior', $names);
        $this->assertNotContains('Inactive', $names);
    }

    public function test_can_create_discount_type(): void
    {
        $this->auth()->postJson('/api/discount-types', [
            'code'        => 'SENIOR',
            'name'        => 'Senior Citizen',
            'default_pct' => 20,
            'max_pct'     => 20,
        ])->assertStatus(201)
          ->assertJsonPath('data.code', 'SENIOR');

        $this->assertDatabaseHas('discount_types', ['code' => 'SENIOR']);
    }

    public function test_create_requires_code_and_name(): void
    {
        $this->auth()->postJson('/api/discount-types', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_code_must_be_unique(): void
    {
        $this->makeDiscount(['code' => 'SENIOR']);
        $this->auth()->postJson('/api/discount-types', ['code' => 'SENIOR', 'name' => 'Dup'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['code']);
    }

    public function test_can_show_discount_type(): void
    {
        $d = $this->makeDiscount(['name' => 'PWD']);
        $this->auth()->getJson("/api/discount-types/{$d->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'PWD');
    }

    public function test_can_update_discount_type(): void
    {
        $d = $this->makeDiscount(['default_pct' => 5]);
        $this->auth()->putJson("/api/discount-types/{$d->id}", ['default_pct' => 15])
             ->assertStatus(200);
        $this->assertDatabaseHas('discount_types', ['id' => $d->id, 'default_pct' => 15]);
    }

    public function test_destroy_deactivates_discount_type(): void
    {
        $d = $this->makeDiscount(['is_active' => true]);
        $this->auth()->deleteJson("/api/discount-types/{$d->id}")
             ->assertStatus(200);
        $this->assertDatabaseHas('discount_types', ['id' => $d->id, 'is_active' => false]);
    }
}
