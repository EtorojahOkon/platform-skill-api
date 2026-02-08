<?php

namespace Database\Seeders;

use App\Enums\GoalType;
use App\Models\Goal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $goals = [
            [
                'title' => 'Complete 1 lesson',
                'type' => GoalType::LESSON,
                'target' => 1,
            ],
            [
                'title' => 'Complete 2 lessons',
                'type' => GoalType::LESSON,
                'target' => 2,
            ],
            [
                'title' => 'Complete 3 lessons',
                'type' => GoalType::LESSON,
                'target' => 3,
            ],
            [
                'title' => 'Complete 5 lessons',
                'type' => GoalType::LESSON,
                'target' => 5,
            ],
            [
                'title' => 'Complete 10 lessons',
                'type' => GoalType::LESSON,
                'target' => 10,
            ],
            [
                'title' => 'Complete 1 task',
                'type' => GoalType::TASK,
                'target' => 1,
            ],
            [
                'title' => 'Complete 2 tasks',
                'type' => GoalType::TASK,
                'target' => 2,
            ],
            [
                'title' => 'Complete 3 tasks',
                'type' => GoalType::TASK,
                'target' => 3,
            ],
            [
                'title' => 'Complete 5 tasks',
                'type' => GoalType::TASK,
                'target' => 5,
            ],

            [
                'title' => 'Complete 1 course',
                'type' => GoalType::COURSE,
                'target' => 1,
            ],
            [
                'title' => 'Complete 2 courses',
                'type' => GoalType::COURSE,
                'target' => 2,
            ],
            [
                'title' => 'Complete 3 courses',
                'type' => GoalType::COURSE,
                'target' => 3,
            ],
            [
                'title' => 'Complete 1 platform assessment',
                'type' => GoalType::PLATFORM,
                'target' => 1,
            ],
            [
                'title' => 'Complete 2 platform assessments',
                'type' => GoalType::PLATFORM,
                'target' => 2,
            ],
            [
                'title' => 'Complete 3 platform assessments',
                'type' => GoalType::PLATFORM,
                'target' => 3,
            ],
        ];

        foreach ($goals as $goal) {
            Goal::firstOrCreate(
                ['title' => $goal['title']],
                $goal
            );
        }
    }
}
