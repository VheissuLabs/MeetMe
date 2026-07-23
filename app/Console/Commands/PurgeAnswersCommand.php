<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('meetme:purge-answers')]
#[Description('Hard-delete recorded answer text once the retention window has passed')]
class PurgeAnswersCommand extends Command
{
    public function handle(): int
    {
        $days = config('meetme.purge_answers_after_days');
        $endsAt = config('meetme.ends_at');

        if ($days === null || blank($endsAt)) {
            $this->info('Answer retention is off (no ends_at or purge window) — nothing purged.');

            return self::SUCCESS;
        }

        $purgeAfter = CarbonImmutable::parse($endsAt)->addDays((int) $days);

        if (now()->lessThan($purgeAfter)) {
            $this->info("Retention window still open — answers purge after {$purgeAfter->toDateString()}.");

            return self::SUCCESS;
        }

        $purged = Meeting::query()->whereNotNull('answer')->update(['answer' => null]);

        $this->info("Purged {$purged} recorded ".str('answer')->plural($purged).'.');

        return self::SUCCESS;
    }
}
