<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function makeVoucher(array $attrs = []): Voucher
    {
        return Voucher::create(array_merge([
            'code'              => 'GV-' . strtoupper(bin2hex(random_bytes(4))),
            'original_amount'   => 500.00,
            'remaining_balance' => 500.00,
            'status'            => 'active',
            'issued_by'         => $this->user->id,
        ], $attrs));
    }

    public function test_can_list_vouchers(): void
    {
        $this->makeVoucher();
        $this->auth()->getJson('/api/vouchers')
             ->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_can_create_voucher(): void
    {
        $response = $this->auth()->postJson('/api/vouchers', [
            'original_amount' => 1000.00,
            'issued_to_name'  => 'Juan Dela Cruz',
        ]);

        $response->assertStatus(201);
        $this->assertStringStartsWith('GV-', $response->json('data.code'));
        $this->assertDatabaseHas('vouchers', ['issued_by' => $this->user->id]);
    }

    public function test_create_requires_original_amount(): void
    {
        $this->auth()->postJson('/api/vouchers', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['original_amount']);
    }

    public function test_can_show_voucher(): void
    {
        $v = $this->makeVoucher();
        $this->auth()->getJson("/api/vouchers/{$v->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $v->id);
    }

    public function test_can_redeem_voucher(): void
    {
        $v = $this->makeVoucher(['remaining_balance' => 500]);
        $this->auth()->postJson("/api/vouchers/{$v->id}/redeem", ['amount' => 200])
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'partially_used');

        $this->assertDatabaseHas('vouchers', ['id' => $v->id, 'remaining_balance' => 300]);
    }

    public function test_redeem_fails_with_insufficient_balance(): void
    {
        $v = $this->makeVoucher(['remaining_balance' => 50]);
        $this->auth()->postJson("/api/vouchers/{$v->id}/redeem", ['amount' => 100])
             ->assertJsonPath('success', false);

        $this->assertDatabaseHas('vouchers', ['id' => $v->id, 'remaining_balance' => 50]);
    }

    public function test_can_cancel_voucher(): void
    {
        $v = $this->makeVoucher();
        $this->auth()->postJson("/api/vouchers/{$v->id}/cancel", ['reason' => 'Customer request'])
             ->assertStatus(200)
             ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_cancel_requires_reason(): void
    {
        $v = $this->makeVoucher();
        $this->auth()->postJson("/api/vouchers/{$v->id}/cancel", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['reason']);
    }

    public function test_can_delete_voucher(): void
    {
        $v = $this->makeVoucher();
        $this->auth()->deleteJson("/api/vouchers/{$v->id}")
             ->assertStatus(200);
        $this->assertDatabaseMissing('vouchers', ['id' => $v->id]);
    }
}
