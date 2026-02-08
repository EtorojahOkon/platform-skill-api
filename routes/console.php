<?php

use App\Console\Commands\ResetGoals;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ResetGoals::class)->dailyAt('00:00');