<?php

namespace Database\Seeders;

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Models\IcebreakerQuestion;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class MeetMeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedQuestionPool();
        $this->seedMeetings($this->seedUsers());
    }

    private function seedQuestionPool(): void
    {
        foreach (config('meetme.fallback_questions') as $question) {
            IcebreakerQuestion::query()->create(['question' => $question]);
        }

        IcebreakerQuestion::factory()->count(40)->create();
    }

    /** @return Collection<int, User> */
    private function seedUsers(): Collection
    {
        $explorer = User::factory()->withGithub()->create([
            'name' => 'Demo Explorer',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'x_username' => 'demoexplorer',
            'bluesky_handle' => 'demo.bsky.social',
            'pronouns' => 'they/them',
            'email_visible' => true,
        ]);

        $others = User::factory()
            ->count(29)
            ->sequence(fn ($sequence) => match ($sequence->index % 4) {
                0 => ['pronouns' => 'she/her', 'email_visible' => true],
                1 => ['pronouns' => 'he/him'],
                2 => ['pronouns' => 'they/them', 'x_username' => 'dev'.$sequence->index],
                default => ['bluesky_handle' => 'dev'.$sequence->index.'.bsky.social'],
            })
            ->create();

        $others->random(15)->each(function (User $user): void {
            $user->socialAccounts()->create([
                'provider' => SocialProvider::Github,
                'provider_id' => (string) fake()->unique()->randomNumber(8),
                'username' => fake()->unique()->userName(),
            ]);

            $user->update(['avatar_source' => AvatarSource::Github]);
        });

        return collect([$explorer])->concat($others);
    }

    /** @param Collection<int, User> $users */
    private function seedMeetings(Collection $users): void
    {
        $ids = $users->pluck('id')->all();
        $pairs = [];

        for ($i = 0; $i < count($ids); $i++) {
            for ($j = $i + 1; $j < count($ids); $j++) {
                $pairs[] = [$ids[$i], $ids[$j]];
            }
        }

        $selected = collect($pairs)->shuffle()->take(80);

        foreach ($selected->values() as $index => [$initiatorId, $recipientId]) {
            $state = match (true) {
                $index % 20 === 5 => 'pending',
                $index % 20 === 10 => 'answered',
                $index % 20 === 15 => 'rejected',
                $index % 20 === 18 => 'redacted',
                default => 'confirmed',
            };

            $attributes = ['initiator_id' => $initiatorId, 'recipient_id' => $recipientId];

            if (in_array($state, ['confirmed', 'redacted'], true)) {
                $attributes['resolved_at'] = now()->subMinutes(fake()->numberBetween(1, 2880));
            }

            $factory = Meeting::factory();

            if ($state !== 'pending') {
                $factory = $factory->{$state}();
            }

            $factory->create($attributes);
        }
    }
}
