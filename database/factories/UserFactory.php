<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name'    => fake()->firstName(),
            'last_name'     => fake()->lastName(),
            'email'         => fake()->unique()->safeEmail(),
            'password_hash' => 'password', // 'hashed' cast handles bcrypt automatically
            'role'          => 'cashier',
            'status'        => 'active',
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => 'owner']);
    }

    public function manager(): static
    {
        return $this->state(fn () => ['role' => 'manager']);
    }
}
