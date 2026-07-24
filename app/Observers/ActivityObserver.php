<?php

namespace App\Observers;

use App\Events\ActivityLogged;
use Spatie\Activitylog\Contracts\Activity;

class ActivityObserver
{
    public function created(Activity $activity): void
    {
        ActivityLogged::dispatch();
    }
}
