<?php

namespace App\Filament\Widgets;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeetMeStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        $byStatus = Meeting::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $confirmed = (int) ($byStatus[MeetingStatus::Confirmed->value] ?? 0);
        $pending = (int) ($byStatus[MeetingStatus::Pending->value] ?? 0);
        $answered = (int) ($byStatus[MeetingStatus::Answered->value] ?? 0);
        $rejected = (int) ($byStatus[MeetingStatus::Rejected->value] ?? 0);

        return [
            Stat::make('Attendees', User::query()->count())
                ->description('Registered users')
                ->color('primary'),
            Stat::make('Connections', $confirmed)
                ->description('Confirmed meetings')
                ->color('success'),
            Stat::make('In flight', $pending + $answered)
                ->description("{$pending} pending · {$answered} awaiting confirmation")
                ->color('warning'),
            Stat::make('Rejected', $rejected)
                ->description('Meetings marked "didn\'t happen"')
                ->color('gray'),
        ];
    }
}
