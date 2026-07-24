<?php

namespace App\Actions;

use App\Enums\Achievement;
use App\Events\AchievementUnlocked;
use App\Models\Meeting;
use App\Models\User;

class AwardAchievements
{
    public function for(User $user): void
    {
        $earned = $user->achievements()->get()->pluck('achievement')->all();
        $score = $user->score();

        foreach (Achievement::cases() as $achievement) {
            if (in_array($achievement, $earned, true)) {
                continue;
            }

            if (! $this->qualifies($user, $achievement, $score)) {
                continue;
            }

            $user->achievements()->create([
                'achievement' => $achievement,
                'earned_at' => now(),
            ]);

            AchievementUnlocked::dispatch($user->id, $achievement);
        }
    }

    private function qualifies(User $user, Achievement $achievement, int $score): bool
    {
        return match ($achievement) {
            Achievement::FirstConnection => $score >= 1,
            Achievement::GettingStarted => $score >= 3,
            Achievement::SocialButterfly => $score >= 10,
            Achievement::SuperConnector => $score >= 25,
            Achievement::FiveStar => Meeting::query()
                ->confirmed()
                ->where('initiator_id', $user->id)
                ->where('rating', 5)
                ->exists(),
        };
    }
}
