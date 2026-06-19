<?php

namespace Tests\Feature;

use App\Models\StockTake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTakeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makeST(array $attrs = []): StockTake
    {
        return StockTake::create(array_merge([
            'st_number'  => 'ST-' . uniqid(),
            'date'       => now(),
            'created_by' => $this->user->id,
            'status'     => 'in_progress',
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_stock_takes(): void
    {
        $this->getJson('/api/stock-takes')->assertStatus(401);
    }

    public function test_can_list_stock_takes(): void
    {
        $this->makeST();
        $this->auth()->getJson('/api/stock-takes')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_can_create_stock_take(): void
    {
        $this->auth()->postJson('/api/stock-takes')
             ->assertStatus(201)
             ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('stock_takes', ['created_by' => $this->user->id]);
    }

    public function test_can_show_stock_take(): void
    {
        $st = $this->makeST();
        $this->auth()->getJson("/api/stock-takes/{$st->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $st->id);
    }

    public function test_can_complete_stock_take(): void
    {
        $st = $this->makeST();
        $this->auth()->postJson("/api/stock-takes/{$st->id}/complete")
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'completed');
    }

    public function test_can_post_stock_take(): void
    {
        $st = $this->makeST();
        $this->auth()->postJson("/api/stock-takes/{$st->id}/post")
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'posted');
        $this->assertDatabaseHas('stock_takes', ['id' => $st->id, 'posted_by' => $this->user->id]);
    }

    public function test_can_delete_stock_take(): void
    {
        $st = $this->makeST();
        $this->auth()->deleteJson("/api/stock-takes/{$st->id}")
             ->assertStatus(200);
        $this->assertDatabaseMissing('stock_takes', ['id' => $st->id]);
    }
}
