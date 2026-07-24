<?php

namespace App\Actions;

use App\Enums\Achievement;
use App\Events\AchievementUnlocked;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\User;

class AwardAchievements
{
    public function for(User $user): void
    {
        $score = $user->score();

        foreach (Achievement::cases() as $achievement) {
            if ($this->qualifies($user, $achievement, $score)) {
                $this->grant($user, $achievement);
            }
        }
    }

    public function grant(User $user, Achievement $achievement): bool
    {
        if ($user->achievements()->where('achievement', $achievement)->exists()) {
            return false;
        }

        $user->achievements()->create([
            'achievement' => $achievement,
            'earned_at' => now(),
        ]);

        AchievementUnlocked::dispatch($user->id, $achievement);

        return true;
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
            Achievement::EarlyTester => ($startsAt = Event::current()->starts_at) !== null
                && $user->created_at->lessThan($startsAt),
        };
    }
}
