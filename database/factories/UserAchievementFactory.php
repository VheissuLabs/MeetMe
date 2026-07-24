<?php

namespace Database\Factories;

use App\Enums\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserAchievement> */
class UserAchievementFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'achievement' => fake()->randomElement(Achievement::cases()),
            'earned_at' => now(),
        ];
    }
}
