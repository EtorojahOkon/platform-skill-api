<?php

namespace App\Listeners;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;

class AssignGoalsOnEmailVerification
{
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $goals = Goal::inRandomOrder()->get();

        if ($goals->isEmpty()) {
            return;
        }

        $user->goals()->attach($goals->random(5)->pluck('id'));
    }
}
