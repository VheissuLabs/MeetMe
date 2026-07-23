<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('meetme:purge-answers')->daily();
