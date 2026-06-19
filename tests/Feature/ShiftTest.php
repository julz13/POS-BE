<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTest extends TestCase
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

    private function openShift(User $cashier = null, array $attrs = []): Shift
    {
        $cashier ??= $this->user;
        return Shift::create(array_merge([
            'shift_number' => 'SFT-' . uniqid(),
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 1000.00,
            'status'       => 'open',
        ], $attrs));
    }

    // ── GET /api/shifts ────────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_list_shifts(): void
    {
        $this->getJson('/api/shifts')->assertStatus(401);
    }

    public function test_can_list_shifts(): void
    {
        $this->openShift();

        $response = $this->auth()->getJson('/api/shifts');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['data']]);
    }

    // ── POST /api/shifts ───────────────────────────────────────────────────────

    public function test_can_open_a_shift(): void
    {
        $response = $this->auth()->postJson('/api/shifts', [
            'cashier_id'   => $this->user->id,
            'opening_cash' => 500.00,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'open')
                 ->assertJsonPath('data.cashier_id', $this->user->id);

        $this->assertDatabaseHas('shifts', [
            'cashier_id'   => $this->user->id,
            'opening_cash' => 500.00,
            'status'       => 'open',
        ]);
    }

    public function test_open_shift_requires_cashier_id(): void
    {
        $this->auth()->postJson('/api/shifts', ['opening_cash' => 500.00])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['cashier_id']);
    }

    public function test_open_shift_cashier_must_exist(): void
    {
        $this->auth()->postJson('/api/shifts', [
            'cashier_id'   => 9999,
            'opening_cash' => 500.00,
        ])->assertStatus(422)->assertJsonValidationErrors(['cashier_id']);
    }

    // ── GET /api/shifts/{id} ───────────────────────────────────────────────────

    public function test_can_show_shift(): void
    {
        $shift = $this->openShift();

        $this->auth()->getJson("/api/shifts/{$shift->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $shift->id);
    }

    public function test_show_returns_404_for_missing_shift(): void
    {
        $this->auth()->getJson('/api/shifts/9999')
             ->assertStatus(404);
    }

    // ── POST /api/shifts/{id}/close ────────────────────────────────────────────

    public function test_can_close_a_shift(): void
    {
        $shift = $this->openShift();

        $response = $this->auth()->postJson("/api/shifts/{$shift->id}/close", [
            'counted_cash' => 1500.00,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('shifts', [
            'id'           => $shift->id,
            'status'       => 'closed',
            'counted_cash' => 1500.00,
        ]);
    }

    // ── GET /api/shifts/active/current ────────────────────────────────────────

    public function test_get_current_shift_returns_null_when_none_open(): void
    {
        $response = $this->auth()->getJson('/api/shifts/active/current');

        $response->assertStatus(200)
                 ->assertJsonPath('data', null);
    }

    public function test_get_current_shift_returns_open_shift(): void
    {
        $shift = $this->openShift();

        $response = $this->auth()->getJson('/api/shifts/active/current');

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $shift->id)
                 ->assertJsonPath('data.status', 'open');
    }

    // ── POST /api/shifts/{id}/movements ────────────────────────────────────────

    public function test_can_add_cash_in_movement(): void
    {
        $shift = $this->openShift();

        $response = $this->auth()->postJson("/api/shifts/{$shift->id}/movements", [
            'type'   => 'in',
            'amount' => 200.00,
            'reason' => 'Opening float top-up',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.type', 'in')
                 ->assertJsonPath('data.amount', 200);

        $this->assertDatabaseHas('shift_movements', [
            'shift_id' => $shift->id,
            'type'     => 'in',
            'amount'   => 200.00,
        ]);
    }

    public function test_can_add_cash_out_movement(): void
    {
        $shift = $this->openShift();

        $this->auth()->postJson("/api/shifts/{$shift->id}/movements", [
            'type'   => 'out',
            'amount' => 100.00,
            'reason' => 'Petty cash withdrawal',
        ])->assertStatus(201)
          ->assertJsonPath('data.type', 'out');
    }

    public function test_add_movement_requires_type_amount_reason(): void
    {
        $shift = $this->openShift();

        $this->auth()->postJson("/api/shifts/{$shift->id}/movements", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['type', 'amount', 'reason']);
    }

    public function test_add_movement_type_must_be_in_or_out(): void
    {
        $shift = $this->openShift();

        $this->auth()->postJson("/api/shifts/{$shift->id}/movements", [
            'type'   => 'invalid',
            'amount' => 100.00,
            'reason' => 'Test',
        ])->assertStatus(422)->assertJsonValidationErrors(['type']);
    }
}
