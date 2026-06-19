<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    private function makeCustomer(array $attrs = []): Customer
    {
        return Customer::create(array_merge([
            'code'          => 'CUST-' . uniqid(),
            'first_name'    => 'Juan',
            'last_name'     => 'Dela Cruz',
            'phone'         => '+639' . rand(100000000, 999999999),
            'customer_type' => 'regular',
            'status'        => 'active',
        ], $attrs));
    }

    // ── GET /api/customers ─────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_customers(): void
    {
        $this->getJson('/api/customers')->assertStatus(401);
    }

    public function test_can_list_customers(): void
    {
        $this->makeCustomer(['first_name' => 'Maria']);
        $this->makeCustomer(['first_name' => 'Pedro']);

        $response = $this->auth()->getJson('/api/customers');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    // ── POST /api/customers ────────────────────────────────────────────────────

    public function test_can_create_customer(): void
    {
        $response = $this->auth()->postJson('/api/customers', [
            'first_name' => 'Rosa',
            'last_name'  => 'Reyes',
            'phone'      => '+639171234567',
            'email'      => 'rosa@example.com',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.first_name', 'Rosa')
                 ->assertJsonPath('data.last_name', 'Reyes');

        $this->assertDatabaseHas('customers', ['phone' => '+639171234567']);
    }

    public function test_create_customer_requires_first_name(): void
    {
        $this->auth()->postJson('/api/customers', [
            'last_name' => 'Reyes',
            'phone'     => '+639171234567',
        ])->assertStatus(422)->assertJsonValidationErrors(['first_name']);
    }

    public function test_create_customer_requires_last_name(): void
    {
        $this->auth()->postJson('/api/customers', [
            'first_name' => 'Rosa',
            'phone'      => '+639171234567',
        ])->assertStatus(422)->assertJsonValidationErrors(['last_name']);
    }

    public function test_create_customer_requires_phone(): void
    {
        $this->auth()->postJson('/api/customers', [
            'first_name' => 'Rosa',
            'last_name'  => 'Reyes',
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }

    public function test_create_customer_phone_must_be_unique(): void
    {
        $this->makeCustomer(['phone' => '+639170000001']);

        $this->auth()->postJson('/api/customers', [
            'first_name' => 'Another',
            'last_name'  => 'Person',
            'phone'      => '+639170000001',
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }

    public function test_create_customer_email_must_be_valid(): void
    {
        $this->auth()->postJson('/api/customers', [
            'first_name' => 'Bad',
            'last_name'  => 'Email',
            'phone'      => '+639170000002',
            'email'      => 'not-an-email',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // ── GET /api/customers/{id} ────────────────────────────────────────────────

    public function test_can_show_customer(): void
    {
        $customer = $this->makeCustomer(['first_name' => 'Visible']);

        $this->auth()->getJson("/api/customers/{$customer->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.first_name', 'Visible');
    }

    public function test_show_returns_404_for_missing_customer(): void
    {
        $this->auth()->getJson('/api/customers/9999')
             ->assertStatus(404);
    }

    // ── PUT /api/customers/{id} ────────────────────────────────────────────────

    public function test_can_update_customer(): void
    {
        $customer = $this->makeCustomer(['first_name' => 'Before']);

        $this->auth()->putJson("/api/customers/{$customer->id}", [
            'first_name' => 'After',
        ])->assertStatus(200)
          ->assertJsonPath('data.first_name', 'After');

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'first_name' => 'After']);
    }

    public function test_can_change_customer_type(): void
    {
        $customer = $this->makeCustomer(['customer_type' => 'regular']);

        $this->auth()->putJson("/api/customers/{$customer->id}", [
            'customer_type' => 'vip',
        ])->assertStatus(200)
          ->assertJsonPath('data.customer_type', 'vip');
    }

    // ── DELETE /api/customers/{id} ─────────────────────────────────────────────

    public function test_can_delete_customer(): void
    {
        $customer = $this->makeCustomer();

        $this->auth()->deleteJson("/api/customers/{$customer->id}")
             ->assertStatus(200)
             ->assertJsonPath('success', true);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    // ── GET /api/customers/search/{term} ──────────────────────────────────────

    public function test_can_search_customers_by_name(): void
    {
        $this->makeCustomer(['first_name' => 'Maribel', 'phone' => '+639170000010']);
        $this->makeCustomer(['first_name' => 'Roberto', 'phone' => '+639170000011']);

        $response = $this->auth()->getJson('/api/customers/search/Maribel');

        $names = collect($response->json('data'))->pluck('first_name');
        $this->assertContains('Maribel', $names);
        $this->assertNotContains('Roberto', $names);
    }

    public function test_can_search_customers_by_phone(): void
    {
        $this->makeCustomer(['phone' => '+639170099999']);

        $response = $this->auth()->getJson('/api/customers/search/99999');

        $phones = collect($response->json('data'))->pluck('phone');
        $this->assertContains('+639170099999', $phones);
    }

    // ── POST /api/customers/{customer}/credit ──────────────────────────────────

    public function test_can_update_customer_credit_limit(): void
    {
        $customer = $this->makeCustomer(['credit_limit' => 1000.00]);

        $this->auth()->postJson("/api/customers/{$customer->id}/credit", [
            'credit_limit' => 5000.00,
        ])->assertStatus(200)
          ->assertJsonPath('data.credit_limit', 5000);
    }

    public function test_can_add_credit_balance_to_customer(): void
    {
        $customer = $this->makeCustomer();

        $this->auth()->postJson("/api/customers/{$customer->id}/credit", [
            'credit_amount' => 200.00,
        ])->assertStatus(200);

        $this->assertDatabaseHas('customers', [
            'id'              => $customer->id,
            'current_balance' => 200.00,
        ]);
    }
}
