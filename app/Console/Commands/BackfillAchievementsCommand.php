<?php

namespace App\Console\Commands;

use App\Actions\AwardAchievements;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('meetme:backfill-achievements')]
#[Description('Award achievements to existing users who already meet the criteria')]
class BackfillAchievementsCommand extends Command
{
    public function handle(AwardAchievements $awardAchievements): int
    {
        $count = 0;

        foreach (User::query()->cursor() as $user) {
            $awardAchievements->for($user);
            $count++;
        }

        $this->info("Evaluated achievements for {$count} ".str('user')->plural($count).'.');

        return self::SUCCESS;
    }
}
