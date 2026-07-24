<?php

namespace App\Actions;

use App\Enums\Achievement;
use App\Events\AchievementUnlocked;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
            Achievement::Legend => $score >= 50,
            Achievement::FiveStar => Meeting::query()
                ->confirmed()
                ->where('initiator_id', $user->id)
                ->where('rating', 5)
                ->exists(),
            Achievement::CrowdPleaser => $this->ratedAnswers($user)->count() >= 5
                && (float) $this->ratedAnswers($user)->avg('rating') >= 4.5,
            Achievement::Curious => Meeting::query()
                ->confirmed()
                ->where('initiator_id', $user->id)
                ->count() >= 10,
            Achievement::EarlyTester => ($startsAt = Event::current()->starts_at) !== null
                && $user->created_at->lessThan($startsAt),
            Achievement::AllWiredUp => $user->githubAccount()->exists()
                && filled($user->x_username)
                && filled($user->bluesky_handle)
                && filled($user->linkedin_username),
            Achievement::Popular => $this->timesScanned($user) >= 10,
            Achievement::Prolific => $this->timesScanned($user) >= 25,
            Achievement::NightOwl => $this->confirmedHours($user)->contains(fn (int $hour): bool => $hour >= 21),
            Achievement::EarlyBird => $this->confirmedHours($user)->contains(fn (int $hour): bool => $hour < 9),
            // Evaluated last (enum order), after any others earned this run.
            Achievement::Completionist => $user->achievements()
                ->where('achievement', '!=', Achievement::Completionist->value)
                ->count() >= count(Achievement::cases()) - 1,
        };
    }

    private function timesScanned(User $user): int
    {
        return Meeting::query()->confirmed()->where('recipient_id', $user->id)->count();
    }

    /** @return Collection<int, int> */
    private function confirmedHours(User $user): Collection
    {
        return Meeting::query()
            ->confirmed()
            ->involving($user)
            ->whereNotNull('resolved_at')
            ->get(['resolved_at'])
            ->map(fn (Meeting $meeting): int => $meeting->resolved_at->timezone(config('app.timezone'))->hour);
    }

    /** @return Builder<Meeting> */
    private function ratedAnswers(User $user): Builder
    {
        return Meeting::query()
            ->confirmed()
            ->where('initiator_id', $user->id)
            ->whereNotNull('rating');
    }
}
