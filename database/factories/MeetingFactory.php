<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Meeting> */
class MeetingFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'initiator_id' => User::factory(),
            'recipient_id' => User::factory(),
            'question' => 'Ask them: '.fake()->unique()->sentence().'?',
            'status' => MeetingStatus::Pending,
        ];
    }

    public function answered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MeetingStatus::Answered,
            'answer' => fake()->paragraph(),
            'answered_at' => now(),
        ]);
    }

    public function confirmed(): static
    {
        return $this->answered()->state(fn (array $attributes) => [
            'status' => MeetingStatus::Confirmed,
            'rating' => fake()->numberBetween(1, 5),
            'resolved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->answered()->state(fn (array $attributes) => [
            'status' => MeetingStatus::Rejected,
            'resolved_at' => now(),
        ]);
    }

    public function redacted(): static
    {
        return $this->confirmed()->state(fn (array $attributes) => [
            'answer' => null,
            'answer_redacted_at' => now(),
        ]);
    }
}
