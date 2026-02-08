<?php

namespace App\Listeners;

use App\Enums\GoalType;
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\PlatformAdded;
use App\Events\TaskCompleted;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Events\Dispatcher;

class GoalTrackingSubscriber
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            TaskCompleted::class => 'handleTaskCompleted',
            LessonCompleted::class => 'handleLessonCompleted',
            CourseCompleted::class => 'handleCourseCompleted',
            PlatformAdded::class => 'handlePlatformAdded',
        ];
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        $this->processActivity($event->user, GoalType::TASK, $event->task);
    }

    public function handleLessonCompleted(LessonCompleted $event): void
    {
        $this->processActivity($event->user, GoalType::LESSON, $event->lesson);
    }

    public function handleCourseCompleted(CourseCompleted $event): void
    {
        $this->processActivity($event->user, GoalType::COURSE, $event->course);
    }

    public function handlePlatformAdded(PlatformAdded $event): void
    {
        $this->processActivity($event->user, GoalType::PLATFORM, $event->platform);
    }

    private function processActivity($user, GoalType $type, $related = null): void
    {
        if ($type) {
            $existing = UserActivity::where('user_id', $user->id)
                ->where('type', $type->value)
                ->where('related_id', $related?->id)
                ->where('related_type', $related ? get_class($related) : null)
                ->first();

            if (!$existing) {
                UserActivity::create([
                    'user_id' => $user->id,
                    'type' => $type->value,
                    'related_id' => $related?->id,
                    'related_type' => $related ? get_class($related) : null,
                ]);
            }

            $this->checkGoal($user, $type);
        }
    }

    private function checkGoal($user, $type): void
    {
        $goals = $user->goals()->where('type', $type->value)->get();

        foreach ($goals as $goal) {
            $activityCount = UserActivity::where('user_id', $user->id)
                ->where('type', $type->value)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($activityCount >= $goal->target) {
                $user->goals()->updateExistingPivot($goal->id, [
                    'completed_at' => now(),
                    'is_completed' => true
                ]);
            }
        }
    }
}
