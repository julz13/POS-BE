<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    private function createTransactionWithPayment(): Payment
    {
        $category = Category::create(['name' => 'Test', 'status' => 'active']);
        $product  = Product::create([
            'sku' => 'P-' . uniqid(), 'name' => 'Test', 'category_id' => $category->id,
            'selling_price' => 50, 'unit' => 'piece', 'stock' => 100, 'status' => 'active',
        ]);

        $response = $this->auth()->postJson('/api/transactions', [
            'subtotal'    => 50, 'total' => 50, 'amount_paid' => 50,
            'items'       => [[
                'product_id' => $product->id, 'product_name' => $product->name,
                'sku' => $product->sku, 'unit' => 'piece', 'quantity' => 1,
                'unit_price' => 50, 'line_total' => 50,
            ]],
            'payments' => [['payment_method' => 'cash', 'amount' => 50]],
        ]);

        return Payment::find($response->json('data.payments.0.id'));
    }

    public function test_unauthenticated_cannot_list_payments(): void
    {
        $this->getJson('/api/payments')->assertStatus(401);
    }

    public function test_can_list_payments(): void
    {
        $this->createTransactionWithPayment();
        $this->auth()->getJson('/api/payments')
             ->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_can_show_payment(): void
    {
        $payment = $this->createTransactionWithPayment();
        $this->auth()->getJson("/api/payments/{$payment->id}")
             ->assertStatus(200)
             ->assertJsonPath('data.id', $payment->id);
    }

    public function test_can_void_payment(): void
    {
        $payment = $this->createTransactionWithPayment();
        $this->auth()->postJson("/api/payments/{$payment->id}/void", [
            'void_reason' => 'Wrong amount',
        ])->assertStatus(200)
          ->assertJsonPath('data.status', 'voided');

        $this->assertDatabaseHas('payments', [
            'id'          => $payment->id,
            'status'      => 'voided',
            'void_reason' => 'Wrong amount',
        ]);
    }

    public function test_void_requires_reason(): void
    {
        $payment = $this->createTransactionWithPayment();
        $this->auth()->postJson("/api/payments/{$payment->id}/void", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['void_reason']);
    }
}
