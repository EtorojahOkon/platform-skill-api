<?php

namespace App\Jobs;

use App\Contracts\GeminiInterface;
use App\Enums\Proficiency;
use App\Enums\AssessmentStatus;
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\PlatformAdded;
use App\Events\TaskCompleted;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Platform;
use App\Models\Task;
use App\Services\V1\AssessmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EvaluateAssessmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function __construct(
        public Assessment $assessment
    ) {}

    public function handle(GeminiInterface $geminiClient, AssessmentService $assessmentService): void
    {
        // Prepare parts (now fetching video Blob from R2 directly)
        $parts = $assessmentService->prepareGeminiParts($this->assessment);
        $config = $assessmentService->getGenerationConfig($this->assessment);

        // Generate Content
        $jsonText = $geminiClient->generateContent($parts, $config);
        $data = json_decode($jsonText, true);

        if (!$data) {
            // Log raw response for debugging if needed
            throw new \RuntimeException('Failed to parse Gemini response: ' . substr($jsonText, 0, 200));
        }

        DB::transaction(function () use ($data) {
            $proficiencyValue = strtolower($data['proficiency'] ?? '');
            $proficiency = Proficiency::tryFrom($proficiencyValue);

            $this->assessment->update([
                'proficiency' => $proficiency,
                'ai_comments' => $data['comments'] ?? [],
                'rating' => $data['rating'] ?? null,
            ]);

            match ($this->assessment->assessable_type) {
                'lesson' => $this->handleLessonAssessment($data),
                'task' => $this->handleTaskAssessment($data),
                'platform' => $this->handlePlatformAssessment($data),
                default => throw new \InvalidArgumentException('Invalid assessable type'),
            };

            $this->assessment->update(['status' => AssessmentStatus::COMPLETED]);
        });
    }

    private function handlePlatformAssessment(array $data): void
    {
        $platformName = $data['platform'] ?? 'Untitled Platform';

        $platform = Platform::create([
            'user_id' => $this->assessment->user_id,
            'name' => $platformName,
            'proficiency' => $this->assessment->proficiency,
        ]);

        $this->assessment->update([
            'assessable_id' => $platform->id,
        ]);

        PlatformAdded::dispatch($this->assessment->user, $platform);

        if (isset($data['course'])) {
            $course = Course::create([
                'user_id' => $this->assessment->user_id,
                'platform_id' => $platform->id,
                'title' => $data['course']['title'] ?? '',
            ]);

            $firstLesson = null;
            foreach ($data['course']['modules'] ?? [] as $moduleData) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'] ?? '',
                ]);

                foreach ($moduleData['lessons'] ?? [] as $lessonTitle) {
                    $lesson = Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonTitle,
                    ]);

                    if ($firstLesson === null) {
                        $firstLesson = $lesson;
                    }
                }
            }

            if ($firstLesson) {
                $firstLesson->update(['is_locked' => false]);
            }
        }
    }

    private function handleLessonAssessment(array $data): void
    {
        $lesson = Lesson::find($this->assessment->assessable_id);

        if ($lesson && isset($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                Task::create([
                    'lesson_id' => $lesson->id,
                    'description' => $taskData['description'] ?? 'New Task',
                    'is_completed' => false,
                ]);
            }
        }
    }

    private function handleTaskAssessment(array $data): void
    {
        $task = Task::find($this->assessment->assessable_id);

        if (!$task) {
            return;
        }

        if ($data['is_completed'] ?? false) {
            $task->update(['is_completed' => true]);

            TaskCompleted::dispatch($this->assessment->user, $task);

            $lesson = $task->lesson;

            if (!$lesson) {
                return;
            }

            if ($lesson->tasks()->where('is_completed', false)->doesntExist()) {
                $lesson->update(['is_completed' => true]);

                LessonCompleted::dispatch($this->assessment->user, $lesson);

                // Check if course is completed
                $course = $lesson->module->course;
                if ($course->completed_lessons === $course->total_lessons) {
                    CourseCompleted::dispatch($this->assessment->user, $course);
                }

                $nextLesson = Lesson::whereHas('module', function (Builder $query) use ($lesson) {
                    $query->where('course_id', $lesson->module->course_id);
                })
                    ->where('id', '!=', $lesson->id)
                    ->where('created_at', '>', $lesson->created_at)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if (!$nextLesson) {
                    $nextLesson = Lesson::where('module_id', $lesson->module_id)
                        ->where('id', '>', $lesson->id)
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if ($nextLesson) {
                    $nextLesson->update(['is_locked' => false]);
                }
            }
        }
    }
}
