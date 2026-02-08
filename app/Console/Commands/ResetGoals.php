<?php

namespace App\Console\Commands;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetGoals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goals:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all user goals and assign 5 new goals to each user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $goals = Goal::inRandomOrder()->get();

        if ($goals->isEmpty()) {
            $this->error('No goals created yet.');
            return Command::FAILURE;
        }

        foreach (User::cursor() as $user) {
            DB::transaction(function () use ($user, $goals) {
                $user->goals()->detach();

                $user->goals()->attach($goals->random(5)->pluck('id'));
            });
        }

        $this->info('Goals reset and assigned successfully.');

        return Command::SUCCESS;
    }
}
