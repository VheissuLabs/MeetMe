<?php

namespace Database\Factories;

use App\Models\IcebreakerQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<IcebreakerQuestion> */
class IcebreakerQuestionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question' => 'Ask them: '.fake()->sentence().'?',
            'meeting_id' => null,
        ];
    }

    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'meeting_id' => (string) Str::ulid(),
        ]);
    }
}
