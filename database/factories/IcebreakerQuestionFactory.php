<?php

namespace Database\Factories;

use App\Models\IcebreakerQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IcebreakerQuestion> */
class IcebreakerQuestionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'question' => 'Ask them: '.fake()->unique()->sentence().'?',
        ];
    }
}
