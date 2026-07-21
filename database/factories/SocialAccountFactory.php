<?php

namespace Database\Factories;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SocialAccount> */
class SocialAccountFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => SocialProvider::Github,
            'provider_id' => (string) fake()->unique()->randomNumber(8),
            'username' => fake()->unique()->userName(),
            'avatar_url' => fake()->imageUrl(200, 200),
        ];
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => SocialProvider::Github,
        ]);
    }

    public function x(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => SocialProvider::X,
        ]);
    }
}
