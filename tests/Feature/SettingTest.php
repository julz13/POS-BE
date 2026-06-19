<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function auth() { return $this->actingAs($this->user, 'sanctum'); }

    public function test_unauthenticated_cannot_get_settings(): void
    {
        $this->getJson('/api/settings')->assertStatus(401);
    }

    public function test_can_get_settings(): void
    {
        $this->auth()->getJson('/api/settings')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_can_update_settings(): void
    {
        Setting::create();

        $this->auth()->putJson('/api/settings', [
            'store_name'    => 'My Sari-Sari Store',
            'store_address' => '123 Main St, Manila',
            'tax_rate'      => 12.00,
        ])->assertStatus(200)
          ->assertJsonPath('data.store_name', 'My Sari-Sari Store');

        $this->assertDatabaseHas('settings', ['store_name' => 'My Sari-Sari Store']);
    }

    public function test_update_validates_tax_rate(): void
    {
        Setting::create();

        $this->auth()->putJson('/api/settings', ['tax_rate' => 150])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['tax_rate']);
    }
}
