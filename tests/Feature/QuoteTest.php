<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makeQuote(array $attrs = []): Quote
    {
        return Quote::create(array_merge([
            'quote_number'  => 'QOT-' . uniqid(),
            'customer_name' => 'Walk-in',
            'date'          => now(),
            'created_by'    => $this->user->id,
            'status'        => 'draft',
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_quotes(): void
    {
        $this->getJson('/api/quotes')->assertStatus(401);
    }

    public function test_can_list_quotes(): void
    {
        $this->makeQuote();
        $this->auth()->getJson('/api/quotes')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_can_create_quote(): void
    {
        $this->auth()->postJson('/api/quotes', [
            'customer_name' => 'Juan',
            'phone'         => '+639171234567',
            'items'         => [['name' => 'Sample Item']],
        ])->assertStatus(201)
          ->assertJsonPath('data.status', 'draft');
    }

    public function test_create_quote_requires_items(): void
    {
        $this->auth()->postJson('/api/quotes', ['customer_name' => 'Test'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['items']);
    }

    public function test_can_show_quote(): void
    {
        $quote = $this->makeQuote();
        $this->auth()->getJson("/api/quotes/{$quote->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $quote->id);
    }

    public function test_can_delete_quote(): void
    {
        $quote = $this->makeQuote();
        $this->auth()->deleteJson("/api/quotes/{$quote->id}")
             ->assertStatus(200);
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }
}
