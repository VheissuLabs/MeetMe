<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\ConferenceRecap;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Query\Builder;

#[Signature('meetme:send-recaps')]
#[Description('Email each attendee their post-conference recap')]
class SendRecapsCommand extends Command
{
    public function handle(): int
    {
        $sent = 0;

        User::query()
            ->where(fn (Builder $query) => $query
                ->whereIn('id', Meeting::query()->confirmed()->select('initiator_id'))
                ->orWhereIn('id', Meeting::query()->confirmed()->select('recipient_id')))
            ->each(function (User $user) use (&$sent): void {
                $user->notify(new ConferenceRecap);
                $sent++;
            });

        $this->info("Queued {$sent} recap ".str('email')->plural($sent).'.');

        return self::SUCCESS;
    }
}
